<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Enums\Models\GoogleWallet\ObjectState;
use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Fluent\Image;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use LogicException;

trait HandlesModels
{
    /**
     * Converts an Activity to a new or existing EventClass object, with the
     * EventClass' data matching the Activity.
     */
    protected function buildEventClassForActivity(Activity $activity): EventClass
    {
        $eventClass = EventClass::forSubject($activity)->first();

        if (! $eventClass) {
            $eventClass = new EventClass();
            $eventClass->subject()->associate($activity);
        }

        // Set data and save object
        $eventClass->fill([
            'name' => $activity->name,
            'review_status' => ReviewStatus::Draft,
            'location_name' => $activity->location,
            'location_address' => $activity->location_address,
            'start_time' => $activity->start_date,
            'end_time' => $activity->end_date,
            'uri' => route('activity.show', $activity),
            'hero_image' => $activity->poster ? Image::make($activity->poster)->preset('social')->getUrl() : null,
        ]);

        $eventClass->save();

        return $eventClass;
    }

    /**
     * Converts an Enrollment to a new or existing EventObject object, with the
     * EventObject' data matching the Enrollment.
     */
    protected function buildEventObjectForEnrollment(Enrollment $enrollment): EventObject
    {
        $activity = $enrollment->activity;
        $eventObject = EventObject::forSubject($enrollment)->first();

        if (! $eventObject) {
            $eventObject = new EventObject();
            $eventObject->subject()->associate($enrollment);
            $eventObject->owner()->associate($enrollment->user);

            $eventClass = EventClass::forSubject($activity)->first();
            throw_unless($eventClass, LogicException::class, 'The event class must exist before an event object can be created.');
            $eventObject->class()->associate($eventClass);
        }

        $properState = ObjectState::Inactive;
        if ($enrollment->consumed()) {
            $properState = ObjectState::Completed;
        } elseif ($enrollment->trashed() || $enrollment->state instanceof Cancelled) {
            $properState = ObjectState::Expired;
        } elseif ($enrollment->state instanceof Confirmed) {
            $properState = ObjectState::Active;
        }

        // Set data and save object
        $eventObject->fill([
            'value' => money_value($enrollment->total_price),
            'ticket_number' => $enrollment->id,
            'ticket_type' => $enrollment->ticket->title,

            // Always provide the barcode.
            'barcode' => $enrollment->ticket_code,
            'state' => $properState,
        ]);

        $eventObject->save();

        return $eventObject;
    }
}
