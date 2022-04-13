<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewEnrollments;
use App\Nova\Metrics\NewUsers;
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
        return [
            new NewUsers(),
            new NewEnrollments(),
            new Help(),
        ];
    }
}
