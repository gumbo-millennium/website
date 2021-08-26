<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Fluent;

/**
 * @method self organizationName(string $organizationName)
 * @method self title(string $title)
 * @method self givenName(string $givenName)
 * @method self familyName(string $familyName)
 * @method self email(string $email)
 * @method self phone(string $phone)
 * @method self streetAndNumber(string $streetAndNumber)
 * @method self streetAdditional(string $streetAdditional)
 * @method self postalCode(string $postalCode)
 * @method self city(string $city)
 * @method self region(string $region)
 * @method self country(string $country)
 */
class Address extends Fluent
{
    public static function make($args = [])
    {
        return new static($args);
    }
}
