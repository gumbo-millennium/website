<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

enum GateLabel: string
{
    case GATE_LABEL_UNSPECIFIED = 'GATE_LABEL_UNSPECIFIED';
    case GATE = 'GATE';
    case DOOR = 'DOOR';
    case ENTRANCE = 'ENTRANCE';
}
