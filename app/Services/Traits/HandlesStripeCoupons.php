<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Models\Activity;
use App\Models\User;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

trait HandlesStripeCoupons
{
    /**
     * Returns the coupon for this activity, to apply the discount on the activity
     * @param Activity $activity
     * @return Stripe\Coupon
     */
    public function getCoupon(Activity $activity): ?Coupon
    {
        // No Coupon on activities without member discount
        if (!$activity->member_discount) {
            dd('No discount');
            return null;
        }

        // Get existing coupon
        if ($activity->stripe_coupon_id) {
            try {
                // Return customer
                return Coupon::retrieve($activity->stripe_coupon_id);
            } catch (ApiErrorException $exception) {
                dd('Four o Four', $exception);
                // Bubble any non-404 errors
                $this->handleError($exception, 404);
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
            return $coupon;
        } catch (ApiErrorException $exception) {
            dd($exception);
            // Bubble all
            $this->handleError($exception);
        }
    }
}
