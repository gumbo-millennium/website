<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Contracts\SponsorService as SponsorServiceContract;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;

class GlobalComposer
{
    public function __construct(private readonly SponsorServiceContract $sponsorService)
    {
        //
    }

    public function compose(View $view): void
    {
        $view->with([
            'sponsorService' => $this->sponsorService,
            'user' => Request::user(),
        ]);
    }
}
