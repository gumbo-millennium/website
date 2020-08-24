<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $enrollment_id
 * @property string $provider
 * @property string $provider_id
 * @property int $amount
 * @property bool $paid
 * @property bool $refunded
 */
class Invoice extends UuidModel
{
    /**
     * Returns the invoice for the given enrollment on the given provider
     * @param string $provider
     * @param Enrollment $enrollment
     * @return null|Invoice
     */
    public static function findEnrollmentInvoiceByProvider(string $provider, Enrollment $enrollment): ?self
    {
        // Always return null if enrollment is not yet created
        if (!$enrollment->exists()) {
            return null;
        }

        // Run query
        return self::query()
            ->where([
                'provider' => $provider,
                'enrollment_id' => $enrollment->id
            ])
            ->first();
    }

    /**
     * Finds the invoice with the given $id on the provider
     * @param string $provider
     * @param string $id
     * @return null|Invoice
     */
    public static function findInvoiceIdByProvider(string $provider, string $id): ?self
    {
        return self::query()->where([
            'provider' => $provider,
            'provider_id' => $id
        ])->first();
    }

    /**
     * Creates an invoice for the given ID on the provider
     * @param string $provider
     * @param string $id
     * @param Enrollment $enrollment
     * @param array $props additional properties, like 'amount'
     * @return Invoice
     * @throws InvalidArgumentException
     */
    public static function createSupplied(string $provider, string $id, Enrollment $enrollment, array $props = []): self
    {
        $invoice = new self($props);
        $invoice->provider = $provider;
        $invoice->provider_id = $id;
        $invoice->enrollment_id = $enrollment->id;
        $invoice->amount = $enrollment->total_price;
        $invoice->save();

        return $invoice;
    }

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'amount',
        'paid',
        'refunded'
    ];

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'amount' => 'int',
        'paid' => 'bool',
        'refunded' => 'bool',
        'meta' => 'json'
    ];

    /**
     * The model's attributes.
     * @var array
     */
    protected $attributes = [
        'meta' => '[]',
        'refunded' => false,
        'paid' => false,
    ];

    /**
     * Returns the enrollment this invoice is for
     * @return BelongsTo
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Returns the currency, which is always Euro
     * @return string
     */
    public function getCurrencyAttribute(): string
    {
        return 'EUR';
    }

    /**
     * The user who needs to pay this invoice
     * @return HasOneThrough
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Enrollment::class);
    }

    /**
     * The activity this invoice is for
     * @return HasOneThrough
     */
    public function activity()
    {
        return $this->hasOneThrough(Activity::class, Enrollment::class);
    }

    /**
     * Finds the item with the given metadata
     */
    public function scopeWhereMeta(Builder $query, string $key, $value): Builder
    {
        return $query->where("meta->{$key}", $value);
    }
}
