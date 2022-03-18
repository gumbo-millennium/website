<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $valid = $request->validate([
            'show' => [
                'nullable',
                Rule::in(['past', 'future']),
            ],
        ]);
        $showGroup = $valid['show'] ?? 'future';

        $enrollments = Enrollment::query()
            ->with(['ticket', 'user', 'activity'])
            ->forUser($request->user())
            ->whereHas(
                'activity',
                fn (Builder $query) => $query
                    ->when($showGroup === 'past', fn (Builder $query) => $query->where('end_date', '<=', Date::now())->orderBy('end_date', 'desc'))
                    ->unless($showGroup === 'past', fn (Builder $query) => $query->where('end_date', '>=', Date::now())->orderBy('end_date', 'asc')),
            )
            ->get();

        return Response::json($enrollments);

        return Response::view('tickets.index', [
            'enrollments' => $enrollments,
            'showGroup' => $showGroup,
        ]);
    }

    public function show(Request $request, string $ticket)
    {
        $enrollment = Enrollment::query()
            ->with(['ticket', 'user', 'activity'])
            ->forUser($request->user())
            ->findOrFail($ticket);

        return Response::view('pdf.ticket', [
            'enrollment' => $enrollment,
            'ticket' => $enrollment->ticket,
            'activity' => $enrollment->activity,
            'subject' => $enrollment->user,
        ]);
    }
}
