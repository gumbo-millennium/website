<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Models\Enrollment;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Source;

trait HandlesStripeInvoices
{
    /**
     * Returns a single invoice for the given Enrollment
     * @param Enrollment $enrollment
     * @return Stripe\Invoice
     */
    public function getInvoice(Enrollment $enrollment): Invoice
    {
        // Forward to locked Create Enrollment method
        // Get a 1 minute lock on this user
        $lock = Cache::lock("stripe.invoice.{$enrollment->user->id}", 60);
        try {
            // Block for max 15 seconds
            $lock->block(15);

            // Reload model
            $enrollment->refresh();

            // Check API first (but inside the lock, so we don't create duplicate invoices)
            if ($enrollment->payment_invoice) {
                try {
                    // Return invoice
                    return Invoice::retrieve($enrollment->payment_invoice);
                } catch (ApiErrorException $exception) {
                    // Bubble any non-404 errors
                    $this->handleError($exception);
                }
            }

            // Create invoice
            return $this->createInvoice($enrollment);
        } catch (LockTimeoutException $e) {
            // Bubble
            throw new RuntimeException('Could not get lock :(', 11, $e);
        } finally {
            // Always free lock
            optional($lock)->release();
        }
    }

    /**
     * Creates an Enrollment by purging the account of line items, creating
     * new ones, applying a coupon if present and finalising it.
     * @param Enrollment $enrollment
     * @return Invoice
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws HttpException
     */
    private function createInvoice(Enrollment $enrollment): Invoice
    {
        // Customer
        $customer = $this->getCustomer($enrollment->user);

        // Remove already present lines (might 404)
        try {
            logger()->debug('Cleaning up old items');
            $existing = InvoiceItem::all([
                'pending' => true,
                'customer' => $customer->id,
                'limit' => 100
            ])->all();

            foreach ($existing as $existingItem) {
                if (!empty($existingItem->invoice)) {
                    continue;
                }
                $existingItem->delete();
            }
            logger()->debug('Removed old items');
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception, 404);
            logger()->debug('No old invoice');
        }

        // Generate new items
        try {
            logger()->debug('Adding items');

            // Get computed lines
            $computed = $this->getComputedInvoiceLines($enrollment);

            // Create all items
            foreach ($computed->get('items') as list($linePrice, $lineDesc, $lineDiscount)) {
                InvoiceItem::create([
                    'customer' => $customer->id,
                    'currency' => 'eur',
                    'amount' => $linePrice,
                    'description' => $lineDesc,
                    'discountable' => $lineDiscount
                ]);
            }
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }

        // Update discount, if applicable
        try {
            // Remove any existing coupons
            logger()->debug('Dropping discount coupon');
            $customer->deleteDiscount();
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception, 404);
        }

        try {
            // Assign new discount, if present
            $coupon = $computed->get('coupon');
            if ($coupon) {
                logger()->debug('Assinging new discount coupon');
                $customer->coupon = $coupon->id;
                $customer->save();
            }
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }

        try {
            logger()->debug('Creating invoice');
            // Create invoice
            $invoice = Invoice::create([
                'customer' => $customer->id,
                'statement_descriptor' => Str::ascii($enrollment->activity->statement)
            ]);

            // Verifiy price
            if ($invoice->amount_due !== $enrollment->total_price) {
                logger()->error(
                    'Invoice price does not match enrollment price',
                    compact('invoice', 'enrollment', 'computed')
                );
                $invoice->delete();

                throw new RuntimeException('Failed to generate invoice with matching price tag');
            }

            // Finalize invoice immediately
            $invoice->finalizeInvoice();

            // Update enrollment
            $enrollment->payment_invoice = $invoice->id;
            $enrollment->save(['payment_invoice']);

            // Return invoice
            return $invoice;
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }

    /**
     * Returns the invoice lines for this enrollment
     * @param Enrollment $enrollment
     * @return Illuminate\Support\Collection
     */
    public function getComputedInvoiceLines(Enrollment $enrollment): Collection
    {
        // Items
        $result = collect([
            'items' => collect(),
            'coupon' => null
        ]);

        // Prep some numbers
        $userPrice = $enrollment->price;
        $transferPrice = $enrollment->total_price - $userPrice;

        // Activity pricing
        $fullPrice = $enrollment->activity->price;
        $discountPrice = $enrollment->activity->discount_price;

        // Always add full price
        $result->get('items')[] = [$fullPrice, "Deelnamekosten {$enrollment->name}", true];

        // Add transfer fees if not free
        if (!empty($userPrice)) {
            $result->get('items')[] = [$transferPrice, 'Transactiekosten', false];
        }

        // No discount was applied, we're done
        if ($userPrice === $fullPrice) {
            return $result;
        }

        // Apply coupon and complete
        if ($userPrice === $discountPrice) {
            $result->put('coupon', $this->getCoupon($enrollment->activity));
            return $result;
        }

        // Apply special discount
        $discount = $userPrice - $fullPrice;
        $result->get('items')[] = [$discount, $discount > 0 ? 'Toeslag' :  'Bijzondere korting', true];

        // Done
        return $result;
    }

    /**
     * Pays the invoice for the enrollment using the given source
     * @param Enrollment $enrollment
     * @param App\Contracts\Source $source
     * @return Stripe\Invoice
     */
    public function payInvoice(Enrollment $enrollment, Source $source): Invoice
    {
        if ($source->status !== Source::STATUS_CHARGEABLE) {
            throw new RuntimeException('Source was already consumed');
        }

        try {
            // Get invoice
            $invoice = $this->getInvoice($enrollment);

            // Pay invoice
            return $invoice->pay([
                'source' => $source->id
                ]);
        } catch (ApiErrorException $exception) {
                        // Bubble all
                        $this->handleError($exception);
        }
    }
}
