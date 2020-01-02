<?php

namespace App\Models;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\States\Enrollment\Cancelled;
use App\Models\Traits\HasEditorJsContent;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

/**
 * A hosted activity
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 *
 * @property-read AttachmentInterface $image
 */
class Activity extends SluggableModel implements AttachableInterface
{
    use PaperclipTrait;
    use HasPaperclip;
    use HasEditorJsContent;
    use HasFlexible;

    public const PAYMENT_TYPE_INTENT = 'intent';
    public const PAYMENT_TYPE_BILLING = 'billing';

    /**
     * @inheritDoc
     */
    protected $dates = [
        // Management dates
        'created_at',
        'updated_at',
        'deleted_at',
        'cancelled_at',

        // Start date
        'start_date',
        'end_date',

        // Enrollment date
        'enrollment_start',
        'enrollment_end'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        // Description
        'description' => 'json',

        // Number of seats
        'seats' => 'int',
        'is_public' => 'bool',

        // Pricing
        'price_member' => 'int',
        'price_guest' => 'int',

        // Extra information
        'enrollment_questions' => 'collection',
    ];

    /**
     * Binds paperclip files
     *
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Max sizes
        $bannerWidth = max(
            640, // Landscape phones
            768 / 12 * 6, // tables
            1024 / 12 * 4, // small laptops
            1280 / 12 * 4, // hd laptops
        );

        // Banner width:height is 2:1
        $bannerHeight = $bannerWidth / 2;

        $coverWidth = 1920; // Full HD width
        $coverHeight = 33 * 16; // 33rem

        // The actual screenshots
        $this->hasAttachedFile('image', [
            'disk' => 'paperclip-public',
            'variants' => [
                // Make banner-sized image (HD and HDPI)
                Variant::make('banner')->steps([
                    ResizeStep::make()->width($bannerWidth)->height($bannerHeight)->crop()
                ])->extension('jpg'),
                Variant::make('banner@2x')->steps([
                    ResizeStep::make()->width($bannerWidth * 2)->height($bannerHeight * 2)->crop()
                ])->extension('jpg'),

                // Make activity cover image (HD and HDPI)
                Variant::make('cover')->steps([
                    ResizeStep::make()->width($coverWidth)->height($coverHeight)->crop()
                ])->extension('jpg'),
                Variant::make('cover@2x')->steps([
                    ResizeStep::make()->width($coverWidth * 2)->height($coverHeight * 2)->crop()
                ])->extension('jpg'),

                // Make Social Media
                Variant::make('social')->steps([
                    ResizeStep::make()->width(1200)->height(650)->crop()
                ])->extension('jpg'),
            ]
        ]);
    }

    /**
     * Generate the slug based on the title property
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
            ]
        ];
    }

    /**
     * Returns the associated role, if any
     *
     * @return BelongsTo
     */
    public function role(): Relation
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Returns all enrollments (both pending and active)
     *
     * @return HasMany
     */
    public function enrollments(): Relation
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Returns all made payments for this event
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Returns if the activity has been cancelled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCancelledAttribute(): bool
    {
        return $this->cancelled_at !== null;
    }

    /**
     * Returns the name of the organiser, either committee or user
     *
     * @return string|null
     */
    public function getOrganiserAttribute(): ?string
    {
        return optional($this->role)->title;
    }

    /**
     * Returns the number of remaining seats
     *
     * @return int
     */
    public function getAvailableSeatsAttribute(): int
    {
        // Only if there are actually places
        if ($this->seats === null) {
            return PHP_INT_MAX;
        }

        // Get enrollment count
        $occupied = $this->enrollments()
            ->whereNotState('state', Cancelled::class)
            ->count();

        // Subtract active enrollments from active seats
        return (int) max(0, $this->seats - $occupied);
    }

    /**
     * Returns the number of remaining guest seats
     *
     * @return int
     */
    public function getAvailableGuestSeatsAttribute(): int
    {
        // No seats are available on private seats
        if (!$this->is_public) {
            return 0;
        }

        // Only if there are actually places
        if ($this->seats === null && $this->guest_seats === null) {
            return PHP_INT_MAX;
        }

        // Return total number of seats if guests don't have reserved seating
        if ($this->guest_seats === null) {
            return $this->available_seats;
        }

        // Count guest enrollments
        $guestOccupied = $this->enrollments()
            ->whereNotState('state', Cancelled::class)
            ->where('user_type', 'guest')
            ->count();
        $freeGuestSeats = (int) max(0, $this->guest_seats - $guestOccupied);

        // Prevent overselling by restricting the available count to the lowest option
        return (int) min($freeGuestSeats, $this->available_seats);
    }

    /**
     * Returns if the enrollment is still open
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnrollmentOpenAttribute(): ?bool
    {
        // Don't re-create a timestamp every time
        $now = now();

        // Cannot sell tickets after activity end
        if ($this->end_date < $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets after activity end',
                ['activity' => $this]
            );
            return false;
        }

        // Cannot sell tickets after enrollment closure
        if ($this->enrollment_end !== null && $this->enrollment_end < $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets after enrollment closure',
                ['activity' => $this]
            );
            return false;
        }

        // Cannot sell tickets before enrollment start
        if ($this->enrollment_start !== null && $this->enrollment_start > $now) {
            logger()->info(
                'Enrollments on {activity} closed:  Cannot sell tickets before enrollment start',
                ['activity' => $this]
            );
            return false;
        }

        // Enrollment start < now < (Enrollment end | Event end)
        return true;
    }

    /**
     * Converts contents to HTML
     *
     * @return string|null
     */
    public function getDescriptionHtmlAttribute(): ?string
    {
        return $this->convertToHtml($this->description);
    }

    /**
     * Enrollment form
     *
     * @return Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function getFlexibleContentAttribute()
    {
        return $this->flexible('enrollment_questions');
    }

    /**
     * Returns member price with transfer costs
     *
     * @return int|null
     */
    public function getTotalPriceMemberAttribute(): ?int
    {
        return $this->price_member ? $this->price_member + config('gumbo.transfer-fee', 0) : $this->price_member;
    }

    /**
     * Returns guest price with transfer cost
     *
     * @return int|null
     */
    public function getTotalPriceGuestAttribute(): ?int
    {
        return $this->price_guest ? $this->price_guest + config('gumbo.transfer-fee', 0) : $this->price_guest;
    }

    /**
     * Returns human-readable summary of the ticket price.
     *
     * @return string
     */
    public function getPriceLabelAttribute(): string
    {
        if ($this->is_free) {
            // If it's free, mention it
            return 'gratis';
        } elseif (!$this->total_price_member && $this->is_public) {
            // Free for members when public
            return 'gratis voor leden';
        } elseif ($this->total_price_member === $this->total_price_guest) {
            // Same price for both parties
            return Str::price($this->total_price_member);
        } elseif ($this->is_public) {
            // Starting bid
            return sprintf('vanaf %s', Str::price($this->total_price_member ?? 0));
        }

        // Return total price as single price point
        return Str::price($this->total_price_member ?? 0);
    }

    /**
     * Returns true if the activity is free
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsFreeAttribute(): bool
    {
        return (
            ($this->total_price_member === null && $this->total_price_guest === null) ||
            ($this->total_price_member === null && !$this->is_public)
        );
    }

    /**
     * Only return activities available to this user
     *
     * @param Builder $query
     * @param User $user
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable(Builder $query, User $user = null): Builder
    {
        /** @var User $user */
        $user = $user ?? request()->user();

        // Add public-only when not a member
        return $user && $user->is_member ? $query : $query->whereIsPublic(true);
    }

    /**
     * Returns url to map provider for the given address
     *
     * @return null|string
     */
    public function getLocationUrlAttribute(): ?string
    {
        // Skip if empty
        if (empty($this->location_address)) {
            return null;
        }

        // Build HERE maps link
        return sprintf(
            'https://wego.here.com/search/%s',
            htmlentities(urlencode($this->location_address))
        );
    }
}
