<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component;
use InvalidArgumentException;

final class Button extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        private string $type = 'link',
        private ?string $size = 'large',
        private ?string $style = 'light',
        private bool $withIcon = false,
        ?string $color = null,
    ) {
        if ($color) {
            trigger_deprecation('gumbo-millennium/website', 'latest', 'The color attribute is deprecated. Use the style attribute instead.');
            $this->style = $color;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure<ViewContract>
     */
    public function render(): Closure
    {
        $sizeClass = Config::get("gumbo.buttons.sizes.{$this->size}");
        $styleClass = Config::get("gumbo.buttons.styles.{$this->style}");

        throw_unless($sizeClass, InvalidArgumentException::class, "Button size {$this->size} not found");
        throw_unless($styleClass, InvalidArgumentException::class, "Button style {$this->style} not found");

        $cssClass = [$sizeClass, $styleClass];
        if ($this->withIcon) {
            $cssClass[] = 'gap-x-2';
        }

        if (in_array($this->type, ['button', 'submit', 'reset'], true)) {
            return fn (array $data) => sprintf(
                '<button %s>%s</button>',
                $data['attributes']->class($cssClass)->merge(['type' => $this->type]),
                $data['slot'],
            );
        }

        if ($this->type === 'link') {
            return fn (array $data) => sprintf(
                '<a %s>%s</a>',
                $data['attributes']->class($cssClass),
                $data['slot'],
            );
        }

        throw new InvalidArgumentException("Invalid button type {$this->type}");
    }
}
