<?php

declare(strict_types=1);

namespace App\View\Components;

use Artesaos\SEOTools\Facades\SEOMeta;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Page extends Component
{
    /**
     * The page title.
     */
    public string $title;

    /**
     * Should the page's flash message be hidden.
     */
    public bool $hideFlash;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        array|string $title = '',
        bool $hideFlash = false,
    ) {
        // Build title
        if (empty($title)) {
            $title = 'Studentenvereniging Gumbo Millennium';
        } else {
            $title = is_string($title) ? explode(' - ', $title) : $title;
            if (Str::endsWith($title, 'Gumbo Millennium')) {
                $title[] = 'Studentenvereniging Gumbo Millennium';
            }
            $title = implode(' - ', $title);
        }

        SEOMeta::setTitle($title, false);
        $this->title = $title;
        ;

        // Other properties
        $this->hideFlash = $hideFlash;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return View::make('components.page');
    }
}
