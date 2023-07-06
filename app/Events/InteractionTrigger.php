<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class InteractionTrigger
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public static triggerFor(User $user, string $interaction, ?Model $subject): static
    {
        $instance = new static($user, $interaction, $subject);
        Event::dispatch($instance);

        return $instance;
    }

    public static triggerForUser(string $interaction, ?Model $subject): static
    {
        $user = Auth::user();
        throw_unless($user, \RuntimeException::class, 'No user logged in');

        return self::triggerFor($user, $interaction, $subject);
    }

    /**
     * The user performing the action.
     */
    private User $user;

    /**
     * The interaction in question.
     */
    private string $interaction;

    /**
     * Possible model that's part of this interaction.
     */
    private ?Model $subject;

    /**
     * Create a new interaction.
     * Not recommended, use triggerForUser() instead.
     */
    public function __construct(User $user, string $interaction, ?Model $subject = null)
    {
        $this->user = $user;
        $this->interaction = $interaction;
        $this->subject = $subject;
    }

    /**
     * Get the user performing the action.
     *
     * @return \App\Models\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get the interaction in question.
     *
     * @return string
     */
    public function getInteraction(): string
    {
        return $this->interaction;
    }

    /**
     * Get the possible model that's part of this interaction.
     *
     * @return \App\Models\Model|null
     */
    public function getSubject(): ?Model
    {
        return $this->subject;
    }
}
