<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Stripe\Charge;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\Refund;
use Stripe\Source;

/**
 * Describes the Stripe service. Doesn't look like much, but this baby took
 * waaayy too much time.
 */
interface StripeServiceContract
{
    public const OPT_NO_CREATE = 1;

    public const REFUND_REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    public const REFUND_DUPLICATE = 'duplicate';
    public const REFUND_FRAUDULENT = 'fraudulent';

    public const CREDIT_DUPLICATE = 'duplicate';
    public const CREDIT_FRAUDULENT = 'fraudulent';
    public const CREDIT_ORDER_CHANGE = 'order_change';
    public const CREDIT_PRODUCT_UNSATISFACTORY = 'product_unsatisfactory';

    /**
     * Returns the customer for this user
     *
     * @param User $user
     * @param int $options Bitwise options, see OPT_ constants
     * @return Stripe\Customer|null
     */
    public function getCustomer(User $user, int $options = 0): ?Customer;

    /**
     * Returns the computed coupon for this activity.
     *
     * @param Activity $activity
     * @return Collection|null
     */
    public function getComputedCoupon(Activity $activity): ?Collection;

    /**
     * Returns the coupon for this activity, to apply the discount on the activity
     *
     * @param Activity $activity
     * @param int $options Bitwise options, see OPT_ constants
     * @return Stripe\Coupon|null
     */
    public function getCoupon(Activity $activity, int $options = 0): ?Coupon;

    /**
     * Returns a single invoice for the given Enrollment
     *
     * @param Enrollment $enrollment
     * @param int $options Bitwise options, see OPT_ constants
     * @return Stripe\Invoice|null
     */
    public function getInvoice(Enrollment $enrollment, int $options = 0): ?Invoice;

    /**
     * Returns the invoice lines for this enrollment
     *
     * @param Enrollment $enrollment
     * @return Illuminate\Support\Collection
     */
    public function getComputedInvoiceLines(Enrollment $enrollment): Collection;

    /**
     * Returns the charge for this paid Enrollement
     *
     * @param Enrollment $enrollment
     * @return Stripe\Charge|null
     */
    public function getCharge(Enrollment $enrollment): ?Charge;

    /**
     * Returns a single source for the given enrollment, as long as it has the
     * same bank.
     *
     * @param Enrollment $enrollment
     * @param string|null $bank
     * @param int $options Bitwise options, see OPT_ constants
     * @return Stripe\Source|null
     */
    public function getSource(Enrollment $enrollment, ?string $bank, int $options = 0): ?Source;

    /**
     * Builds a redirect to fulfill the Source's payment, if applicable.
     *
     * @param Source $source
     * @return RedirectResponse|null
     */
    public function getSourceRedirect(Source $source): ?RedirectResponse;

    /**
     * Pays the invoice for the enrollment using the given source
     *
     * @param Enrollment $enrollment
     * @param App\Contracts\Source $source
     * @return Stripe\Invoice
     */
    public function payInvoice(Enrollment $enrollment, Source $source): Invoice;

    /**
     * Creates a refund for the given enrollment, which will issue a refund for the
     * charge, and then add that refund as a credit note on the invoice.
     *
     * @param Enrollment $enrollment
     * @param string $reason
     * @param int|null $amount
     * @return Refund
     */
    public function createRefund(Enrollment $enrollment, string $reason, ?int $amount): Refund;
}
