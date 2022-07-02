<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Laravel\Nova\Nova;

class Header extends Component
{
    public function __construct(public bool $transparent = false, public bool $simple = false)
    {
        // noop
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $links = array_filter([
            [
                'title' => 'Mijn account',
                'href' => route('account.index'),
                'icon' => 'solid/user',
            ],
            [
                'title' => 'Mijn wist-je-datjes',
                'href' => route('account.quotes'),
                'icon' => 'solid/comment-dots',
            ],
            Gate::allows('enter-admin') ? [
                'title' => 'Administratie',
                'href' => Nova::path(),
                'icon' => 'solid/cogs',
            ] : null,
        ]);

        return View::make('components.layout.header', [
            'user' => Auth::user(),
            'accountLinks' => $links,
            'desktopMenuItems' => config('gumbo.layout.menu.desktop'),
            'mobileMenuItems' => config('gumbo.layout.menu.mobile.main'),
            'mobileMenuFooter' => config('gumbo.layout.menu.mobile.footer'),
        ]);
    }
}
