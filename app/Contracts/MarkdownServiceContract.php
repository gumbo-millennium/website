<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\HtmlString;

interface MarkdownServiceContract
{
    /**
     * Returns the body as parsed markdown. Should only contain safe HTML,
     * but no guarantees given.
     */
    public function parse(string $body): string;

    /**
     * Returns the body as safe, parsed markdown. Can be injected into a
     * page without further validation.
     */
    public function parseSafe(string $body): HtmlString;
}
