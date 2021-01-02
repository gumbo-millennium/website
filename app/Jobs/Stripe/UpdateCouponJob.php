<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\Activity;

class UpdateCouponJob extends StripeJob
{
    /**
     * Activity
     *
     * @var \App\Models\Activity
     */
    protected Activity $activity;

    /**
     * Create new job for this activity
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StripeServiceContract $service)
    {
        $activity = $this->activity;
        $coupon = $service->getCoupon($activity);

        if (!$coupon && empty($activity->member_discount)) {
            logger()->info('No coupon and none needed', ['coupon' => $coupon]);
            return;
        }

        // Coupon is up-to-date
        if ($coupon->amount_off === $activity->member_discount) {
            logger()->info('Coupon {coupon} up-to-date', ['coupon' => $coupon]);
            return;
        }

        // Flag old coupon as deleted
        $activity->stripe_coupon_id = null;
        $activity->save(['stripe_coupon_id']);

        // Create new coupon
        if ($activity->member_discount) {
            $newCoupon = $service->getCoupon($activity);
            logger()->info('Created new coupon {coupon}', ['coupon' => $newCoupon]);
        }

        // Delete old coupon
        $coupon->delete();
        logger()->info('Deleted old coupon {coupon}', ['coupon' => $coupon]);
    }
}
