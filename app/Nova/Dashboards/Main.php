<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewEnrollments;
use App\Nova\Metrics\NewUsers;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        $cards = [
            new NewUsers(),
            new NewEnrollments(),
        ];

        if (App::isLocal()) {
            $cards[] = new Help();
        }

        return $cards;
    }
}
