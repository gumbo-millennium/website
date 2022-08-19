<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Uri extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $description;

    /**
     * @var LocalizedString
     */
    public $localizedDescription;

    /**
     * @var string
     */
    public $id;

    public static function create(string $uri, string $description, ?LocalizedString $localizedDescription = null): self
    {
        return new self(array_filter([
            'uri' => $uri,
            'description' => $description,
            'localizedDescription' => $localizedDescription,
        ]));
    }
}
