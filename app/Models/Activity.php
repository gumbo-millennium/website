<?php

namespace App\Models;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Traits\HasEditorJsContent;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Permission\Models\Role;

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
        'public_seats' => 'int',

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
    public function getAvailableSeatsAttribute(): ?int
    {
        // Only if there are actually places
        if ($this->seats === null) {
            return null;
        }

        return $this->seats - $this->enrollments()->count();
    }

    /**
     * Returns if the enrollment is still open
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEnrollmentOpenAttribute(): ?bool
    {
        $now = now();

        // Cannot sell tickets after activity end
        if ($this->end_date > $now) {
            return false;
        }

        // Cannot sell tickets after enrollment closure
        if ($this->enrollment_end !== null && $this->enrollment_end < $now) {
            return false;
        }

        // Cannot sell tickets before enrollment start
        if ($this->enrollment_start !== null && $this->enrollment_start > $now) {
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
}
