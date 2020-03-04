<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Contracts\StripeServiceContract;
use App\Helpers\Str;
use App\Models\Enrollment;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Source;

trait HandlesStripeInvoices
{
    /**
     * Invoices retrieved from API
     * @var array<Invoice>
     */
    private array $invoiceCache = [];

    /**
     * Returns the invoice lines for this enrollment
     * @param Enrollment $enrollment
     * @return Illuminate\Support\Collection
     */
    public function getComputedInvoiceLines(Enrollment $enrollment): Collection
    {
        // Items
        $items = collect();

        // Prep some numbers
        $userPrice = $enrollment->price;
        $transferPrice = $enrollment->total_price - $userPrice;

        // Activity pricing
        $fullPrice = $enrollment->activity->price;
        $discountPrice = $enrollment->activity->discount_price;

        // Always add full price
        $items[] = [$fullPrice, "Deelnamekosten {$enrollment->name}", true];

        // Add transfer fees if not free
        if (!empty($userPrice)) {
            $items[] = [$transferPrice, 'Transactiekosten', false];
        }

        // There's a discount, but it's non-default. Add it as a line
        if ($userPrice !== $discountPrice && $userPrice !== $fullPrice) {
            $discount = $userPrice - $fullPrice;
            $items[] = [$discount, $discount > 0 ? 'Toeslag' :  'Korting', true];
        }

        // Return the items
        return $items;
    }

    /**
     * Returns a single invoice for the given Enrollment
     * @param Enrollment $enrollment
     * @param int $options Bitwise options, see OPT_ constants
     * @return Stripe\Invoice
     */
    public function getInvoice(Enrollment $enrollment, int $options = 0): ?Invoice
    {
        // Forward to locked Create Enrollment method
        // Get a 1 minute lock on this user
        $lock = Cache::lock("stripe.invoice.{$enrollment->user_id}", 60);
        try {
            // Block for max 15 seconds
            $lock->block(15);

            // Reload model
            $enrollment->refresh();

            if (!empty($this->invoiceCache[$enrollment->payment_invoice])) {
                return $this->invoiceCache[$enrollment->payment_invoice];
            }

            // Check API first (but inside the lock, so we don't create duplicate invoices)
            if ($enrollment->payment_invoice) {
                try {
                    // Get invoice
                    $invoice = Invoice::retrieve($enrollment->payment_invoice);

                    // Cache invoice
                    $this->invoiceCache[$enrollment->payment_invoice] = $invoice;

                    // Return invoice
                    return $invoice;
                } catch (ApiErrorException $exception) {
                    // Bubble any non-404 errors
                    $this->handleError($exception, 404);
                }
            }

            // Allow no-create
            if ($options & StripeServiceContract::OPT_NO_CREATE) {
                return null;
            }

            // Create invoice
            // phpcs:ignore Generic.Files.LineLength.TooLong
            return $this->invoiceCache[$enrollment->payment_invoice] = $this->createInvoice($enrollment);
        } catch (LockTimeoutException $e) {
            // Bubble
            throw new RuntimeException('Could not get lock :(', 0, $e);
        } finally {
            // Always free lock
            optional($lock)->release();
        }
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
            return $this->invoiceCache[$enrollment->payment_invoice] = $invoice->pay([
                'source' => $source->id
                ]);
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
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
        $this->clearPendingInvoiceItems($customer);

        // Add computed lines
        $computed = $this->getComputedInvoiceLines($enrollment);
        $this->createPendingInvoiceItems($customer, $computed);

        // Add discount, if any
        $coupon = $this->getCoupon($enrollment->activity);
        $this->updateCustomerDiscount($customer, $coupon);

        // Create the actual invoice
        $invoice = $this->createActualInvoice($customer, $enrollment);

        // Update enrollment
        $enrollment->payment_invoice = $invoice->id;
        $enrollment->save(['payment_invoice']);

        // Return invoice
        return $invoice;
    }

    /**
     * Clears pending invoice items off the account
     * @param Customer $customer
     * @return void
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    private function clearPendingInvoiceItems(Customer $customer): void
    {
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
    }

    /**
     * Creates a new set of invoice items, for the new invoice
     * @param Customer $customer
     * @param Collection $items
     * @return void
     */
    private function createPendingInvoiceItems(Customer $customer, Collection $items): void
    {
        // Generate new items
        try {
            logger()->debug('Adding items');
            // Create all items
            foreach ($items as [$linePrice, $lineDesc, $lineDiscount]) {
                // Assertions
                \assert(is_int($linePrice) && $linePrice > 0, 'Price not above 0');
                \assert(is_string($lineDesc) && !empty($lineDesc), 'Description not a non-empty string');
                \assert(is_bool($lineDesc), 'Discount not a bool');

                // Validate line
                if ($linePrice <= 0 || empty($lineDesc)) {
                    logger()->debug('Line is invalid', [
                        'line-price' => $linePrice,
                        'line-desc' => $lineDesc,
                        'line-discount' => $lineDiscount,
                    ]);
                }

                // Create item
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
    }

    /**
     * Assign new discount to the user, removing the old one
     * @param Customer $customer
     * @param null|Coupon $coupon
     * @return void
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    private function updateCustomerDiscount(Customer $customer, ?Coupon $coupon): void
    {
        // Update discount, if applicable
        if ($customer->discount !== null) {
            try {
                // Remove any existing coupons
                logger()->debug('Dropping discount coupon');
                $customer->deleteDiscount();
            } catch (ApiErrorException $exception) {
                // Bubble all
                $this->handleError($exception, 404);
            }
        }

        try {
            // Assign new discount, if present
            if ($coupon) {
                logger()->debug('Assinging new discount coupon');
                $customer->coupon = $coupon->id;
                $customer->save();
            }
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }

    /**
     * Creates the actual invoice model on the stripe API
     * @param Customer $customer
     * @param Enrollment $enrollment
     * @return Invoice
     * @throws RuntimeException
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    private function createActualInvoice(Customer $customer, Enrollment $enrollment): Invoice
    {
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
                    compact('invoice', 'enrollment')
                );
                $invoice->delete();

                throw new RuntimeException('Failed to generate invoice with matching price tag');
            }

            // Finalize invoice immediately
            $invoice->finalizeInvoice();

            // Return invoice
            return $invoice;
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }
}
