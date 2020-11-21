<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Invoicable
{
    /**
     * Returns the assigned invoice
     * @return MorphOne<Invoice>
     */
    public function invoice(): MorphOne;

    /**
     * Returns an array of InvoiceLine models, which will build the invoice.
     * Must NOT include a transaction fee, that's computed later.
     * @return array<\App\Models\InvoiceLine>
     */
    public function getInvoiceLines(): array;
}
