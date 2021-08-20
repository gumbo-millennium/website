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
     * Tests redirect homepage.
     */
    public function test_get_index(): void
    {
        $this->get('http://gumbo.nu/')
            ->assertRedirect($this->computeExpectedURL('/'))
            ->assertStatus(Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Tests a redirect works.
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
     * Test a deleted redirect throws a 410.
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
     * Test a missing redirect falls through.
     */
    public function test_get_missing(): void
    {
        $this->get('http://gumbo.nu/random-url')
            ->assertRedirect($this->computeExpectedURL('/random-url'))
            ->assertStatus(Response::HTTP_FOUND);
    }

    /**
     * Test the fallback on the main route.
     */
    public function test_main_fallback_valid(): void
    {
        RedirectInstruction::updateOrCreate([
            'slug' => 'random-fallback-url',
        ], [
            'path' => '/test-ok',
        ]);

        $this->get('/random-fallback-url')
            ->assertRedirect($this->computeExpectedURL('/test-ok'))
            ->assertStatus(Response::HTTP_FOUND);
    }

    /**
     * Test the fallback on the main route.
     */
    public function test_main_fallback_gone(): void
    {
        RedirectInstruction::updateOrCreate([
            'slug' => 'random-gone-fallback-url',
        ], [
            'path' => '/test-ok',
        ])->delete();

        $this->get('/random-gone-fallback-url')
            ->assertStatus(Response::HTTP_GONE);
    }

    /**
     * Test the fallback on the main route.
     */
    public function test_main_fallback_missing(): void
    {
        $this->get('/missing-but-still-random-url')
            ->assertNotFound();
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
