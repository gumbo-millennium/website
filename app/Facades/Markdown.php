<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\MarkdownServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string parse(string $body)
 * @method static \Illuminate\Support\HtmlString parseSafe(string $body)
 * @see \App\Contracts\MarkdownServiceContract
 */
class Markdown extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MarkdownServiceContract::class;
    }
}
