<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Helpers\Str;
use App\Models\RedirectInstruction;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RedirectControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_index(): void
    {
        $this->get('http://gumbo.nu/')
            ->assertRedirect($this->computeExpectedURL('/'))
            ->assertStatus(Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_found(): void
    {
        RedirectInstruction::updateOrCreate([
            'slug' => 'my-test/route',
        ], [
            'path' => '/cake',
        ]);

        $this->get('http://gumbo.nu/my-test/route')
            ->assertRedirect($this->computeExpectedURL('/cake'))
            ->assertStatus(Response::HTTP_FOUND);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_deleted(): void
    {
        RedirectInstruction::updateOrCreate([
            'slug' => 'removed-route',
        ], [
            'path' => '/cake',
        ])->delete();

        $this->get('http://gumbo.nu/removed-route')
            ->assertStatus(Response::HTTP_GONE);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_missing(): void
    {
        $this->get('http://gumbo.nu/random-url')
            ->assertRedirect($this->computeExpectedURL('/random-url'))
            ->assertStatus(Response::HTTP_FOUND);
    }

    /**
     * Determines the URL that should've been generated.
     *
     * @param string $path Local path
     * @return string Path with the proper domain prefixed
     */
    private function computeExpectedURL(string $path): string
    {
        return rtrim(Str::finish(Config::get('app.url'), '/') . ltrim($path, '/'), '/');
    }
}
