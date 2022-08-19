<?php

declare(strict_types=1);

namespace Tests\Fixtures\Services\Google;

use App\Services\Google\SmartModel;

class DummySmartModel extends SmartModel
{
    protected array $casts = [
        'user' => \App\Models\User::class,
        'barcode' => \App\Services\Google\WalletObjects\Barcode::class,
        'dates' => [\App\Services\Google\WalletObjects\DateTime::class],
    ];

    protected array $enums = [
        'visibility' => \App\Enums\AlbumVisibility::class,
    ];
}
