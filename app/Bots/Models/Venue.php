<?php

declare(strict_types=1);

namespace App\Bots\Models;

/**
 * @method self title(string $title)
 * @method self latitude(float $latitude)
 * @method self longitude(float $longitude)
 * @method self address(string $address)
 * @method static self make(array|string $title = [])
 * @codeCoverageIgnore
 */
class Venue extends TelegramObject
{
    public function __construct(
        $title = [],
        string $address = 'Zwolle, The Netherlands',
        float $latitude = 52.514,
        float $longitude = 5.966
    ) {
        if (is_string($title)) {
            $title = [
                'title' => $title,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        parent::__construct($title);
    }
}
