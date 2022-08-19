<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum BarcodeType: string
{
    case BARCODE_TYPE_UNSPECIFIED = 'BARCODE_TYPE_UNSPECIFIED';
    case AZTEC = 'AZTEC';
    case CODE_39 = 'CODE_39';
    case CODE_128 = 'CODE_128';
    case CODABAR = 'CODABAR';
    case DATA_MATRIX = 'DATA_MATRIX';
    case EAN_8 = 'EAN_8';
    case EAN_13 = 'EAN_13';
    case ITF_14 = 'ITF_14';
    case PDF_417 = 'PDF_417';
    case QR_CODE = 'QR_CODE';
    case UPC_A = 'UPC_A';
    case TEXT_ONLY = 'TEXT_ONLY';
}
