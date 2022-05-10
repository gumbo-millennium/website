<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\AlertLevel;
use Illuminate\View\Component;
use Illuminate\View\View as ViewInstance;

class AlertLarge extends Alert
{
    private const BODY_COLORS = [
        'info' => 'text-blue-700',
        'success' => 'text-brand-700',
        'warning' => 'text-yellow-700',
        'danger' => 'text-red-700',
    ];

    /**
     * @var string[] $errors
     */
    public array $errors;

    /**
     * Create a new component instance.
     */
    public function __construct(string|AlertLevel $level = 'info', ?string $message = null, array $errors = [])
    {
        parent::__construct($level, false, $message);

        $this->errors = $errors;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): ViewInstance
    {
        return parent::render()->with([
            'bodyColor' => self::BODY_COLORS[$this->level] ?? self::BODY_COLORS[AlertLevel::Info->value],
        ]);
    }
}
