<?php

namespace App\Listeners;

use App\Events\InteractionTrigger;

class RegisterInteractionTriggersInDatabase
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\InteractionTrigger  $event
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
