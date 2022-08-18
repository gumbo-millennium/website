<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Google;

use App\Enums\AlbumVisibility;
use App\Services\Google\WalletObjects\Barcode;
use App\Services\Google\WalletObjects\DateTime;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Services\Google\DummySmartModel;

class SmartModelTest extends TestCase
{
    /**
     * Test values without casts or enums.
     */
    public function test_plain_values(): void
    {
        $model = new DummySmartModel([
            'all' => [
                'the' => 'yes',
            ],
            'little' => false,
            'lights' => true,
        ]);

        $this->assertSame(['the' => 'yes'], $model->all);
        $this->assertSame(false, $model->little);
        $this->assertSame(true, $model->lights);
    }

    /**
     * Test enum casting.
     */
    public function test_enum_cast(): void
    {
        $model = new DummySmartModel([
            'visibility' => 'public',
        ]);
        $this->assertInstanceOf(AlbumVisibility::class, $model->visibility);
        $this->assertSame(AlbumVisibility::Public, $model->visibility);
    }

    /**
     * Test array-model casting.
     */
    public function test_model_cast(): void
    {
        $model = new DummySmartModel([
            'barcode' => [
                'value' => $barcodeValue = '12345',
                'type' => $barcodeType = 'CODABAR',
                'alternateText' => $barcodeAlternateText = 'Reservation 12345',
            ],
        ]);

        $this->assertInstanceOf(Barcode::class, $model->barcode);
        $this->assertSame($barcodeValue, $model->barcode->value);
        $this->assertSame($barcodeType, $model->barcode->type);
        $this->assertSame($barcodeAlternateText, $model->barcode->alternateText);
    }

    /**
     * Test casting using array-definitions.
     */
    public function test_array_cast(): void
    {
        $model = new DummySmartModel([
            'dates' => [
                ['date' => '2020-01-01T00:00:00+00:00'],
                ['date' => '2020-01-02T00:00:00+00:00'],
                ['date' => '2020-01-03T00:00:00+00:00'],
            ],
        ]);

        $this->assertContainsOnlyInstancesOf(DateTime::class, $model->dates);
        $this->assertCount(3, $model->dates);

        $this->assertSame('2020-01-01T00:00:00+00:00', $model->dates[0]->date);
        $this->assertSame('2020-01-02T00:00:00+00:00', $model->dates[1]->date);
        $this->assertSame('2020-01-03T00:00:00+00:00', $model->dates[2]->date);
    }

    public function test_empty_states(): void
    {
        $model = new DummySmartModel([
            'barcode' => null,
            'visibility' => null,
            'dates' => [],
        ]);

        $this->assertNull($model->barcode);
        $this->assertNull($model->visibility);
        $this->assertEmpty($model->dates);
    }
}
