<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment as States;
use App\Models\States\Enrollment\State as EnrollmentState;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A user enrollment for an activity. Optionally has payments.
 *
 * @property string $id
 * @property int $user_id
 * @property int $activity_id
 * @property null|int $ticket_id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property \App\Models\States\Enrollment\State $state
 * @property null|string $deleted_reason
 * @property null|int $price
 * @property null|int $total_price
 * @property null|string $payment_intent
 * @property null|string $payment_invoice
 * @property null|string $payment_source
 * @property string $user_type
 * @property null|\Illuminate\Support\Carbon $expire
 * @property null|string $transfer_secret
 * @property null|array $data
 * @property-read \App\Models\Activity $activity
 * @property-read null|array $form
 * @property-read mixed $form_data
 * @property-read bool $is_discounted
 * @property-read null|bool $is_form_exportable
 * @property-read bool $is_stable
 * @property-read bool $requires_payment
 * @property-read null|\App\Models\States\Enrollment\State $wanted_state
 * @property-read \App\Models\Payment[]|\Illuminate\Database\Eloquent\Collection $payments
 * @property-read null|\App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment newQuery()
 * @method static \Illuminate\Database\Query\Builder|Enrollment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereNotState(string $column, $states)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereState(string $column, $states)
 * @method static \Illuminate\Database\Query\Builder|Enrollment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Enrollment withoutTrashed()
 * @mixin \Eloquent
 */
class Enrollment extends UuidModel
{
    use HasEncryptedAttributes;
    use HasStates;
    use SoftDeletes;

    public const USER_TYPE_MEMBER = 'member';

    public const USER_TYPE_GUEST = 'guest';

    /**
     * @inheritDoc
     */
    protected $encrypted = [
        'data',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'expire' => 'datetime',

        'data' => 'json',
        'paid' => 'bool',
        'price' => 'int',
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
    ];

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
     * An enrollment can have multiple payments (in case one failed, for example).
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
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
        return $this->belongsTo(Ticket::class);
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

    /**
     * Register the states an enrollment can have.
     */
    protected function registerStates(): void
    {
        // Register enrollment state
        $this
            ->addState('state', EnrollmentState::class)

            // Default to Created
            ->default(States\Created::class)

            // Create → Seeded
            ->allowTransition(States\Created::class, States\Seeded::class)

            // Created, Seeded → Confirmed
            ->allowTransition([States\Created::class, States\Seeded::class], States\Confirmed::class)

            // Created, Seeded, Confirmed → Paid
            ->allowTransition([States\Created::class, States\Seeded::class, States\Confirmed::class], States\Paid::class)

            // Created, Seeded, Confirmed, Paid → Cancelled
            ->allowTransition(
                [States\Created::class, States\Seeded::class, States\Confirmed::class, States\Paid::class],
                States\Cancelled::class,
            )

            // Paid, Cancelled → Refunded
            ->allowTransition(
                [States\Paid::class, States\Cancelled::class],
                States\Refunded::class,
            );
    }
}
