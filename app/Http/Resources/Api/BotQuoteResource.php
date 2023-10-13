<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\BotQuote;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * An API representation of a BotQuote, without giving away too much
 * confidential information.
 *
 * @property-read \App\Models\BotQuote $resource
 */
class BotQuoteResource extends JsonResource
{
    /**
     * Get the author name for the given quote.
     */
    protected static function getQuoteAuthorName(BotQuote $quote): string
    {
        return $quote->user?->alias ?? $quote->display_name;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $quote = $this->resource;

        return [
            'id' => $quote->id,
            'author' => static::getQuoteAuthorName($quote),
            'quote' => $quote->formatted_quote->toHtml(),
            'date' => $quote->created_at->startOfMinute(),
            'author_verified' => $quote->user !== null,
            'quote_sent' => $quote->submitted_at !== null,
        ];
    }
}
