<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Google\Traits;

use App\Services\Google\Traits\DeepComparesArraysAndObjects;
use Google_Service_Walletobjects_EventTicketObject;
use Google_Service_Walletobjects_Image;
use Google_Service_Walletobjects_ImageUri;
use Google_Service_Walletobjects_LocalizedString;
use Google_Service_Walletobjects_TranslatedString;
use PHPUnit\Framework\TestCase;

class DeepComparesArraysAndObjectsTest extends TestCase
{
    use DeepComparesArraysAndObjects;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_expected_behaviour()
    {
        $expected = [
            'eventId' => 'RandomId123',
            'eventName' => [
                'defaultValue' => [
                    'value' => 'My Localized Title',
                ],
            ],
            'logo' => [
                'sourceUri' => [
                    'uri' => 'https://example.com/logo.png',
                ],
            ],
            'confirmationCodeLabel' => 'ORDER_NUMBER',
            'issuerName' => 'Steve Test',
            'countryCode' => 'CTRY',
        ];

        $actual = new Google_Service_Walletobjects_EventTicketObject([
            'kind' => 'walletobjects#eventTicketClass',
            'eventName' => new Google_Service_Walletobjects_LocalizedString([
                'kind' => 'walletobjects#localizedString',
                'translatedValues' => [
                    new Google_Service_Walletobjects_TranslatedString([
                        'kind' => 'walletobjects#translatedString',
                        'language' => 'nl',
                        'value' => 'Titel van het evenement',
                    ]),
                ],
                'defaultValue' => new Google_Service_Walletobjects_TranslatedString([
                    'kind' => 'walletobjects#translatedString',
                    'language' => 'en',
                    'value' => 'Title of the event',
                ]),
            ]),
            'logo' => new Google_Service_Walletobjects_Image([
                'kind' => 'walletobjects#image',
                'sourceUri' => new Google_Service_Walletobjects_ImageUri([
                    'kind' => 'walletobjects#uri',
                    'uri' => 'https://example.net/logo.png',
                    'description' => 'Picture of a logo',
                ]),
            ]),
            'issuerName' => null,
        ]);

        /** @var Google_Service_Walletobjects_EventTicketObject $result */
        $result = $this->deepCompareArrayToObject($expected, $actual);

        // Check types
        $this->assertInstanceOf(Google_Service_Walletobjects_EventTicketObject::class, $result);
        $this->assertInstanceOf(Google_Service_Walletobjects_LocalizedString::class, $result->eventName);
        $this->assertInstanceOf(Google_Service_Walletobjects_TranslatedString::class, $result->eventName->defaultValue);
        $this->assertInstanceOf(Google_Service_Walletobjects_ImageUri::class, $result->logo->sourceUri);

        // Check some types are missing
        $this->assertEmpty($result->eventName->translatedValues);
        $this->assertEmpty($result->eventName->defaultValue->language);

        // Compare basic values
        $this->assertSame('RandomId123', $result->eventId);
        $this->assertSame('Steve Test', $result->issuerName);
        $this->assertSame('CTRY', $result->countryCode);
        $this->assertSame('ORDER_NUMBER', $result->confirmationCodeLabel);

        // Compare more complex values
        $this->assertSame('My Localized Title', $result->eventName->defaultValue->value);
    }
}
