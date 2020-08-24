<?php

declare(strict_types=1);

namespace Tests\Stubs;

use App\Contracts\PaymentProvider;
use App\Helpers\Str;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PaymentMethods\DirectDebitMethod;
use App\Models\PaymentMethods\IdealMethod;
use App\Models\PaymentMethods\PaymentMethod;
use App\Models\PaymentMethods\TransferMethod;
use App\Models\User;

class DummyPaymentProvider implements PaymentProvider
{
    /**
     * Fired when a user is created or updated, which allows the provider
     * to, for example, create this user on the invoice provider.
     * @param User $user
     * @return void
     */
    public function handleUserUpdate(User $user): void
    {
        $user->setVendorId('test', (string) Str::uuid());
        $user->save();
    }

    /**
     * Creates a new invoice for the given enrollment, configuring it for use with the given
     * payment method if it's set
     * @param Enrollment $enrollment The enrollment to create an invoice for
     * @param null|PaymentMethod $method The payment method to use
     * @return Invoice
     */
    public function createInvoice(Enrollment $enrollment, ?PaymentMethod $method): Invoice
    {
        return Invoice::createForPlatform('test', (string) Str::uuid(), [
            'amount' => 1337,
            'enrollment_id' => $enrollment->id,
            'meta' => [
                'payment-provider' => $method->name
            ]
        ]);
    }

    /**
     * Returns the existing invoice for the enrollment
     * @param Enrollment $enrollment The enrollment to find the Invoice for
     * @return null|Invoice The existing invoice
     */
    public function getInvoice(Enrollment $enrollment): ?Invoice
    {
        return Invoice::findEnrollmentInvoiceByProvider('test', $enrollment);
    }

    /**
     * Updates the invoice to match the $invoice data and the given PaymentMethod, if it's set
     * @param Invoice $invoice
     * @return bool True if updated succesfully
     */
    public function updateInvoice(Invoice $invoice, ?PaymentMethod $paymentMethod): bool
    {
        if ($paymentMethod instanceof IdealMethod) {
            $invoice->paid = true;
            $invoice->save();
        }

        if ($paymentMethod instanceof DirectDebitMethod) {
            $invoice->refunded = true;
            $invoice->save();
        }

        if ($paymentMethod instanceof TransferMethod) {
            return false;
        }

        return true;
    }

    /**
     * Updates the invoice using data from a backend to make sure it's in-sync with the
     * data from the payment provider
     * @param Invoice $invoice
     * @return bool True if updated succesfully
     */
    public function downloadInvoiceUpdates(Invoice $invoice): bool
    {
        $invoice->paid = $invoice->paid ?: \random_int(1, 5) >= 4;
        $invoice->save();
        return $invoice->paid;
    }

    /**
     * Asks the provider to cancel all pending invoices, usually
     * called when a user has unenrolled before paying.
     * Must not issue refunds.
     * @param Invoice $invoice
     * @return bool Indication if it was succesful
     */
    public function cancelInvoice(Invoice $invoice): bool
    {
        if ($invoice->refunded) {
            return false;
        }

        $invoice->refunded = true;
        $invoice->save();
        return true;
    }

    /**
     * Refunds a given invoice
     * @param Invoice $invoice
     * @return bool Indication if it was succesful
     */
    public function refundInvoice(Invoice $invoice): bool
    {
        $invoice->refunded = true;
        return true;
    }

    /**
     * Returns method that can be used to complete this invoice.
     * @param Invoice $invoice
     * @return array<App\Models\PaymentMethods\PaymentMethod>
     */
    public function getPaymentMethods(Invoice $invoice): array
    {
        $out = [
            IdealMethod::make(
                'ideal',
                'iDEAL',
                ['fixed' => 29],
                ['abn', 'rabo', 'ign']
            )
        ];
        if ($invoice->amount > 500) {
            $out[] = TransferMethod::make(
                'wire-transfer',
                'Transfer via can-wire',
                ['flexible' => 0.15]
            );
        }
        return [];
    }
}
