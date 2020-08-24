<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PaymentMethods\PaymentMethod;
use App\Models\User;

interface PaymentProvider
{
    /**
     * Fired when a user is created or updated, which allows the provider
     * to, for example, create this user on the invoice provider.
     * @param User $user
     * @return void
     */
    public function handleUserUpdate(User $user): void;

    /**
     * Creates a new invoice for the given enrollment, configuring it for use with the given
     * payment method if it's set
     * @param Enrollment $enrollment The enrollment to create an invoice for
     * @param null|PaymentMethod $method The payment method to use
     * @return Invoice
     */
    public function createInvoice(Enrollment $enrollment, ?PaymentMethod $method): Invoice;

    /**
     * Returns the existing invoice for the enrollment
     * @param Enrollment $enrollment The enrollment to find the Invoice for
     * @return null|Invoice The existing invoice
     */
    public function getInvoice(Enrollment $enrollment): ?Invoice;

    /**
     * Updates the invoice to match the $invoice data and the given PaymentMethod, if it's set
     * @param Invoice $invoice
     * @return bool True if updated succesfully
     */
    public function updateInvoice(Invoice $invoice, ?PaymentMethod $paymentMethod): bool;

    /**
     * Updates the invoice using data from a backend to make sure it's in-sync with the
     * data from the payment provider
     * @param Invoice $invoice
     * @return bool True if updated succesfully
     */
    public function downloadInvoiceUpdates(Invoice $invoice): bool;

    /**
     * Asks the provider to cancel all pending invoices, usually
     * called when a user has unenrolled before paying.
     * Must not issue refunds.
     * @param Invoice $invoice
     * @return bool Indication if it was succesful
     */
    public function cancelInvoice(Invoice $invoice): bool;

    /**
     * Refunds a given invoice
     * @param Invoice $invoice
     * @return bool Indication if it was succesful
     */
    public function refundInvoice(Invoice $invoice): bool;

    /**
     * Returns method that can be used to complete this invoice.
     * @param Invoice $invoice
     * @return array<App\Models\PaymentMethods\PaymentMethod>
     */
    public function getPaymentMethods(Invoice $invoice): array;
}
