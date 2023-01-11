<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Models\BarcodeType;
use App\Helpers\Str;
use Endroid\QrCode\Builder\Builder as QRBuilder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use LogicException;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;

final class BarcodeService
{
    /**
     * Renders the given barcode as base64 encoded image.
     * Type is optional, as image may be a scalable vector graphic.
     * @param string $barcode Actual barcode, should be compatible with the given type
     * @return string base64 encoded image, might be vector
     */
    public function toBase64(BarcodeType $type, string $barcode, int $size = 400): string
    {
        switch ($type) {
            case BarcodeType::CODABAR:
            case BarcodeType::CODE39:
            case BarcodeType::CODE128:
            case BarcodeType::EAN8:
            case BarcodeType::EAN13:
                return $this->create2DBarcode($type, $barcode);
            case BarcodeType::QRCODE:
            case BarcodeType::TEXT:
            default:
                return $this->createBase64QrCode($barcode, $size);
        };
    }

    private function createBase64QrCode(string $barcode, int $size): string
    {
        return QRBuilder::create()
            ->writer(new PngWriter())
            ->encoding(new Encoding('ISO-8859-1')) // Not UTF-8 for highest compatibility
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($size)
            ->margin(0)
            ->data(Str::ascii($barcode))
            ->build()
            ->getDataUri();
    }

    private function create2DBarcode(BarcodeType $type, string $barcode): string
    {
        $generatorType = match ($type) {
            BarcodeType::CODABAR => BarcodeGenerator::TYPE_CODABAR,
            BarcodeType::CODE39 => BarcodeGenerator::TYPE_CODE_39,
            BarcodeType::CODE128 => BarcodeGenerator::TYPE_CODE_128_A,
            BarcodeType::EAN8 => BarcodeGenerator::TYPE_EAN_8,
            BarcodeType::EAN13 => BarcodeGenerator::TYPE_EAN_13,
            default => throw new LogicException('Invalid barcode type'),
        };

        $generator = new BarcodeGeneratorPNG();

        return sprintf('data:image/png;base64,%s', base64_encode($generator->getBarcode(Str::ascii($barcode), $generatorType, 2, 100)));
    }
}
