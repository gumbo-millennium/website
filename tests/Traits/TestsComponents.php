<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Helpers\Str;
use Blade;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Testing\TestResponse;
use RuntimeException;

trait TestsComponents
{
    /**
     * Renders the given component.
     */
    protected function renderComponent(string $component, array $args = []): TestResponse
    {
        $componentInstance = App::make($component, $args);

        try {
            return new TestResponse(
                Response::make(Blade::renderComponent($componentInstance)),
            );
        } catch (Exception $exception) {
            $componentName = Str::of($component)
                ->after(\App\View\Components::class)
                ->trim('\\')
                ->replace('\\', '-separator-')
                ->kebab()
                ->replace('-separator-', '.');

            throw new RuntimeException("Failed to render component {$componentName}!", $exception->getCode(), $exception);
        }
    }
}
