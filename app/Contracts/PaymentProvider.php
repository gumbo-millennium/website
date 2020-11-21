<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Invoice;

interface PaymentProvider
{
    /**
     * Name of the provider
     * @return string
     */
    public function getName(): string;

    /**
     * Returns a configured list with methods to pay the invoice and
     * their associated costs.
     * @return array
     */
    public function getInvoiceMethods(): array;

    /**
     * Prepares a payment for this invoice, usually results in API calls and
     * model updates.
     * @param Invoice $invoice
     * @return bool
     */
    public function createPayment(Invoice $invoice): bool;

    /**
     * Triggers an update on the payment method
     * @param Invoice $invoice
     * @return bool
     */
    public function updatePayment(Invoice $invoice): bool;

    /**
     * Returns the URL the user should be forwarded to, if any
     * @param Invoice $invoice
     * @return null|string
     */
    public function getNextUrl(Invoice $invoice): ?string;
}
