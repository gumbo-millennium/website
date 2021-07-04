<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Support\Facades\Date;

final class EventService
{
    private array $events = [];

    public function __construct()
    {
        $today = Date::today();

        $lustrumStart = Date::parse('2021-09-18 00:00:00');
        $lustrumEnd = Date::parse('2021-09-25 00:00:00');

        $this->events = [
            'april-fools' => $today->format('F jS') === 'April 1st',
            'lustrum' => $lustrumStart <= $today && $lustrumEnd >= $today,
        ];
    }

    public function eventActive(string $name): bool
    {
        return Arr::get($this->events, $name, false);
    }

    /**
     * Force-enable a special event.
     */
    public function forceEvent(string $name): void
    {
        foreach ($this->events as $event => $value) {
            $this->events[$event] = false;
        }

        Arr::put($this->events, $name, true);
    }
}
