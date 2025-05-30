<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Payments\Payable;
use App\Enums\EnrollmentCancellationReason;
use App\Enums\Models\BarcodeType;
use App\Facades\Enroll;
use App\Fluent\Payment as PaymentFluent;
use App\Models\States\Enrollment as States;
use App\Models\States\Enrollment\State as EnrollmentState;
use App\Models\Traits\HasPayments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * App\Models\Enrollment.
 *
 * @property int $id
 * @property null|string $previous_id
 * @property int $activity_id
 * @property null|int $ticket_id
 * @property int $user_id
 * @property EnrollmentState $state
 * @property null|int $price
 * @property null|int $total_price
 * @property string $user_type
 * @property null|\Illuminate\Support\Carbon $expire
 * @property null|string $transfer_secret
 * @property null|string $barcode
 * @property BarcodeType $barcode_type
 * @property bool $barcode_generated
 * @property null|\Illuminate\Support\Carbon $consumed_at
 * @property null|int $consumed_by_id
 * @property null|\Illuminate\Support\Collection $data
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property string $name
 * @property string $email
 * @property null|EnrollmentCancellationReason $deleted_reason
 * @property-read \App\Models\Activity $activity
 * @property-read null|\App\Models\User $consumedBy
 * @property-read null|array $form
 * @property-read mixed $form_data
 * @property-read bool $has_been_paid
 * @property-read bool $is_discounted
 * @property-read null|bool $is_form_exportable
 * @property-read bool $is_stable
 * @property-read string $payment_status
 * @property-read string $pdf_disk
 * @property-read string $pdf_path
 * @property-read bool $requires_payment
 * @property-read null|\App\Models\States\Enrollment\State $wanted_state
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read null|\App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static Builder|Enrollment active()
 * @method static \Database\Factories\EnrollmentFactory factory($count = null, $state = [])
 * @method static Builder|Enrollment forUser(\App\Models\User|int $user)
 * @method static Builder|Enrollment newModelQuery()
 * @method static Builder|Enrollment newQuery()
 * @method static Builder|Enrollment onlyTrashed()
 * @method static Builder|Enrollment orWhereNotState(string $column, $states)
 * @method static Builder|Enrollment orWhereState(string $column, $states)
 * @method static Builder|Enrollment query()
 * @method static Builder|Enrollment stable()
 * @method static Builder|Enrollment whereCancelled()
 * @method static Builder|Enrollment whereExpired()
 * @method static Builder|Enrollment whereNotState(string $column, $states)
 * @method static Builder|Enrollment wherePaid()
 * @method static Builder|Enrollment whereState(string $column, $states)
 * @method static Builder|Enrollment withTrashed()
 * @method static Builder|Enrollment withoutTrashed()
 * @mixin \Eloquent
 */
class Enrollment extends Model implements Payable
{
    use HasFactory;
    use HasPayments;
    use HasStates;
    use SoftDeletes;

    public const OWNER_NAME_DEFAULT = 'GUEST';

    public const OWNER_EMAIL_DEFAULT = 'guest@example.invalid';

    public const USER_TYPE_MEMBER = 'member';

    public const USER_TYPE_GUEST = 'guest';

    public const PDF_TICKET_DIR = 'tickets/by-user';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_enrollments';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'expire' => 'datetime',
        'consumed_at' => 'datetime',

        'deletion_reason' => EnrollmentCancellationReason::class,
        'state' => EnrollmentState::class,
        'data' => 'encrypted:collection',
        'paid' => 'bool',
        'price' => 'int',
        'total_price' => 'int',

        'barcode_type' => BarcodeType::class,
        'barcode_generated' => 'bool',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'data',
        'deleted_at',
        'deleted_reason',
        'payment_intent',
        'payment_invoice',
        'payment_source',
        'transfer_secret',
        'barcode',
        'barcode_type',
        'barcode_generated',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'activity_id',
        'ticket_id',
        'state',
        'deleted_reason',
        'price',
        'total_price',

        'barcode',
        'barcode_type',
        'barcode_generated',
    ];

    /**
     * Ensure a ticket code is set when saving an enrollment.
     */
    public static function booted(): void
    {
        parent::booted();

        static::creating(function (self $enrollment) {
            $name = $enrollment->user?->name ?? self::OWNER_NAME_DEFAULT;
            $email = $enrollment->user?->email ?? self::OWNER_EMAIL_DEFAULT;

            Log::debug('Writing name {name} and email {email} to {enrollment}', [
                'name' => $name,
                'email' => $email,
                'enrollment' => $enrollment,
            ]);

            $enrollment->forceFill([
                'name' => $name,
                'email' => $email,
            ]);
        });

        static::saving(function (self $enrollment) {
            if ($enrollment->barcode == null) {
                Enroll::updateBarcode($enrollment);
            }
        });
    }

    /**
     * Finds the active enrollment for this activity.
     */
    public static function findActive(User $user, Activity $activity): ?self
    {
        return self::query()
            ->withoutTrashed()
            ->whereUserId($user->id)
            ->whereActivityId($activity->id)
            ->whereNotState('state', [States\Cancelled::class, States\Refunded::class])
            ->with(['activity'])
            ->first();
    }

    /**
     * Finds the active enrollment for this activity, or throws a 404 HTTP exception.
     *
     * @throws NotFoundHttpException if there is no enrollment present
     */
    public static function findActiveOrFail(User $user, Activity $activity): self
    {
        $result = self::findActive($user, $activity);
        if ($result) {
            return $result;
        }

        throw new NotFoundHttpException();
    }

    /**
     * The user this enrollment belongs to.
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to.
     *
     * @return BelongsTo
     */
    public function activity(): Relation
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * The ticket associated with this enrollment.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class)
            ->withTrashed();
    }

    /**
     * The user who consumed this ticket using the scan app.
     */
    public function consumedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns true if the state is stable and will not auto-delete.
     */
    public function getIsStableAttribute(): bool
    {
        return $this->state instanceof States\Confirmed;
    }

    /**
     * Returns if the enrollment is discounted.
     */
    public function getIsDiscountedAttribute(): bool
    {
        return $this->price === $this->activity->discount_price;
    }

    /**
     * Returns if the enrollment has been paid.
     */
    public function getHasBeenPaidAttribute(): bool
    {
        return $this->state instanceof States\Paid;
    }

    /**
     * Returns state we want to go to, depending on Enrollment's own attributes.
     * Returns null if it can't figure it out.
     *
     * @return null|App\Models\States\Enrollment\State
     */
    public function getWantedStateAttribute(): ?EnrollmentState
    {
        // First check for any transition
        $options = $this->state->transitionableStates();
        if (in_array(States\Seeded::$name, $options, true) && $this->activity->form) {
            return new States\Seeded($this);
        }

        if (in_array(States\Paid::$name, $options, true) && $this->price) {
            return new States\Paid($this);
        }

        if (in_array(States\Confirmed::$name, $options, true)) {
            return new States\Confirmed($this);
        }

        return null;
    }

    public function getRequiresPaymentAttribute(): bool
    {
        return $this->exists
            && $this->total_price
            && ! ($this->state instanceof States\Cancelled);
    }

    /**
     * Stores the enrollment data on this user.
     */
    public function setFormData(array $values): void
    {
        // Transform data into something persistable
        $formValues = [];
        $formLabels = [];
        $formExportable = [];

        foreach ($this->activity->form as $field) {
            $rawValue = Arr::get($values, $field->getName());
            $fieldLabel = Arr::get($field->getOptions(), 'label', $field->getName());
            $fieldType = $field->getType();

            if ($fieldType === 'checkbox') {
                $rawValue = (bool) $rawValue;
            }

            $formValues[$field->getName()] = $rawValue;
            $formLabels[$field->getName()] = $fieldLabel;
            $formExportable[$fieldLabel] = $rawValue;
        }

        // Store data
        $data = $this->data;

        // Assign data
        Arr::set($data, 'form.fields', $formValues);
        Arr::set($data, 'form.labels', $formLabels);
        Arr::set($data, 'form.exportable', $formExportable);
        Arr::set($data, 'form.filled', true);
        Arr::set($data, 'form.medical', (bool) $this->activity->form_is_medical);

        // Re-apply
        $this->data = $data;
    }

    /**
     * Returns if this enrollment has been consumed.
     */
    public function consumed(): bool
    {
        return $this->consumed_at !== null;
    }

    /**
     * Returns the filled in form.
     */
    public function getFormAttribute(): ?array
    {
        return Arr::get($this->data, 'form.exportable');
    }

    /**
     * Returns the data for this form, as it's presented to the form builder.
     */
    public function getFormDataAttribute()
    {
        return Arr::get($this->data, 'form.fields');
    }

    /**
     * Returns if the form can be exported.
     */
    public function getIsFormExportableAttribute(): ?bool
    {
        if (! $this->form) {
            return null;
        }

        return Arr::get($this->data, 'form.medical', false) !== true;
    }

    public function toPayment(): PaymentFluent
    {
        if ($this->price === null) {
            throw new LogicException('Cannot create payment for enrollment without price');
        }

        $fees = $this->total_price - $this->price;

        $payment = PaymentFluent::make()
            ->withNumber("{$this->created_at->format('Y.m')}.{$this->id}")
            ->withModel($this)
            ->withUser($this->user)
            ->withDescription("Inschrijving voor {$this->activity->name}")
            ->addLine(__(':ticket for :activity', [
                'ticket' => $this->ticket->title,
                'activity' => $this->activity->name,
            ]), $this->price);

        if (($fees = $this->total_price - $this->price) > 0) {
            $payment->addLine(__('Fees'), $fees);
        }

        throw_unless($payment->verifySum($this->total_price), new LogicException('Price mismatch'));

        return $payment;
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereState('state', [
            States\Created::class,
            States\Seeded::class,
            States\Confirmed::class,
            States\Paid::class,
        ]);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeStable(Builder $query): void
    {
        $query->whereState('state', [
            States\Confirmed::class,
            States\Paid::class,
        ]);
    }

    /**
     * Filter by the given user.
     */
    public function scopeForUser(Builder $query, User|int $user): void
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $query->whereHas('user', fn (Builder $query) => $query->where('id', $user));
    }

    public function getPdfPathAttribute(): string
    {
        if ($this->id == null) {
            throw new LogicException('Cannot generate ticket PDF for enrollment that have not been saved yet');
        }

        return sprintf('%s/%06d/%06d.pdf', self::PDF_TICKET_DIR, $this->user_id ?? 0, $this->id);
    }

    public function getPdfDiskAttribute(): string
    {
        return Config::get('gumbo.tickets.disk');
    }

    /**
     * Checks if the PDF ticket exists.
     */
    public function pdfExists(): bool
    {
        return Storage::disk($this->pdf_disk)->fileExists($this->pdf_path);
    }

    /**
     * Returns true if this enrollment is still active.
     */
    public function active(): bool
    {
        return $this->state instanceof States\Created
            || $this->state instanceof States\Seeded
            || $this->state instanceof States\Confirmed
            || $this->state instanceof States\Paid;
    }

    public function has2dBarcode(): bool
    {
        return in_array($this->barcode_type, [
            BarcodeType::CODABAR,
            BarcodeType::CODE39,
            BarcodeType::CODE128,
            BarcodeType::EAN8,
            BarcodeType::EAN13,
        ], true);
    }

    public function __toString()
    {
        $out = "Ticket for {$this->activity->name}, owned by {$this->user->name}";
        if ($this->state instanceof States\Cancelled) {
            $out .= ' (cancelled)';
        } elseif ($this->has_been_paid) {
            $out .= ", paid on {$this->payments->firstWhere('paid_at', '!=', null)?->paid_at?->format('d-m-Y')}";
        } elseif (! $this->is_stable) {
            $out .= ' (pending)';
        }

        return $out;
    }
}
