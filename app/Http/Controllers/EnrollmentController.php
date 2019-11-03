<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Enrollment handler, WIP
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EnrollmentController extends Controller
{
    /**
     * Returns array with enrollment and locking status
     *
     * @param Activity $activity
     * @param User|null $user
     * @return bool[]
     */
    public static function enrollmentStatus(Activity $activity, ?User $user): array
    {
        // Build default
        $status = [
            'enrolled' => false,
            'locked' => false,
            'paid' => ($activity->price_guest > 0),
        ];

        // Return default if no user is set
        if (!$user) {
            return $status;
        }

        // Get enrollments, including deleted ones
        $userEnrollment = $activity->enrollments()
            ->where('user_id', $user->id)
            ->withTrashed()
            ->get();

        /** @var Enrollment $enrollment */
        foreach ($userEnrollment as $enrollment) {
            // Flag as enrolled if they are
            if (!$enrollment->trashed()) {
                $status['enrolled'] = true;
                continue;
            }

            // Check payments for refunds
            $payments = $enrollment->payments;

            /** @var Payment $payment */
            foreach ($payments as $payment) {
                if (!$payment->is_refunded) {
                    continue;
                }

                // Flag as locked, since the user is costing us
                // too much money.
                $status['locked'] = true;
                break;
            }
        }

        // Add flag if payment is required
        $status['paid'] = (
            ($user->is_member && $activity->price_member > 0) ||
            (!$user->is_member && $activity->price_guest > 0)
        );

        // Return status when done
        return $status;
    }

    /**
     * Ensure logged in users when enrolling
     */
    public function __construct()
    {
        // Ensure users are logged in and verified
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Get user
        $user = $request->user();

        // Listing overview
        $enrollments = $user->enrollments()
            ->with(['activity'])
            ->where('activity.end_date', '>', today()->subDay())
            ->orderBy('activity.start_date', 'ASC')
            ->orderBy('enrollment.created_at', 'AC')
            ->get();

        // Render view
        return view('enrollments.index', [
            'enrollments' => $enrollments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Activity $activity
     * @return Response
     */
    public function create(Request $request, Activity $activity)
    {
        // Get user and enrollment status
        $user = $request->user();
        $data = self::enrollmentStatus($activity, $user);

        // Check if already enrolled
        if ($data['enrolled']) {
            $enrollment = $user->enrollments()->whereActivityId($activity->id);
            if ($enrollment->paid || !$data['paid']) {
                return redirect()->back()->with([
                    'message' => 'Je bent al ingeschreven op deze activiteit.'
                ]);
            }

            // Redirect to payment
            return redirect()->route('enroll.pay', compact('activity'));
        }

        // Check if locked
        if ($data['locked']) {
            return redirect()->back()->with([
                'message' => 'Je bent uitgesloten van deelname aan dit evenement.'
            ]);
        }

        // Start transaction
        DB::beginTransaction();

        // Check if there's still room
        $count = Enrollment::whereActivityId($activity->id)->lockForUpdate()->count();

        // Abort if no room is left
        if ($count >= $activity->seats) {
            DB::rollBack();

            return redirect()->back()->with([
                'message' => 'Sorry, er is geen plek meer in deze activiteit.'
            ]);
        }

        // Add enrollment
        $enroll = Enrollment::enroll($user, $activity);
        $enroll->save();

        // Commmit
        DB::commit();

        // Redirect to activity if not paid
        if (!$data['paid']) {
            return redirect()->back()->with([
                'message' => 'Je bent ingeschreven op deze activiteit.'
            ]);
        }

        // Redirect to paymentp
        return redirect()->route('enroll.pay', compact('activity'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function show(Enrollment $enrollment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function edit(Enrollment $enrollment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Enrollment  $enrollment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Enrollment $enrollment)
    {
        //
    }
}
