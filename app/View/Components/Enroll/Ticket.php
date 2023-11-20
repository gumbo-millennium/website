<?php

declare(strict_types=1);

namespace App\View\Components\Enroll;

use App\Models\Ticket as TicketModel;
use Closure;
use Illuminate\View\Component;

class Ticket extends Component
{
    public function __construct(
        public TicketModel $ticket,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $ticketsAvailable = min(
            $this->ticket->quantity ?? PHP_INT_MAX,
            $this->ticket->activity->seats ?? PHP_INT_MAX,
        );
        $ticketsRemaining = min(
            $this->ticket->quantity_available ?? PHP_INT_MAX,
            $this->ticket->activity->available_seats ?? PHP_INT_MAX,
        );

        if ($ticketsAvailable === PHP_INT_MAX) {
            $ticketsAvailable = null;
            $ticketsRemaining = null;
        }

        return view('components.enroll.ticket', [
            'ticket' => $this->ticket,
            'ticketsAvailable' => $ticketsAvailable,
            'ticketsRemaining' => $ticketsRemaining,
        ]);
    }
}
