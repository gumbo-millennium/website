<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    private const MODEL_TYPENAME_MAP = [
        Enrollment::class => 'inschrijving'
    ];

    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent saving without an invoiceable
        self::saving(static function (Invoice $invoice) {
            if (!$invoice->invoicable) {
                return false;
            }
        });
    }

    /**
     * The object that's being paid
     * @return MorphTo
     */
    public function invoicable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Find payments for the vendor with the provided ID
     * @param Builder $query
     * @param string $vendor
     * @param string $vendorId
     * @return Builder
     */
    public function scopeWhereProviderId(Builder $query, string $vendor, string $vendorId): Builder
    {
        return $query->where([
            'vendor' => $vendor,
            'vendor_id' => $vendorId,
        ]);
    }

    /**
     * The user that's paying
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A proper human-readable title
     * @return string
     */
    public function getTitleAttribute(): string
    {
        $ref = $this->invoicable;
        return sprintf(
            'Factuur van %s voor %s %s',
            Str::price($this->total),
            self::MODEL_TYPENAME_MAP[get_class($ref)] ?? class_basename($ref),
            $ref->title ?? $ref->name ?? $ref->getKey()
        );
    }
}
