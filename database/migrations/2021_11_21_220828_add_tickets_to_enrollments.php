<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;

class AddTicketsToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $activities = Activity::withoutGlobalScopes()->cursor();

        foreach ($activities as $activity) {
            $regularTicket = $activity->tickets()->create([
                'title' => __('Regular ticket'),

                'available_from' => $activity->enrollment_start,
                'available_until' => $activity->enrollment_end,

                'members_only' => false,
                'price' => $activity->price,
                'quantity' => $activity->seats,
            ]);

            $memberTicket = null;
            if ($activity->member_discount > 0) {
                $memberTicket = $activity->tickets()->create([
                    'title' => __('Member ticket'),

                    'available_from' => $activity->enrollment_start,
                    'available_until' => $activity->enrollment_end,

                    'members_only' => true,
                    'price' => $activity->price - $activity->member_discount,
                    'quantity' => $activity->discount_count ?? $activity->seats,
                ]);
            }

            /** @var Enrollment $enrollment */
            foreach ($activity->enrollments()->withoutGlobalScopes()->get() as $enrollment) {
                $ticket = $regularTicket;

                if ($memberTicket && $enrollment->is_discounted) {
                    $ticket = $memberTicket;
                }

                $enrollment->ticket()->associate($ticket);
                $enrollment->save();
            }
        }
    }
}
