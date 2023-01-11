<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Enums\Models\BarcodeType;
use App\Services\BarcodeService;
use PHPUnit\Framework\TestCase;

class BarcodeServiceTest extends TestCase
{
    /**
     * @dataProvider provideBasicCreation
     */
    public function test_basic_creation(BarcodeType $type, string $value): void
    {
        $service = new BarcodeService();

        $result = $service->toBase64($type, $value);
        $this->assertNotNull($result);
    }

    public function provideBasicCreation(): iterable
    {
        yield [BarcodeType::CODABAR, '123456789'];
        yield [BarcodeType::CODE39, '123456789'];
        yield [BarcodeType::CODE128, '081231723897'];
        yield [BarcodeType::EAN8, '40170725'];
        yield [BarcodeType::EAN13, '081231723897'];
        yield [BarcodeType::QRCODE, 'Hello World!'];
        yield [BarcodeType::TEXT, 'I am a bunch of words'];
    }
}
