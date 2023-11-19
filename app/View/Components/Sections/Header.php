<?php

declare(strict_types=1);

namespace App\View\Components\Sections;

use Closure;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class Header extends Component
{
    public string $title;

    /**
     * @var string[] $crumbs
     */
    public array $crumbs;

    public ?string $subtitle;

    /**
     * @var array[]|string[]
     */
    public array $stats;

    public $buttons;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string $title,
        ?string $subtitle = null,
        array $crumbs = [],
        array $stats = [],
        $buttons = null,
    ) {
        // Validate crumbs
        foreach ($crumbs as $url => $label) {
            if (! is_string($url) || ! (URL::isValidUrl($url) || URL::to($url) !== $url)) {
                throw new InvalidArgumentException("Invalid URL for crumb '{$label}': {$url}");
            }

            if (! is_string($label) || empty($label)) {
                throw new InvalidArgumentException("Invalid label for crumb '{$url}': {$label}");
            }
        }

        // Validate stats
        $actualStats = [];
        foreach ($stats as $index => $value) {
            // Skip null values
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                if (is_string($index)) {
                    $actualStats[] = [
                        'icon' => $value,
                        'label' => $index,
                    ];

                    continue;
                }

                $actualStats[] = ['label' => $value];

                continue;
            }

            if (! is_array($value)) {
                throw new InvalidArgumentException("Invalid value for stat at '{$index}'");
            }

            if (($value['label'] ?? '') === '') {
                throw new InvalidArgumentException("Missing label for stat at '{$index}'");
            }

            $actualStats[] = [
                'icon' => $value['icon'] ?? null,
                'label' => $value['label'],
            ];
        }

        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->crumbs = $crumbs;
        $this->stats = $actualStats;
        $this->buttons = $buttons;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $back = last(array_keys($this->crumbs));

        return View::make('components.sections.header', [
            'back' => $back,
        ]);
    }
}
