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
use Stripe\Source;

/**
 * Describes the Stripe service. Doesn't look like much, but this baby took
 * waaayy too much time.
 */
interface StripeServiceContract
{
    /**
     * Returns the customer for this user
     * @param User $user
     * @return Stripe\Customer
     */
    public function getCustomer(User $user): Customer;

    /**
     * Returns the coupon for this activity, to apply the discount on the activity
     * @param Activity $activity
     * @return Stripe\Coupon
     */
    public function getCoupon(Activity $activity): ?Coupon;

    /**
     * Returns a single invoice for the given Enrollment
     * @param Enrollment $enrollment
     * @return Stripe\Invoice
     */
    public function getInvoice(Enrollment $enrollment): Invoice;

    /**
     * Returns the invoice lines for this enrollment
     * @param Enrollment $enrollment
     * @return Illuminate\Support\Collection
     */
    public function getComputedInvoiceLines(Enrollment $enrollment): Collection;

    /**
     * Returns the charge for this paid Enrollement
     * @param Enrollment $enrollment
     * @return null|Stripe\Charge
     */
    public function getCharge(Enrollment $enrollment): ?Charge;

    /**
     * Returns a single source for the given enrollment, as long as it has the
     * same bank.
     * @param Enrollment $enrollment
     * @param null|string $bank
     * @return App\Contracts\Source
     */
    public function getSource(Enrollment $enrollment, ?string $bank): Source;

    /**
     * Builds a redirect to fulfill the Source's payment, if applicable.
     * @param Source $source
     * @return null|RedirectResponse
     */
    public function getSourceRedirect(Source $source): ?RedirectResponse;

    /**
     * Pays the invoice for the enrollment using the given source
     * @param Enrollment $enrollment
     * @param App\Contracts\Source $source
     * @return Stripe\Invoice
     */
    public function payInvoice(Enrollment $enrollment, Source $source): Invoice;
}
