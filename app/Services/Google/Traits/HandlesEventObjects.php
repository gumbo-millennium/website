<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Enums\Models\GoogleWallet\ObjectState;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventObject;
use Brick\Money\Money;
use Google_Service_Exception as ServiceException;
use Google_Service_Walletobjects_EventTicketObject  as EventTicketObject;
use Google_Service_Walletobjects_Eventticketobject_Resource as EventTicketObjectResource;
use Illuminate\Support\Facades\Date;

trait HandlesEventObjects
{
    use DeepComparesArraysAndObjects;

    abstract protected function getEventTicketObjectApi(): EventTicketObjectResource;

    /**
     * Writes a given EventObject to the Google Wallet API.
     */
    public function writeEventObject(EventObject $eventObject): EventObject
    {
        throw_unless(
            $eventObject->exists,
            LogicException::class,
            'The event object must exist before it can be created in Google Wallet.',
        );

        // Try to fetch the event class
        $existing = $this->findEventObject($eventObject);

        try {
            return $this->updateEventObject($eventObject, $existing);
        } catch (ServiceException $e) {
            if ($e->getCode() === 404) {
                return $this->createEventObject($eventObject);
            }

            throw $e;
        }
    }

    /**
     * Determine the proper state of this EventObject.
     */
    protected function determineProperState(EventObject $object): ObjectState
    {
        if ($object->class->end_date < Date::now()) {
            return ObjectState::Expired;
        }

        $subject = $object->subject;
        if ($subject instanceof Enrollment) {
            return match (true) {
                $subject->trashed() => ObjectState::Inactive,
                $subject->consumed() => ObjectState::Completed,
                default => ObjectState::Active,
            };
        }

        return ObjectState::Inactive;
    }

    /**
     * Returns an array of data that's expected to be present on the event object, to
     * allow for PATCH updates.
     */
    protected function buildTicketObjectData(EventObject $eventObject): array
    {
        $eventClass = $eventObject->class;
        $objectState = $this->determineProperState($eventObject);

        // Show barcode if the event start time is within 9 hours from now (or in the past)
        $shouldShowBarcode = $eventClass->start_time->isBefore(Date::now()->addHours(9));

        return [
            'id' => $eventObject->wallet_id,
            'classId' => $eventClass->wallet_id,
            'reservationInfo' => [
                'confirmationCode' => $eventObject->ticket_number,
            ],
            'ticketHolderName' => $eventObject->owner->name,
            'ticketNumber' => $eventObject->ticket_number,
            'ticketType' => [
                'defaultValue' => [
                    'language' => 'nl',
                    'value' => $eventObject->ticket_type,
                ],
            ],
            'faceValue' => [
                'micros' => ($eventObject->value ?? Money::zero('EUR'))->getAmount()->toFloat() * 1_000_000,
                'currencyCode' => 'EUR',
            ],
            'state' => $objectState,
            'barcode' => $shouldShowBarcode && $eventObject->barcode ? [
                'type' => 'QR_CODE',
                'renderEncoding' => 'UTF_8',
                'value' => $eventObject->barcode,
            ] : null,
            'validTimeInterval' => [
                'start' => [
                    'date' => $eventClass->start_time->clone()->subHours(6)->toIso8601String(),
                ],
                'end' => [
                    'date' => $eventClass->end_time->toIso8601String(),
                ],
            ],
        ];
    }

    /**
     * Finds the Google Wallet EventTicketObject for a given EventObject.
     *
     * @throws ServiceException
     */
    protected function findEventObject(EventObject $eventObject): ?EventTicketObject
    {
        try {
            return $this->getEventTicketObjectApi()->get($eventObject->wallet_id);
        } catch (ServiceException $exception) {
            if ($exception->getCode() === 404) {
                return null;
            }

            throw $exception;
        }
    }

    protected function updateEventObject(EventObject $eventObject, EventTicketObject $existing): EventObject
    {
        $ticketClassData = $this->buildTicketObjectData($eventObject);

        /** @var null|EventTicketObject $differences */
        $differences = $this->deepCompareArrayToObject($ticketClassData, $existing, EventTicketObject::class);

        if (empty($differences)) {
            return $eventObject;
        }

        $this->getEventTicketObjectApi()->patch($existing->id, $differences);
        $eventObject->save();

        return $eventObject;
    }

    protected function createEventObject(EventObject $eventObject): EventObject
    {
        $ticketObjectData = $this->buildTicketObjectData($eventObject);

        $result = $this->getEventTicketObjectApi()->insert(new EventTicketObject($ticketObjectData));
        $eventObject->save();

        return $eventObject;
    }
}
