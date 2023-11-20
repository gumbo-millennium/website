<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Helpers\Str;
use App\Http\Middleware\MustAcceptJson;
use Generator;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TestMustAcceptJson extends TestCase
{
    /**
     * Returns a list of HTTP methods.
     * @return Generator<string,string[]>
     */
    public static function provide_http_methods(): Generator
    {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
        ];

        foreach ($methods as $method) {
            yield $method;
        }
    }

    /**
     * Returns headers that match the application/json mime.
     * @return Generator<string,string[]>
     */
    public static function provide_valid_headers(): Generator
    {
        $headers = [
            'Does not specify acceptance' => [],
            'Accepts JSON only' => ['Accept' => 'application/json'],
            'Accepts any type' => ['Accept' => '*/*'],
            'Accepts complex collection' => ['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8'],
        ];

        // Build matrix
        foreach (self::provide_http_methods() as $method) {
            foreach ($headers as $headerDescription => $headerValues) {
                yield "{$method} × {$headerDescription}" => [$method, $headerValues];
            }
        }
    }

    /**
     * Returns headers that don't match the application/json mime.
     * @return Generator<string,string[]>
     */
    public static function provide_invalid_headers(): Generator
    {
        $headers = [
            'Accepts text types' => ['Accept' => 'text/plain,text/*;q=0.9'],
            'Accept binary types' => ['Accept' => 'application/octet-stream'],
        ];

        // Build matrix
        foreach (self::provide_http_methods() as $method) {
            foreach ($headers as $headerDescription => $headerValues) {
                yield "{$method} × {$headerDescription}" => [$method, $headerValues];
            }
        }
    }

    /**
     * Ensure a dummy route is set up before the test starts.
     * @before
     */
    public function setUpTestRoute(): void
    {
        $this->afterApplicationCreated(function () {
            Route::any('/tests/middleware/accept-json', fn () => Response::make('OK'))
                ->middleware(MustAcceptJson::class);
            Route::any('/tests/middleware/accept-json-via-alias', fn () => Response::make('OK'))
                ->middleware('accept-json');
        });
    }

    /**
     * Test if valid header combos yield a valid response.
     * @dataProvider provide_valid_headers
     */
    public function test_valid_requests(string $method, array $headers): void
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        $this->call($method, '/tests/middleware/accept-json', [], $cookies, [], $server)
            ->assertOk();
        $this->call($method, '/tests/middleware/accept-json-via-alias', [], $cookies, [], $server)
            ->assertOk();
    }

    /**
     * Ensure invalid header combo's yield an error.
     * @dataProvider provide_invalid_headers
     */
    public function test_invalid_requests(string $method, array $headers): void
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        $this->call($method, '/tests/middleware/accept-json', [], $cookies, [], $server)
            ->assertStatus(HttpResponse::HTTP_NOT_ACCEPTABLE);
        $this->call($method, '/tests/middleware/accept-json-via-alias', [], $cookies, [], $server)
            ->assertStatus(HttpResponse::HTTP_NOT_ACCEPTABLE);
    }

    /**
     * Convert HTTP headers to $_SERVER variables.
     * @param array<string,string> $headers
     * @return array<string,string>
     */
    private function buildServerParamsFromHeaders(array $headers): array
    {
        return Collection::make($headers)
            ->put('Host', 'localhost')
            ->mapWithKeys(fn ($value, $key) => [(string) Str::of("http-{$key}")->snake()->upper() => $value])
            ->toArray();
    }

    /**
     * Run the middleware on a request with the specified method, body and headers.
     * @param string $method HTTP request method
     * @param array $headers HTTP headers
     * @return TestResponse The response from the middleware
     */
    private function runMiddleware(string $method, array $headers): TestResponse
    {
        $serverParams = $this->buildServerParamsFromHeaders($headers);

        $request = Request::create('/test', $method, [], [], [], $serverParams);

        try {
            $response = (new MustAcceptJson())->handle($request, fn () => Response::make('OK'));

            return TestResponse::fromBaseResponse($response);
        } catch (HttpException $exception) {
            return TestResponse::fromBaseResponse(
                Response::make(
                    $exception->getMessage(),
                    $exception->getStatusCode(),
                    $exception->getHeaders(),
                ),
            );
        }
    }
}
