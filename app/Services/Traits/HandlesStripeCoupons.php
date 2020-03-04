<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Contracts\StripeServiceContract;
use App\Models\Activity;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
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
     * Returns the computed coupon for this activity.
     * @param Activity $activity
     * @return null|Collection
     */
    public function getComputedCoupon(Activity $activity): ?Collection
    {
        // Skip if no activity is available
        if ($activity->discount_price === null) {
            return null;
        }

        // Create proper name
        $couponName = sprintf('Bijdrage %s', optional($activity->role)->title ?? 'Gumbo Millennium');

        // Assert that an end date is set
        \assert($activity->end_date instanceof CarbonInterface, 'Activity has no end date!');
        $dueDate = (clone $activity->end_date)->addDay()->getTimestamp();

        // Return collection
        return collect([
            'label' => $couponName,
            'discount' => $activity->member_discount,
            'due-date' => $dueDate
        ]);
    }

    /**
     * Returns the coupon for this activity, to apply the discount on the activity
     * @param Activity $activity
     * @param int $options Bitwise options, see OPT_ constants
     * @return null|Stripe\Coupon
     */
    public function getCoupon(Activity $activity, int $options = 0): ?Coupon
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

        // Allow no-create
        if ($options & StripeServiceContract::OPT_NO_CREATE) {
            return null;
        }

        try {
            // Get coupon parameters
            $couponData = $this->getComputedCoupon($activity);

            // Create customer
            $coupon = Coupon::create([
                'amount_off' => $couponData->get('discount'),
                'currency' => 'eur',
                'duration' => 'once',
                'name' => $couponData->get('label'),
                'redeem_by' => $couponData->get('due-date'),
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
