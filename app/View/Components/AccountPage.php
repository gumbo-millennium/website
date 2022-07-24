<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AccountPage extends Page
{
    /**
     * Current page being viewed.
     */
    public string $activeRoute;

    /**
     * Should the default title be hidden?
     */
    public bool $hideTitle;

    /**
     * Title of this account page component.
     */
    public string $accountTitle;

    /**
     * List of routes for account pages.
     * @var array<string,string[]>
     */
    public iterable $accountRoutes;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        array|string $title = null,
        bool $hideFlash = false,
        bool $hideTitle = false,
        ?string $activeRoute = null,
        ?string $accountTitle = null,
    ) {
        $this->hideTitle = $hideTitle;
        $this->activeRoute = $activeRoute ?? Route::currentRouteName();
        $this->accountRoutes = Collection::make(Config::get('gumbo.account.menu'))
            ->mapWithKeys(fn (array $route) => [
                $route['route'] => [
                    __($route['title']),
                    $route['icon'],
                ],
            ]);

        // Determine account title
        $this->accountTitle = $accountTitle ?? __('My :part', [
            'part' => $this->accountRoutes->get($this->activeRoute, [__('Account')])[0],
        ]);

        // Auto-determine title if missing
        $title ??= $this->accountTitle;

        // Ensure it's not "My Account - My Account", but add otherwise
        $titleSuffix = __('My Account');
        if ($title !== $titleSuffix) {
            $title = [$title, $titleSuffix];
        }

        parent::__construct($title, $hideFlash);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return View::make('components.page-account');
    }
}
