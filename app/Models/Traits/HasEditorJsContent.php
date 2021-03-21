<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Advoor\NovaEditorJs\NovaEditorJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use JsonException;

/**
 * Converts contents array to HTML
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
            __('Failed to render body, sorry.')
        );
    }

    /**
     * Adds a HTML conversion method
     *
     * @param string|array $contents
     * @return string|null
     */
    protected function convertToHtml($contents): ?string
    {
        // Skip if empty
        if (empty($contents)) {
            return null;
        }

        // Return JSON as-is when Nova is not available
        if (!Config::get('services.features.enable-nova')) {
            return $this->renderConvertToHtmlError();
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
        } catch (\Throwable $renderException) {
            report(new InvalidArgumentException(sprintf(
                'Caught [%s] when trying to render %s (key %s): %s',
                get_class($renderException),
                static::class,
                $this instanceof Model ? $this->getKey() : '???',
                $renderException->getMessage()
            ), 0, $renderException));

            return $this->renderConvertToHtmlError();
        }
    }
}
