<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use Artesaos\SEOTools\Facades\SEOMeta;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Minimal extends Component
{
    /**
     * The page title.
     */
    public string $title;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array|string $title = '') {
        // Build title
        if (empty($title)) {
            $title = 'Studentenvereniging Gumbo Millennium';
        } else {
            $title = is_string($title) ? explode(' - ', $title) : $title;
            if (! Str::endsWith(last($title), 'Gumbo Millennium')) {
                $title[] = 'Studentenvereniging Gumbo Millennium';
            }
            $title = implode(' - ', $title);
        }

        SEOMeta::setTitle($title, false);
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return View::make('components.layout.page.minimal');
    }
}
