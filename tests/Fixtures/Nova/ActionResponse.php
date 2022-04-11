<?php

declare(strict_types=1);

namespace Tests\Fixtures\Nova;

use Illuminate\Support\Collection;
use Tests\TestCase as PHPUnit;

class ActionResponse
{
    /**
     * @var array
     */
    private $response;

    public function __construct($response)
    {
        $this->response = Collection::make($response)->toArray();
    }

    public function assertOk(): self
    {
        PHPUnit::assertNotEmpty($this->response, 'Failed asserting response is not empty');

        return $this;
    }

    public function assertDownload(?string $url = null, ?string $filename = null): self
    {
        PHPUnit::assertEquals([
            'download',
            'name',
        ], array_keys($this->response), 'Response is not a download response');

        if ($url != null) {
            PHPUnit::assertEquals($url, $this->response['download'], 'Download URL is not correct');
        }

        if ($filename != null) {
            PHPUnit::assertEquals($filename, $this->response['name'], 'Download filename is not correct');
        }

        return $this;
    }

    public function assertMessage(?string $message = null): self
    {
        PHPUnit::assertArrayHasKey('message', $this->response, 'Failed asserting message is present');

        if ($message != null) {
            PHPUnit::assertStringContainsString($message, $this->response['message'], 'Failed asserting message contains correct body');
        }

        return $this;
    }

    public function assertDanger(?string $message = null): self
    {
        PHPUnit::assertArrayHasKey('danger', $this->response, 'Failed asserting danger message is present');

        if ($message != null) {
            PHPUnit::assertStringContainsString($message, $this->response['danger'], 'Failed asserting danger message contains correct body');
        }

        return $this;
    }

    public function assertDeleted(): self
    {
        PHPUnit::assertArrayHasKey('deleted', $this->response, 'Failed asserting deleted flag is present');

        return $this;
    }

    public function assertRedirect(?string $url = null): self
    {
        PHPUnit::assertArrayHasKey('redirect', $this->response, 'Failed asserting redirect is present');

        if ($url != null) {
            PHPUnit::assertEquals($url, $this->response['redirect'], 'Failed asserting redirect URL is correct');
        }

        return $this;
    }

    public function assertOpensInNewTab(): self
    {
        PHPUnit::assertArrayHasKey('openInNewTab', $this->response, 'Failed asserting openInNewTab flag is set');

        return $this;
    }
}
