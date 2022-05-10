<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\AlertLevel;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Illuminate\View\View as ViewInstance;

class Alert extends Component
{
    private const ALERT_CLASSES = [
        'info' => [
            'containerColor' => 'bg-blue-50',
            'iconName' => 'solid/info-circle',
            'iconColor' => 'text-blue-400',
            'textColor' => 'text-blue-800',
        ],
        'success' => [
            'containerColor' => 'bg-brand-50',
            'iconName' => 'solid/check-circle',
            'iconColor' => 'text-brand-400',
            'textColor' => 'text-brand-800',
        ],
        'warning' => [
            'containerColor' => 'bg-yellow-50',
            'iconName' => 'solid/exclamation-circle',
            'iconColor' => 'text-yellow-400',
            'textColor' => 'text-yellow-800',
        ],
        'danger' => [
            'containerColor' => 'bg-red-50',
            'iconName' => 'solid/times-circle',
            'iconColor' => 'text-red-400',
            'textColor' => 'text-red-800',
        ],
    ];

    public string $level;

    public bool $dismissable;

    public ?string $message;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string|AlertLevel $level = AlertLevel::Info, bool $dismissable = true, ?string $message = null)
    {
        $this->level = AlertLevel::make($level)->value;
        $this->dismissable = $dismissable;
        $this->message = $message;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): ViewInstance
    {
        return View::make('components.alert', array_merge(
            self::ALERT_CLASSES[AlertLevel::Info->value],
            self::ALERT_CLASSES[$this->level],
        ));
    }
}
