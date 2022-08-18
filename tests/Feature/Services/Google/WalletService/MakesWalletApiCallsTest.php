<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google\WalletService;

use App\Services\Google\Traits\MakesWalletApiCalls;
use Google\Client as GoogleClient;
use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MakesWalletApiCallsTest extends TestCase
{
    use MakesWalletApiCalls;

    public function test_proper_client_initialisation(): void
    {
        $response1 = new Response(200, [], json_encode($responseBody1 = ['foo' => 'bar']));
        $response2 = new Response(200, [], json_encode($responseBody2 = ['foo' => 'baz']));
        $response3 = new Response(200, [], '{]');

        $googleHttpClient = Mockery::mock(GuzzleClient::class, function (MockInterface $mock) use ($response1, $response2, $response3) {
            $mock->shouldReceive('request')
                ->times(3)
                ->with(
                    Mockery::anyOf('GET', 'PUT'),
                    Mockery::mustBe('https://example.com'),
                    Mockery::mustBe(['http_errors' => true]),
                )
                ->andReturn(
                    $response1,
                    $response2,
                    $response3,
                );
        });

        $this->googleClient = Mockery::mock(GoogleClient::class, function (MockInterface $mock) use ($googleHttpClient) {
            $mock->shouldReceive('authorize')->once()->andReturn($googleHttpClient);
        });

        $this->assertSame($googleHttpClient, $this->getGoogleClient());

        $this->assertSame($responseBody1, $this->sendRequest('GET', 'https://example.com'));
        $this->assertSame($responseBody2, $this->sendRequest('PUT', 'https://example.com'));

        $this->assertSame($this->googleHttpClient, $this->getGoogleClient());

        $this->expectException(JsonException::class);
        $this->sendRequest('GET', 'https://example.com');
    }

    public function test_body_serialisation(): void
    {
        $response = new Response(200, [], json_encode($responseBody = [
            'hello' => 'world',
        ]));

        $googleHttpClient = Mockery::mock(GuzzleClient::class, function (MockInterface $mock) use ($response) {
            $mock->shouldReceive('request')
                ->once()
                ->with(
                    Mockery::any(),
                    Mockery::any(),
                    Mockery::mustBe(['body' => '{"success":true}', 'http_errors' => true]),
                )
                ->andReturn($response);
        });

        $this->googleClient = Mockery::mock(GoogleClient::class, function (MockInterface $mock) use ($googleHttpClient) {
            $mock->shouldReceive('authorize')->once()->andReturn($googleHttpClient);
        });

        $result = $this->sendRequest('POST', 'https://example.com', ['body' => ['success' => true]]);

        $this->assertSame($responseBody, $result);
    }
}
