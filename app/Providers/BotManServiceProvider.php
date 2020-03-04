<?php

declare(strict_types=1);

namespace App\Providers;

use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;
use BotMan\Studio\Providers\DriverServiceProvider as ServiceProvider;

class BotManServiceProvider extends ServiceProvider
{
    /**
     * The drivers that should be loaded to
     * use with BotMan
     * @var array
     */
    protected $drivers = [
        WebDriver::class,
    ];

    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();

        foreach ($this->drivers as $driver) {
            DriverManager::loadDriver($driver);
        }
    }
}
