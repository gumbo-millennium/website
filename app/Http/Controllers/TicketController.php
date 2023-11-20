<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use RuntimeException;

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

        return Response::json($enrollments->map(fn (Enrollment $enrollment) => [
            'id' => $enrollment->id,
            'description' => (string) $enrollment,
            'activity' => $enrollment->activity->name,
            'ticket' => $enrollment->ticket->title,
            'stable' => $enrollment->is_stable,
            'view' => URL::route('tickets.show', $enrollment),
            'download' => $this->getTemporaryDownloadUrl($enrollment),
        ]));

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
            'showWeb' => true,
            'enrollment' => $enrollment,
            'ticket' => $enrollment->ticket,
            'activity' => $enrollment->activity,
            'subject' => $enrollment->user,
        ]);
    }

    private function getTemporaryDownloadUrl(Enrollment $enrollment): ?string
    {
        try {
            return Storage::cloud()->temporaryUrl($enrollment->pdf_path, Date::now()->addHour());
        } catch (RuntimeException $exception) {
            if (Str::contains($exception->getMessage(), ['does not support', 'not found'])) {
                return null;
            }

            throw $exception;
        }
    }
}
