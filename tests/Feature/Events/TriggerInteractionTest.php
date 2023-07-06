<?php

namespace Tests\Feature\Events;

use Illuminate\Foundation\Testing\RefreshDatabase;

class TriggerInteractionTest extends \Tests\TestCase
{
    use RefreshDatabase;

    public function testEventResultsInARegistration(): void
    {
        $user = User::factory()->create();

        $this->assertTableDoesntHave('user_interactions', [
            'user_id' => $user->id,
            'interaction' => 'register',
        ]);

        InteractionTrigger::triggerFor($user, 'register');

        $this->assertTableHas('user_interactions', [
            'user_id' => $user->id,
            'interaction' => 'register',
        ]);
    }

    public function testInteractionsAreUpdated(): void
    {
        $user = User::factory()->create();

        $interaction = InteractionTrigger::create([
            'user_id' => $user->id,
            'interaction' => 'register',
        ])

        $this->travel(5)->days();

        InteractionTrigger::triggerFor($user, 'register');

        $interactionTwo = $interaction->fresh();

        $this->assertEquals($interaction->first_interaction, $interactionTwo->first_interaction);
        $this->assertLessThan($interaction->last_interaction, $interactionTwo->last_interaction);
    }
}
