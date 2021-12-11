<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Advoor\NovaEditorJs\NovaEditorJs;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;
use Throwable;

/**
 * Converts contents array to HTML.
 */
trait HasEditorJsContent
{
    protected string $htmlConversionErrorTemplate = <<<'HTML'
    <div class="border.rounded-lg.p-4.border-orange-primary-1">
        <p class="font-bold text-orange-primary-1">%s</p>
    </div>
    HTML;

    protected function renderConvertToHtmlError(): string
    {
        return sprintf(
            $this->htmlConversionErrorTemplate,
            __('Failed to render body, sorry.'),
        );
    }

    /**
     * Adds a HTML conversion method.
     *
     * @param array|string $contents
     */
    protected function convertToHtml($contents): ?string
    {
        // Skip if empty
        if (empty($contents)) {
            return null;
        }

        // Clean up contents
        try {
            if (is_string($contents)) {
                $contents = json_decode($contents, true, JSON_THROW_ON_ERROR);
            } elseif (is_object($contents)) {
                $contents = json_decode(json_encode($contents, JSON_THROW_ON_ERROR), true, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $jsonException) {
            report($jsonException);
        }

        // Parse HTML
        try {
            return NovaEditorJs::generateHtmlOutput($contents);
        } catch (Throwable $renderException) {
            report(new InvalidArgumentException(sprintf(
                'Caught [%s] when trying to render %s (key %s): %s',
                get_class($renderException),
                static::class,
                $this instanceof Model ? $this->getKey() : '???',
                $renderException->getMessage(),
            ), 0, $renderException));

            return $this->renderConvertToHtmlError();
        }
    }
}
