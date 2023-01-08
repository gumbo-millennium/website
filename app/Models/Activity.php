<?php

declare(strict_types=1);

namespace App\Models;

use Advoor\NovaEditorJs\NovaEditorJsCast;
use App\Casts\ActivityFormCast;
use App\Contracts\FormLayoutContract;
use App\Helpers\Str;
use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

/**
 * App\Models\Activity.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|\Illuminate\Support\Carbon $published_at
 * @property null|\Illuminate\Support\Carbon $cancelled_at
 * @property string $name
 * @property string $slug
 * @property null|string $tagline
 * @property null|\Advoor\NovaEditorJs\NovaEditorJsData $description
 * @property null|\Advoor\NovaEditorJs\NovaEditorJsData $ticket_text
 * @property null|string $poster
 * @property null|string $location
 * @property null|string $location_address
 * @property string $location_type
 * @property \Illuminate\Support\Carbon $start_date Start date and time
 * @property \Illuminate\Support\Carbon $end_date End date and time
 * @property null|int $seats
 * @property bool $is_public
 * @property null|\Illuminate\Support\Carbon $enrollment_start
 * @property null|\Illuminate\Support\Carbon $enrollment_end
 * @property null|string $cancelled_reason
 * @property null|\Illuminate\Support\Carbon $rescheduled_from
 * @property null|string $rescheduled_reason
 * @property null|\Illuminate\Support\Carbon $postponed_at
 * @property null|string $postponed_reason
 * @property null|array<\Whitecube\NovaFlexibleContent\Layouts\Layout>|\Whitecube\NovaFlexibleContent\Layouts\Collection $enrollment_questions
 * @property null|int $role_id
 * @property array $features
 * @property-read \App\Models\Enrollment[]|\Illuminate\Database\Eloquent\Collection $enrollments
 * @property-read int $available_seats
 * @property-read null|\Illuminate\Support\HtmlString $description_html
 * @property-read null|int $discount_price
 * @property-read null|int $discounts_available
 * @property-read null|int $effective_seat_limit
 * @property-read bool $enrollment_open
 * @property-read Collection $expanded_features
 * @property-read \Whitecube\NovaFlexibleContent\Layouts\Collection $flexible_content
 * @property-read null|\App\Contracts\FormLayoutContract[] $form
 * @property-read null|bool $form_is_medical
 * @property-read string $full_statement
 * @property-read string $human_readable_dates
 * @property-read bool $is_cancelled
 * @property-read bool $is_free
 * @property-read bool $is_free_for_member
 * @property-read bool $is_postponed
 * @property-read bool $is_published
 * @property-read bool $is_rescheduled
 * @property-read null|string $location_url
 * @property-read null|string $organiser
 * @property-read string $price_range
 * @property-read null|\Illuminate\Support\HtmlString $ticket_html
 * @property-read null|int $total_discount_price
 * @property-read null|int $total_price
 * @property-read \App\Models\ActivityMessage[]|\Illuminate\Database\Eloquent\Collection $messages
 * @property-read \App\Models\Payment[]|\Illuminate\Database\Eloquent\Collection $payments
 * @property-read null|Role $role
 * @property-read \App\Models\Ticket[]|\Illuminate\Database\Eloquent\Collection $tickets
 * @method static \Database\Factories\ActivityFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|Activity newModelQuery()
 * @method static Builder|Activity newQuery()
 * @method static Builder|Activity query()
 * @method static Builder|Activity whereAvailable(?\App\Models\User $user = null)
 * @method static Builder|Activity whereHasFeature(string $feature)
 * @method static Builder|Activity whereInTheFuture(?\DateTimeInterface $date = null)
 * @method static Builder|Activity wherePublished()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel whereSlug(string $slug)
 * @method static Builder|Activity withEnrollmentsFor(?\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static Builder|Activity withoutUncertainty()
 * @mixin \Eloquent
 */
class Activity extends SluggableModel
{
    use HasFactory;

    public const PAYMENT_TYPE_INTENT = 'intent';

    public const PAYMENT_TYPE_BILLING = 'billing';

    public const LOCATION_OFFLINE = 'offline';

    public const LOCATION_ONLINE = 'online';

    public const LOCATION_MIXED = 'mixed';

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'features' => '[]',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Description
        'description' => NovaEditorJsCast::class,
        'ticket_text' => NovaEditorJsCast::class,
        'enrollment_questions' => ActivityFormCast::class,

        // Number of seats
        'seats' => 'int',
        'is_public' => 'bool',

        // Pricing
        'member_discount' => 'int',
        'discount_count' => 'int',
        'price' => 'int',

        // Features
        'features' => 'json',

        // Management dates
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'published_at' => 'datetime',
        'cancelled_at' => 'datetime',

        // Start date
        'start_date' => 'datetime',
        'end_date' => 'datetime',

        // Enrollment date
        'enrollment_start' => 'datetime',
        'enrollment_end' => 'datetime',

        // Reschedule date
        'rescheduled_from' => 'datetime',

        // Postpone date
        'postponed_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'poster',
        'location',
        'location_address',
        'start_date',
        'end_date',
        'seats',
        'is_public',
        'published_at',
        'enrollment_start',
        'enrollment_end',
        'features',
    ];

    /**
     * Lists the next up activities.
     *
     * @throws InvalidArgumentException
     */
    public static function getNextActivities(?User $user): Builder
    {
        return self::query()
            ->whereInTheFuture()
            ->orderBy('start_date')
            ->whereAvailable($user);
    }

    /**
     * Generate the slug based on the title property.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
            ],
        ];
    }

    /**
     * Returns the associated role, if any.
     *
     * @return BelongsTo
     */
    public function role(): Relation
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Returns all enrollments (both pending and active).
     *
     * @return HasMany
     */
    public function enrollments(): Relation
    {
        return $this->hasMany(Enrollment::class);
    }

    public function seatedEnrollments(): Relation
    {
        return $this->enrollments()
            ->whereNotState('state', [CancelledState::class, RefundedState::class]);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Returns all made payments for this event.
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Returns the name of the organiser, either committee or user.
     */
    public function getOrganiserAttribute(): ?string
    {
        return optional($this->role)->title ?? optional($this->role)->name;
    }

    /**
     * Returns the number of remaining seats.
     */
    public function getAvailableSeatsAttribute(): int
    {
        // Only if there are actually places
        if ($this->seats === null) {
            return PHP_INT_MAX;
        }

        // Get enrollment count
        $occupied = $this->seatedEnrollments()->count();

        // Subtract active enrollments from active seats
        return (int) max(0, $this->seats - $occupied);
    }

    /**
     * Returns if the enrollment is still open.
     *
     * @return bool
     */
    public function getEnrollmentOpenAttribute(): ?bool
    {
        // Prevent if cancelled
        if ($this->is_cancelled) {
            return false;
        }

        // Don't re-create a timestamp every time
        $now = Date::now();

        // Cannot sell tickets after activity end
        if ($this->end_date < $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets after activity end',
                ['activity' => $this],
            );

            return false;
        }

        // Cannot sell tickets after enrollment closure
        if ($this->enrollment_end !== null && $this->enrollment_end < $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets after enrollment closure',
                ['activity' => $this],
            );

            return false;
        }

        // Cannot sell tickets before enrollment start
        if ($this->enrollment_start !== null && $this->enrollment_start > $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets before enrollment start',
                ['activity' => $this],
            );

            return false;
        }

        // Enrollment start < now < (Enrollment end | Event end)
        return true;
    }

    /**
     * Converts contents to HTML.
     */
    public function getDescriptionHtmlAttribute(): ?HtmlString
    {
        return $this->description?->toHtml();
    }

    /**
     * Converts ticket contents to HTML.
     */
    public function getTicketHtmlAttribute(): ?HtmlString
    {
        return $this->ticket_text?->toHtml();
    }

    /**
     * Enrollment form.
     *
     * @return Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function getFlexibleContentAttribute()
    {
        return $this->enrollment_questions;
    }

    /**
     * Returns the price for people with discounts.
     */
    public function getDiscountPriceAttribute(): ?int
    {
        // Return null if no discounts are available
        if (! $this->member_discount) {
            return null;
        }

        // Member price
        return max(0, $this->price - $this->member_discount);
    }

    /**
     * Returns member price with transfer costs.
     */
    public function getTotalDiscountPriceAttribute(): ?int
    {
        // Return null if no discounts are available
        if (! $this->member_discount) {
            return null;
        }

        // In case it's free
        if ($this->price - $this->member_discount <= 0) {
            return 0;
        }

        // Otherwise, use with transfer cost
        return max(0, $this->total_price - $this->member_discount);
    }

    /**
     * Returns the number of discounts available, if any.
     */
    public function getDiscountsAvailableAttribute(): ?int
    {
        // None if no discount is available
        if (! $this->member_discount || ! $this->discount_count) {
            return null;
        }

        // Count them
        $usedEnrollments = $this->enrollments()
            ->whereNotState('state', [CancelledState::class, RefundedState::class])
            ->where('user_type', 'member')
            ->count();

        // Return enrollments with discount that have been paid
        return (int) max(0, $this->discount_count - $usedEnrollments);
    }

    /**
     * Returns guest price with transfer cost.
     */
    public function getTotalPriceAttribute(): ?int
    {
        return $this->price ? $this->price + config('gumbo.transfer-fee', 0) : $this->price;
    }

    /**
     * Returns if members can go for free.
     */
    public function getIsFreeForMemberAttribute(): bool
    {
        return $this->is_free
            || ($this->member_discount === $this->price && $this->discount_count === null);
    }

    /**
     * Returns true if the activity is free.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->total_price === null;
    }

    /**
     * Returns if the activity has been cancelled.
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->cancelled_at !== null;
    }

    /**
     * Returns if the activity was rescheduled to a different date.
     */
    public function getIsRescheduledAttribute(): bool
    {
        return $this->rescheduled_from !== null;
    }

    /**
     * Returns if the activity was postponed to an as-of-yet unknown date.
     */
    public function getIsPostponedAttribute(): bool
    {
        return $this->postponed_at !== null;
    }

    /**
     * Returns if the activity is published.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at === null || $this->published_at < \now();
    }

    /**
     * Returns collection of applied icons, with label and icon.
     */
    public function getExpandedFeaturesAttribute(): Collection
    {
        $featureIcons = collect();

        foreach (collect($this->features)->filter()->keys() as $feature) {
            $featureIcon = Config::get("gumbo.activity-features.{$feature}.icon");
            if (! file_exists(storage_path("app/font-awesome/{$featureIcon}.svg"))) {
                continue;
            }

            $featureIcons->push((object) [
                'icon' => $featureIcon,
                'title' => Config::get("gumbo.activity-features.{$feature}.title"),
            ]);
        }

        return $featureIcons;
    }

    /**
     * Return a nice-to-display date indication of the activity.
     */
    public function getHumanReadableDatesAttribute(): string
    {
        $activityDistance = $this->start_date->diffInHours($this->end_date);
        if ($activityDistance <= 8) {
            return sprintf(
                '%s - %s',
                $this->start_date->isoFormat('D MMMM, HH:mm'),
                $this->end_date->isoFormat('HH:mm'),
            );
        }

        if ($activityDistance > 48) {
            return sprintf(
                '%s - %s, vanaf %s',
                $this->start_date->isoFormat('D MMMM'),
                $this->end_date->isoFormat('D MMMM'),
                $this->start_date->isoFormat('HH:mm'),
            );
        }

        return sprintf(
            '%s - %s',
            $this->start_date->isoFormat('D MMMM, HH:mm'),
            $this->end_date->isoFormat('D MMMM, HH:mm'),
        );
    }

    public function getPriceRangeAttribute(): string
    {
        $ticketCount = $this->tickets->count();

        $ticketPrices = $this->tickets->pluck('total_price')->sort()->values();

        $hasFreeTickets = $ticketPrices->contains(null);

        $minPrice = $ticketPrices->first();
        $maxPrice = $ticketPrices->last();
        $minNonZeroPrice = $ticketPrices->min();

        if ($ticketCount === 0) {
            return __('Price unknown');
        }

        if ($ticketCount === 1 || $maxPrice === $minPrice) {
            return $hasFreeTickets ? __('Free') : Str::price($maxPrice);
        }

        if ($hasFreeTickets && $minNonZeroPrice) {
            return __('Free, or paid starting at :price', ['price' => Str::price($minNonZeroPrice)]);
        }

        return __('From :price', ['price' => Str::price($minPrice)]);
    }

    /**
     * Only return activities available to this user.
     */
    public function scopeWhereAvailable(Builder $query, ?User $user = null): void
    {
        // Load user from auth if unset, and check if the possibly-null user is a member
        $isMember = ($user ?? Auth::user())?->is_member === true;

        // Only show public if not a member
        if (! $isMember) {
            $query->where('is_public', true);
        }

        // Only return published
        $query->wherePublished();
    }

    /**
     * Only return published activities.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePublished(Builder $query): Builder
    {
        return $query->where(static fn (Builder $query) => $query
            ->whereNull('published_at')
            ->orWhere('published_at', '<', \now()), );
    }

    /**
     * Returns url to map provider for the given address.
     */
    public function getLocationUrlAttribute(): ?string
    {
        // Skip if empty
        if (empty($this->location_address)) {
            return null;
        }

        // Build HERE maps link
        return sprintf(
            'https://www.qwant.com/maps/?%s',
            \http_build_query(['q' => $this->location_address]),
        );
    }

    /**
     * Returns a complete statement, up to 22 characters long.
     */
    public function getFullStatementAttribute(): string
    {
        if (! empty($this->statement)) {
            return Str::limit(Str::ascii("Gumbo {$this->statement}", 'nl'), 22, '');
        }

        return 'Gumbo Millennium';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ActivityMessage::class);
    }

    /**
     * Returns the form fields interpreted as a form field.
     *
     * @return null|FormLayoutContract[]
     */
    public function getFormAttribute(): ?array
    {
        $fields = [];

        foreach ($this->enrollment_questions as $field) {
            if (! $field instanceof FormLayoutContract) {
                continue;
            }

            $fields[] = $field->toFormField();
        }

        return $fields;
    }

    /**
     * If the form contains medical data, we can't export the data.
     * This method should check that somehow.
     */
    public function getFormIsMedicalAttribute(): ?bool
    {
        if ($this->form === null) {
            return false;
        }

        $medicalNames = Config::get('gumbo.activity.medical-titles');

        foreach ($this->form as $field) {
            $fieldLabel = Arr::get($field->getOptions(), 'label');

            if (! $fieldLabel || $field->getType() === 'static') {
                continue;
            }

            $fieldLabel = Str::lower($fieldLabel);
            if (Str::contains($fieldLabel, $medicalNames)) {
                return true;
            }
        }

        return false;
    }

    public function scopeWhereHasFeature(Builder $query, string $feature): Builder
    {
        return $query->where("features.{$feature}", '=', true);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) Arr::get($this->features ?? [], $feature, false);
    }

    /**
     * Scope the query to only target future activities after the given date.
     */
    public function scopeWhereInTheFuture(Builder $query, ?DateTimeInterface $date = null): void
    {
        // Skipp all cancelled
        $query->whereNull('cancelled_at');

        // Add rest of the scope in a subquery
        $query->where(function (Builder $query) use ($date) {
            // Check if the end date is after the given date
            $query->where('end_date', '>', $date ?? Date::now());

            // ... or where the event is postponed, but only postponed
            $query->orWhere(fn (Builder $query) => (
                $query
                    ->whereNotNull('postponed_at')
                    ->whereNull('rescheduled_from')
                    ->whereNull('cancelled_at')
            ));
        });
    }

    public function scopeWithoutUncertainty(Builder $query): void
    {
        $query->whereNull([
            'postponed_at',
            'cancelled_at',
        ]);
    }

    public function scopeWithEnrollmentsFor(Builder $query, ?User $user): void
    {
        $query->when(
            $user,
            fn ($query) => $query->with('enrollments', fn ($query) => $query->where('user_id', $user->id)),
        );
    }

    /**
     * Returns the effective seat limit for this activity, which is
     * always capped by the number of seats on the activity, but
     * if less tickets are available, the number is lower.
     * @return null|int Max number of seats, or null if not limited
     */
    public function getEffectiveSeatLimitAttribute(): ?int
    {
        $ticketLimits = $this->tickets->pluck('quantity');
        $hasUnlimitedTicket = $ticketLimits->contains(null);

        // There is a ticket that allows infinite seats, so the activity is unlimited, unless a number of seats is set.
        if ($hasUnlimitedTicket) {
            return $this->seats;
        }

        // Activity has no limit, so use the combined ticket limit
        if ($this->seats === null) {
            return $ticketLimits->sum();
        }

        // Activity has a limit, so use the lowest of the two
        return min($ticketLimits->sum(), $this->seats);
    }
}
