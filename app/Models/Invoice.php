<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $enrollment_id
 * @property string $platform
 * @property string $platform_id
 * @property int $amount
 * @property bool $paid
 * @property bool $refunded
 */
class Invoice extends UuidModel
{
    /**
     * Returns the invoice for the given enrollment on the given platform
     * @param string $platform
     * @param Enrollment $enrollment
     * @return null|Invoice
     */
    public static function findEnrollmentInvoiceByProvider(string $platform, Enrollment $enrollment): ?self
    {
        // Always return null if enrollment is not yet created
        if (!$enrollment->exists()) {
            return null;
        }

        // Run query
        return self::query()
            ->where([
                'platform' => $platform,
                'enrollment_id' => $enrollment->id
            ])
            ->first();
    }

    /**
     * Finds the invoice with the given $id on the platform
     * @param string $platform
     * @param string $id
     * @return null|Invoice
     */
    public static function findInvoiceIdByProvider(string $platform, string $id): ?self
    {
        return self::query()->where([
            'platform' => $platform,
            'platform_id' => $id
        ])->first();
    }

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
