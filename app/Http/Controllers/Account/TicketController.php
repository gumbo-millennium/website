<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Facades\Enroll;
use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Services\Google\WalletService;
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

    /**
     * Redirect the user to their personal redeem URL for the given activity.
     * Only if the conditions allow it.
     */
    public function addToWallet(WalletService $walletService, Request $request, Activity $activity): RedirectResponse
    {
        $enrollment = Enroll::getEnrollment($activity);

        if (! $enrollment) {
            flash()->warning(__(
                "You're not currently enrolled into :activity.",
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('account.tickets');
        }

        $jwtUrl = $walletService->getImportUrlForEnrollment($request->user(), $enrollment);

        if ($enrollment->is_stable && $activity->end_date > Date::now() && $jwtUrl) {
            return Response::redirectTo($jwtUrl);
        }

        flash()->warning(__(
            "You can't add this ticket for :activity to your Google Wallet.",
            ['activity' => $activity->name],
        ));

        return Response::redirectToRoute('account.tickets');
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
