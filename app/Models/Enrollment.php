<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\States\Enrollment\Cancelled as CancelledState;
use App\Models\States\Enrollment\Confirmed as ConfirmedState;
use App\Models\States\Enrollment\Created as CreatedState;
use App\Models\States\Enrollment\Paid as PaidState;
use App\Models\States\Enrollment\Refunded as RefundedState;
use App\Models\States\Enrollment\Seeded as SeededState;
use App\Models\States\Enrollment\State as EnrollmentState;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Spatie\ModelStates\HasStates;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A user enrollment for an activity. Optionally has payments.
 *
 * @property States\Enrollment\State $state
 * @property string $id
 * @property int $user_id
 * @property int $activity_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property \Illuminate\Support\Date|null $deleted_at
 * @property string|null $deleted_reason
 * @property int|null $price
 * @property int|null $total_price
 * @property string|null $payment_intent
 * @property string|null $payment_invoice
 * @property string|null $payment_source
 * @property string $user_type
 * @property \Illuminate\Support\Date|null $expire
 * @property string|null $transfer_secret
 * @property \Illuminate\Support\Collection|null $data
 * @property-read Activity $activity
 * @property-read bool $is_discounted
 * @property-read bool $is_stable
 * @property-read bool $requires_payment
 * @property-read States\Enrollment\State|null $wanted_state
 * @property-read \Illuminate\Database\Eloquent\Collection<Payment> $payments
 * @property-read User $user
 * @property-read array<scalar>|null $form The form data ready for export
 * @property-read array<scalar>|null $form_data The form data to supply to the form builder
 */
class Enrollment extends UuidModel
{
    use HasEncryptedAttributes;
    use HasStates;
    use SoftDeletes;

    public const USER_TYPE_MEMBER = 'member';
    public const USER_TYPE_GUEST = 'guest';

    /**
     * Finds the active enrollment for this activity
     *
     * @param User $user
     * @param Activity $activity
     * @return Enrollment|null
     */
    public static function findActive(User $user, Activity $activity): ?Enrollment
    {
        return self::query()
            ->withoutTrashed()
            ->whereUserId($user->id)
            ->whereActivityId($activity->id)
            ->whereNotState('state', [CancelledState::class, RefundedState::class])
            ->with(['activity'])
            ->first();
    }

    /**
     * Finds the active enrollment for this activity, or throws a 404 HTTP exception
     *
     * @param User $user
     * @param Activity $activity
     * @return Enrollment
     * @throws NotFoundHttpException if there is no enrollment present
     */
    public static function findActiveOrFail(User $user, Activity $activity): Enrollment
    {
        $result = self::findActive($user, $activity);
        if ($result) {
            return $result;
        }
        throw new NotFoundHttpException();
    }

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
        'data' => 'json',
        'paid' => 'bool',
    ];

    /**
     * @inheritDoc
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expire',
    ];

    /**
     * An enrollment can have multiple payments (in case one failed, for example)
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The user this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function activity(): Relation
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Returns true if the state is stable and will not auto-delete
     *
     * @return bool
     */
    public function getIsStableAttribute(): bool
    {
        return $this->state instanceof ConfirmedState;
    }

    /**
     * Returns if the enrollment is discounted.
     *
     * @return bool
     */
    public function getIsDiscountedAttribute(): bool
    {
        return $this->price === $this->activity->discount_price;
    }

    /**
     * Returns state we want to go to, depending on Enrollment's own attributes.
     * Returns null if it can't figure it out.
     *
     * @return App\Models\States\Enrollment\State|null
     */
    public function getWantedStateAttribute(): ?EnrollmentState
    {
        // First check for any transition
        $options = $this->state->transitionableStates();
        if (in_array(SeededState::$name, $options) && $this->activity->form) {
            return new SeededState($this);
        }

        if (in_array(PaidState::$name, $options) && $this->price) {
            return new PaidState($this);
        }

        if (in_array(ConfirmedState::$name, $options)) {
            return new ConfirmedState($this);
        }

        return null;
    }

    public function getRequiresPaymentAttribute(): bool
    {
        return $this->exists &&
            $this->total_price &&
            !($this->state instanceof CancelledState);
    }

    /**
     * Stores the enrollment data on this user.
     *
     * @param array $values
     * @return void
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

        // Re-apply
        $this->data = $data;
    }

    /**
     * Returns the filled in form.
     *
     * @return array|null
     */
    public function getFormAttribute(): ?array
    {
        return Arr::get($this->data, 'form.exportable');
    }

    /**
     * Returns the data for this form, as it's presented to the form builder.
     *
     * @return mixed
     */
    public function getFormDataAttribute()
    {
        return Arr::get($this->data, 'form.fields');
    }

    /**
     * Register the states an enrollment can have
     *
     * @return void
     */
    protected function registerStates(): void
    {
        // Register enrollment state
        $this
            ->addState('state', EnrollmentState::class)

            // Default to Created
            ->default(CreatedState::class)

            // Create → Seeded
            ->allowTransition(CreatedState::class, SeededState::class)

            // Created, Seeded → Confirmed
            ->allowTransition([CreatedState::class, SeededState::class], ConfirmedState::class)

            // Created, Seeded, Confirmed → Paid
            ->allowTransition([CreatedState::class, SeededState::class, ConfirmedState::class], PaidState::class)

            // Created, Seeded, Confirmed, Paid → Cancelled
            ->allowTransition(
                [CreatedState::class, SeededState::class, ConfirmedState::class, PaidState::class],
                CancelledState::class
            )

            // Paid, Cancelled → Refunded
            ->allowTransition(
                [PaidState::class, CancelledState::class],
                RefundedState::class
            );
    }
}
