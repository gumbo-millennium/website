<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Advoor\NovaEditorJs\NovaEditorJsField;
use HTMLPurifier;
use HTMLPurifier_Config as HTMLPurifierConfig;
use Illuminate\Support\HtmlString;

class EditorJs extends NovaEditorJsField
{
    private const ALLOWED_TAGS = 'p,br,b,strong,i,em,ul,ol,li,blockquote,pre,code,h1,h2,h3';

    private const TAG_FORMATTING = [
        'p' => 'class="leading-loose mb-4"',
        'h1' => 'class="font-bold text-2xl mb-4"',
        'h2' => 'class="font-bold text-xl mb-4"',
        'h3' => 'class="font-bold text-lg mb-4"',
        'ol' => 'class="mb-4" style="list-style: decimal inside;"',
        'ul' => 'class="mb-4" style="list-style: disc inside;"',
        'li' => 'class="mb-2"',
    ];

    private static ?HTMLPurifier $purifier = null;

    /**
     * Purify the HTML to only allow basic tags.
     * @return HtmlString
     */
    private static function purifyHtmlToBasics(string $html): string
    {
        self::$purifier ??= new HTMLPurifier(HTMLPurifierConfig::create([
            'HTML.Allowed' => self::ALLOWED_TAGS,
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ]));

        $purified = self::$purifier->purify($html);

        $formattingTags = array_keys(self::TAG_FORMATTING);
        $sources = array_map(fn ($tag) => "<{$tag}>", $formattingTags);
        $targets = array_map(fn ($tag, $style) => "<{$tag} {$style}>", $formattingTags, self::TAG_FORMATTING);

        return str_replace($sources, $targets, $purified);
    }

    public function resolveForDisplay($resource, $attribute = null)
    {
        parent::resolveForDisplay($resource, $attribute);

        if (! $this->value) {
            return;
        }

        $this->value = self::purifyHtmlToBasics((string) $this->value);
    }
}
