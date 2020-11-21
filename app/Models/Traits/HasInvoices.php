<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Makes an object invoicable
 * @property-read bool is_paid
 * @property-read Invoice $invoice
 */
trait HasInvoices
{
    /**
     * The invoice being paid
     * @return MorphOne
     */
    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoicable');
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->invoice && $this->invoice->paid;
    }
}
