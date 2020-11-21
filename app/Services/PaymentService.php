<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Invoicable;
use App\Contracts\PaymentProvider;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Support\Arr;
use RuntimeException;

final class PaymentService
{
    /**
     * @var array<PaymentProvider>
     */
    private array $providers = [];
    private string $defaultProvider;

    /**
     * Prep instance with a default
     * @param string $defaultProvider
     */
    public function __construct(string $defaultProvider)
    {
        $this->defaultProvider = $defaultProvider;
    }

    /**
     * Registers the provider
     * @param PaymentProvider $provider
     * @return PaymentService
     */
    public function addProvider(PaymentProvider $provider): self
    {
        $this->providers[$provider->getName()] = $provider;
        return $this;
    }

    /**
     * Returns the provider for the invoice
     * @param null|Invoice $invoice
     * @return PaymentProvider
     * @throws RuntimeException if provider is missing
     */
    public function getProvider(?Invoice $invoice = null): PaymentProvider
    {
        // Get instance driver or the default one
        $driver = $invoice ? $invoice->driver : null;
        $driver ??= $this->defaultProvider;

        if (!isset($this->providers[$driver])) {
            throw new RuntimeException("Cannot find payment provider {$driver}");
        }

        return $this->providers[$driver];
    }

    /**
     * Make a new invoice, won't check for conflicts so do that yourself 😉
     * @param Invoicable $invoicable
     * @return Invoice
     */
    public function createInvoice(Invoicable $invoicable): Invoice
    {
        // Get lines and add a transfer-fee line
        $lines = $invoicable->getInvoiceLines();
        $total = \array_reduce($lines, static fn (int $carry, InvoiceLine $line) => $carry += $line->price, 0);

        // Get the provider to use
        $provider = $this->getProvider(null);
        $payment = Arr::first($provider->getInvoiceMethods());
        $lines[] = $payment->toInvoiceLine($total);

        // Create an invoice
        $invoice = new Invoice();
        $invoice->invoicable()->associate($invoicable);

        // Associate data
        $invoice->vendor = $provider->getName();
        $invoice->lines = $lines;
        $invoice->price = \array_reduce($lines, static fn (int $carry, InvoiceLine $line) => $carry += $line->price, 0);

        // Save the initial model
        $invoice->save();

        // Done
        return $invoice;
    }
}
