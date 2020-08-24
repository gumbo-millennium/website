<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\PaymentProvider;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PaymentMethods\PaymentMethod;
use App\Models\States\Enrollment\Cancelled;
use App\Models\User;
use App\Services\Payments\Parts\Mollie\HandlesApiClient;
use App\Services\Payments\Parts\Mollie\HandlesCustomerUpdates;
use App\Services\Payments\Parts\Mollie\HandlesInvoiceData;
use App\Services\Payments\Parts\Mollie\HandlesInvoices;
use App\Services\Payments\Parts\Mollie\HandlesMethods;
use InvalidArgumentException;

class MolliePaymentService implements PaymentProvider
{
    use HandlesApiClient;
    use HandlesCustomerUpdates;
    use HandlesInvoiceData;
    use HandlesInvoices;
    use HandlesMethods;

    /**
     * Cache prefix to use everywhere
     */
    protected const CACHE_PREFIX = 'payments.providers.mollie.';
    protected const PLATFORM_NAME = 'mollie';

    /**
     * @inheritdoc
     */
    public function handleUserUpdate(User $user): void
    {
        $this->updateUser($user);
    }

    /**
     * @inheritdoc
     */
    public function createInvoice(Enrollment $enrollment, ?PaymentMethod $paymentMethod): Invoice
    {
        // First check if possible
        if ($enrollment->can_be_paid) {
            throw new InvalidArgumentException('This enrollment cannot be paid (anymore)');
        }

        // Create the invoice
        $apiInvoice = $this->createMollieInvoice($enrollment, $paymentMethod);

        // Create new Invoice model
        return Invoice::createSupplied(self::PLATFORM_NAME, $apiInvoice->id, $enrollment);
    }

    /**
     * @inheritdoc
     */
    public function getInvoice(Enrollment $enrollment): ?Invoice
    {
        // First check if possible
        if ($enrollment->can_be_paid) {
            throw new InvalidArgumentException('This enrollment cannot be paid (anymore)');
        }

        // Then, find invoice
        return Invoice::findEnrollmentInvoiceByProvider(self::PLATFORM_NAME, $enrollment);
    }

    /**
     * @inheritdoc
     */
    public function updateInvoice(Invoice $invoice, ?PaymentMethod $paymentMethod): bool
    {
        // First check if possible
        if ($enrollment->can_be_paid) {
            throw new InvalidArgumentException('This enrollment cannot be paid (anymore)');
        }

        // We can only update if we have a payment method, other fields are locked
        if (!$paymentMethod) {
            return false;
        }

        // Update the invoice
        $this->updateMollieInvoice($invoice, $paymentMethod);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function downloadInvoiceUpdates(Invoice $invoice): bool
    {
        // Get enrollment
        $enrollment = $invoice->enrollment;

        // Get invoice data
        $apiInvoice = $this->getMollieInvoice($invoice);

        // Fail if missing
        if (!$apiInvoice) {
            return false;
        }

        // Check if the order is refunded
        if ($this->readPrice($apiInvoice->amountRefunded) >= $this->readPrice($apiInvoice->amount)) {
            $invoice->refunded = true;
        }

        // Check if the order was, at some point, paid
        if ($apiInvoice->isPaid()) {
            $invoice->paid = true;
        }

        // Save changes if required
        if ($invoice->isDirty(['paid', 'refunded'])) {
            $invoice->save();
        }

        // All good
        return true;
    }

    /**
     * @inheritdoc
     */
    public function cancelInvoice(Invoice $invoice): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function refundInvoice(Invoice $invoice): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethods(Invoice $invoice): array
    {
        return $this->getBankListForActivity($invoice->activity);
    }
}
