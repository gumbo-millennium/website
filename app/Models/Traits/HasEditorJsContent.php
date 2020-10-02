<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Advoor\NovaEditorJs\NovaEditorJs;
use Illuminate\Support\Facades\Config;

/**
 * Converts contents array to HTML
 */
trait HasEditorJsContent
{
    /**
     * Adds a HTML conversion method
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
            return "<strong>HTML content not renderable</strong>";
        }

        // Convert to JSON if required
        $contents = is_string($contents) ? $contents : json_encode($contents);

        // Parse HTML
        return NovaEditorJs::generateHtmlOutput($contents);
    }
}
