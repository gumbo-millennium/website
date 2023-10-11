<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\BotQuote;

/**
 * BotQuote resource for an HTTP API, but which uses real names instead of aliases.
 */
class RealNameBotQuoteResource extends BotQuoteResource
{
    protected static function getQuoteAuthorName(BotQuote $quote): string
    {
        return $quote->user?->name ?? $quote->display_name;
    }
}
