<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Enums\Models\BarcodeType;
use App\Enums\Models\GoogleWallet\ObjectState;
use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Fluent\Image;
use App\Models\Activity;
use App\Models\ActivityMessage;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use App\Models\GoogleWallet\Message;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Confirmed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
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
            'barcode' => $enrollment->barcode,
            'barcode_type' => $this->convertBarcodeType($enrollment->barcode_type),
            'state' => $properState,
        ]);

        // Save object
        $eventObject->save();

        // Add messages (requires the object to be saved first)
        $messages = $this->findEnrollmentMessages($enrollment);
        $eventObject->messages()->sync($messages->pluck('id'));

        return $eventObject;
    }

    /**
     * Converts a BarcodeType to a string that Google understands.
     */
    private function convertBarcodeType(BarcodeType $type): string
    {
        return match ($type) {
            BarcodeType::CODABAR => 'CODABAR',
            BarcodeType::CODE39 => 'CODE_39',
            BarcodeType::CODE128 => 'CODE_128',
            BarcodeType::EAN8 => 'EAN_8',
            BarcodeType::EAN13 => 'EAN_13',
            BarcodeType::QRCODE => 'QR_CODE',
            default => 'textOnly',
        };
    }

    /**
     * Returns all messages that belong to the given enrollment.
     * @return Collection|Message[]
     */
    private function findEnrollmentMessages(Enrollment $enrollment): Collection
    {
        $existingActivityMessages = ActivityMessage::query()
            ->ForEnrollment($enrollment)
            ->shouldBeSent()
            ->orderByDesc('sent_at')
            ->take(5)
            ->get();

        $upcommingActivityMessage = ActivityMessage::query()
            ->forEnrollment($enrollment)
            ->where('scheduled_at', '>', Date::now())
            ->orderBy('scheduled_at')
            ->take(1)
            ->get();

        $activityMessages = $upcommingActivityMessage
            ->concat($existingActivityMessages);

        $defaultMessageExpiration = $enrollment->activity->end_date->addDays(7)->startOfHour();

        $walletMessages = Collection::make();

        foreach ($activityMessages as $activityMessage) {
            $messageStartTime = $activityMessage->sent_at ?? $activityMessage->scheduled_at ?? $activityMessage->created_at;
            $messageEndTime = $defaultMessageExpiration->max((clone $messageStartTime)->addDays(7)->startOfHour());

            $walletMessage = Message::firstOrNew([
                'activity_message_id' => $activityMessage->id,
            ]);

            $walletMessage->fill([
                'header' => $activityMessage->subject,
                'body' => $activityMessage->body,
                'start_time' => $messageStartTime,
                'end_time' => $messageEndTime,
            ]);

            $walletMessage->save();

            $walletMessages->push($walletMessage);
        }

        return $walletMessages;
    }
}
