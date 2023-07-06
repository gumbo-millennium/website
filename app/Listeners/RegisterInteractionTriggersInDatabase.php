<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InteractionTrigger;

class RegisterInteractionTriggersInDatabase
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(InteractionTrigger $event)
    {
        $interaction = UserInteraction::query()
            ->whereUser($event->getUser())
            ->whereInteraction($event->getInteraction())
            ->whereModel($event->getSubject())
            ->firstOrNew();

        $interaction->last_interacted = Date::now();
        $interaction->save();
    }
}
