<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Models\Activity;
use Stripe\Coupon;
use Stripe\Exception\ApiErrorException;

trait HandlesStripeCoupons
{
    /**
     * Coupons retrieved from API
     * @var array<Coupon>
     */
    private array $couponCache = [];

    /**
     * Returns the coupon for this activity, to apply the discount on the activity
     * @param Activity $activity
     * @return Stripe\Coupon
     */
    public function getCoupon(Activity $activity): ?Coupon
    {
        // No Coupon on activities without member discount
        if (!$activity->member_discount) {
            logger()->info('Tried to get coupon for activity without discount', compact('activity'));
            return null;
        }

        // Return from cache
        if (!empty($this->couponCache[$activity->stripe_coupon_id])) {
            return $this->couponCache[$activity->stripe_coupon_id];
        }

        // Get existing coupon
        if ($activity->stripe_coupon_id) {
            try {
                // Get coupon
                $coupon = Coupon::retrieve($activity->stripe_coupon_id);

                // Cache coupon
                $this->couponCache[$activity->stripe_coupon_id] = $coupon;

                // Return coupon
                return $coupon;
            } catch (ApiErrorException $exception) {
                // Bubble any non-404 errors
                $this->handleError($exception, 404);

                // Quietly weep
                logger()->info('Failed to find discount for {activity}', compact('activity'));
            }
        }

        try {
            // Create proper name
            $couponName = sprintf('Bijdrage %s', optional($activity->role)->title ?? 'Gumbo Millennium');

            // Create customer
            $coupon = Coupon::create([
                'amount_off' => $activity->member_discount,
                'currency' => 'eur',
                'duration' => 'once',
                'name' => $couponName,
                'redeem_by' => optional($activity->end_date)->getTimestamp() ?? now()->addMonths(6)->getTimestamp()
            ]);

            // Update activity
            $activity->stripe_coupon_id = $coupon->id;
            $activity->save(['stripe_coupon_id']);

            // Return customer
            return $this->couponCache[$activity->stripe_coupon_id] = $coupon;
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }
}
