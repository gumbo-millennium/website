<?php

declare(strict_types=1);

namespace App\Enums;

enum AlertLevel: string
{
    public static function make(self|string $name): self
    {
        if ($name instanceof self) {
            return $name;
        }

        return match ($name) {
            'info' => AlertLevel::Info,
            'success' => AlertLevel::Success,
            'warning' => AlertLevel::Warning,
            'danger' => AlertLevel::Danger,
            default => AlertLevel::Info,
        };
    }

    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
