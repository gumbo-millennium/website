<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use RuntimeException;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Source;

trait HandlesStripeSources
{
    /**
     * Sources retrieved from API
     * @var array<Source>
     */
    private array $sourceCache = [];

    /**
     * Returns a single source for the given enrollment, as long as it has the
     * same bank.
     * @param Enrollment $enrollment
     * @param null|string $bank
     * @param int $options Bitwise options, see OPT_ constants
     * @return App\Contracts\Source
     */
    public function getSource(Enrollment $enrollment, ?string $bank, int $options = 0): Source
    {
        if ($enrollment->payment_source) {
            try {
                // Test cache or check locally
                // phpcs:ignore Generic.Files.LineLength.TooLong
                $source = $this->sourceCache[$enrollment->payment_source] ?? Source::retrieve($enrollment->payment_source);

                // Cache "new" result
                $this->sourceCache[$enrollment->payment_source] = $source;

                // Validation disabled, return most-recent source
                if (!$bank) {
                    return $source;
                }

                // Validation enabled, make sure we can use this source.
                if (
                    $bank &&
                    object_get($source, 'ideal.bank') === $bank &&
                    $source->status == Source::STATUS_PENDING
                ) {
                    return $source;
                }
            } catch (ApiErrorException $exception) {
                // Bubble any non-404 errors
                $this->handleError($exception, 404);
            }
        }

        // Don't return a new source on blank requests
        if (!$bank) {
            throw new RuntimeException('Not found', 404);
        }

        // Allow no-create
        if ($options & StripeServiceContract::OPT_NO_CREATE) {
            return null;
        }

        try {
            // Create customer
            $source = Source::create([
                'type' => 'ideal',
                'amount' => $enrollment->total_price,
                'currency' => 'eur',
                'flow' => 'redirect',
                'metadata' => [
                    'activity-id' => $enrollment->activity->id,
                    'enrollment-id' => $enrollment->id,
                    'user-id' => $enrollment->user->id,
                ],
                'redirect' => [
                    'return_url' => route('enroll.pay-return', ['activity' => $enrollment->activity])
                ],
                'ideal' => [
                    'bank' => $bank
                ],
                'statement_descriptor' => $enrollment->activity->full_statement
            ]);

            // Bind to customer
            // Associate new source with user
            $source = Customer::createSource($enrollment->user->stripe_id, [
                'source' => $source->id,
            ]);

            // Update user
            $enrollment->payment_source = $source->id;
            $enrollment->save(['payment_source']);

            // Cache source
            $this->sourceCache[$enrollment->payment_source] = $source;

            // Return source
            return $source;
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }

    /**
     * Builds a redirect to fulfill the Source's payment, if applicable.
     * @param Source $source
     * @return null|RedirectResponse
     */
    public function getSourceRedirect(Source $source): ?RedirectResponse
    {
        $redirectStatus = \object_get($source, 'redirect.status');
        $redirectUrl = \object_get($source, 'redirect.url');

        // Redirect to payment page
        if ($redirectStatus === Source::STATUS_PENDING && $redirectUrl) {
            // Redirect
            return response()
                ->redirectTo($redirectUrl)
                ->setPrivate();
        }

        // Can't redirect yet
        return null;
    }
}
