<?php

declare(strict_types=1);

namespace App\Models\GoogleWallet;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use LogicException;

trait HasGoogleWalletProperties
{
    /**
     * Ensure a wallet ID is generated on creation.
     */
    public static function bootHasGoogleWalletProperties(): void
    {
        static::creating(fn (self $instance) => $instance->assignWalletId());
    }

    /**
     * Ensure a review_status is always cast to enum, and wallet_id is always
     * hidden.
     */
    public function initializeHasGoogleWalletProperties(): void
    {
        $this->hidden = array_merge($this->hidden, [
            'wallet_id',
        ]);
    }

    /**
     * The subject this wallet class belongs to.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The wallet objects in this class.
     */
    public function objects(): HasMany
    {
        return $this->hasMany(EventObject::class, 'class_id');
    }

    /**
     * Scope this query to a single subject.
     */
    public function scopeForSubject(Builder $query, Model $subject): void
    {
        $query->whereHasMorph(
            'subject',
            [get_class($subject)],
            fn (Builder $query) => $query->where($subject->getKeyName(), $subject->getKey()),
        );
    }

    /**
     * Create a proper, semi-random wallet ID.
     * @throws LogicException
     */
    public function assignWalletId(): void
    {
        throw_if($this->exists, LogicException::class, 'Cannot assign wallet ID to existing model');
        throw_if($this->subject === null, LogicException::class, 'Subject is required');

        // Create a proper ID, with some randomness.
        // If creating for an Enrollment(id: 44), the ID will look like:
        // (class ID).EN00446jbvs6bd
        $this->wallet_id = sprintf(
            '%s.%s.%s%04d%s',
            Config::get('services.google.wallet.issuer_id'),
            hexdec((string) Str::of(md5(URL::to('/')))->substr(0, 8)),
            Str::of(class_basename($this->subject))->studly()->upper()->substr(0, 2),
            $this->subject->getKey(),
            Str::of(Str::random(12))->upper()->ascii(),
        );
    }
}
