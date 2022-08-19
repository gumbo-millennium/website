<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class SecurityAnimation extends \Google\Model
{
    /**
     * @var AnimationType
     */
    public $animationType;

    public static function create(?AnimationType $animationType): self
    {
        return new self([
            'animationType' => $animationType ?? AnimationType::ANIMATION_UNSPECIFIED,
        ]);
    }
}
