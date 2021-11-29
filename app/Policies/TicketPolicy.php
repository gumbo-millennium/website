<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user)
    {
        return $user->can('viewAny', Activity::class);
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(?User $user, Ticket $ticket)
    {
        return Gate::allows('view', $ticket->activity);
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user)
    {
        return $user->can('create', Activity::class);
    }

    /**
     * Determine whether the user can update the ticket.
     *
     * @param \App\Ticket $ticket
     */
    public function update(User $user, Ticket $ticket)
    {
        return $user->can('update', $ticket->activity);
    }

    /**
     * Determine whether the user can delete the ticket.
     *
     * @param \App\Ticket $ticket
     */
    public function delete(User $user, Ticket $ticket)
    {
        return $user->can('delete', $ticket->activity);
    }

    /**
     * Determine whether the user can restore the ticket.
     *
     * @param \App\Ticket $ticket
     */
    public function restore(User $user, Ticket $ticket)
    {
        return $user->can('restore', $ticket->activity);
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     *
     * @param \App\Ticket $ticket
     */
    public function forceDelete(User $user, Ticket $ticket)
    {
        return false;
    }
}
