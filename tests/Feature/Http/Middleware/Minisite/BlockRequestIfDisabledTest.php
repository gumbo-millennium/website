<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware\Minisite;

use App\Http\Middleware\Minisite\BlockRequestIfDisabled;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BlockRequestIfDisabledTest extends TestCase
{
    public function test_not_configured(): void
    {
        $this->fireMiddleware('http://localhost')
            ->assertStatus(HttpResponse::HTTP_SERVICE_UNAVAILABLE)
            ->assertSee('Niet beschikbaar');
    }

    public function test_configured_enabled(): void
    {
        Config::set('gumbo.minisites', [
            'localhost' => [
                'enabled' => true,
            ],
        ]);

        $this->fireMiddleware('http://localhost')
            ->assertOk();
    }

    public function test_configured_disabled(): void
    {
        Config::set('gumbo.minisites', [
            'localhost' => [
                'enabled' => false,
            ],
        ]);

        $this->fireMiddleware('http://localhost')
            ->assertStatus(HttpResponse::HTTP_SERVICE_UNAVAILABLE)
            ->assertSee('Niet beschikbaar');
    }

    public function test_misconfigured(): void
    {
        Config::set('gumbo.minisites', [
            'localhost' => [
                'test' => 'test',
            ],
        ]);

        $this->fireMiddleware('http://localhost')
            ->assertStatus(HttpResponse::HTTP_SERVICE_UNAVAILABLE)
            ->assertSee('Niet beschikbaar');
    }

    private function fireMiddleware(Request|string $request): TestResponse
    {
        if (is_string($request)) {
            $request = Request::create($request);
        }

        $result = $this->app->make(BlockRequestIfDisabled::class)
            ->handle($request, fn () => Response::make('OK'));

        return TestResponse::fromBaseResponse($result);
    }
}
