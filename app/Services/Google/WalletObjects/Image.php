<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Image extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var ImageUri
     */
    public $sourceUri;

    /**
     * @var LocalizedString
     */
    public $contentDescription;

    public static function create(string $url, ?string $description = null, string $descriptionLocale = 'nl'): self|string
    {
        if (empty($url)) {
            return self::NULL_VALUE;
        }

        return new self([
            'sourceUri' => new ImageUri([
                'uri' => url($url),
            ]),
            'contentDescription' => LocalizedString::create($descriptionLocale, $description),
        ]);
    }
}
