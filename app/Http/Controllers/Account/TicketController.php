<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketController extends Controller
{
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();
        $showOld = (bool) $request->query('include-past', false);

        $activities = Activity::query()
            ->whereAvailable($user)
            ->unless($showOld, fn ($query) => $query->where([
                ['end_date', '>=', Date::now()->subDay()],
            ]))
            ->whereHas('enrollments', fn ($query) => $query->where('user_id', $user->id)->active())
            ->with('enrollments', fn ($query) => $query->where('user_id', $user->id)->active())
            ->get()
            ->sortBy('start_date')
            ->each(fn (Activity &$activity) => $activity->enrollment = $activity->enrollments->first());

        return Response::view('account.tickets', [
            'activities' => $activities,
        ]);
    }

    public function download(Request $request, string $id): StreamedResponse|RedirectResponse
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::query()
            ->stable()
            ->whereId($id)
            ->whereHas('user', fn ($q) => $q->whereId($request->user()->id))
            ->with('activity')
            ->firstOrFail();

        if (! $enrollment->pdfExists()) {
            throw new NotFoundHttpException();
        }

        $filename = sprintf(
            'Ticket %s (%d).pdf',
            Str::of($enrollment->activity->name)
                ->ascii()
                ->replace("[\"']", '')
                ->toString(),
            $enrollment->id,
        );

        $disk = Storage::disk($enrollment->pdf_disk);

        // Try to use a simple redirect.
        if ($disk->providesTemporaryUrls()) {
            return Response::redirectTo(
                $disk->temporaryUrl(
                    $enrollment->pdf_path,
                    Date::now()->addMinutes(5),
                    [
                        'ResponseContentType' => 'application/pdf',
                        'ResponseContentDisposition' => "attachment; filename=\"{$filename}\".pdf",
                    ],
                ),
            );
        }

        // Otherwise, stream it
        return $disk->download($enrollment->pdf_path, $filename, [
            'Cache-Control' => 'no-cache,no-store,must-revalidate',
        ]);
    } // penis
}
