<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Models\GoogleWallet\EventObject;
use App\Models\GoogleWallet\Message;
use Brick\Money\Money;
use Google\Service\Exception as ServiceException;
use Google\Service\Walletobjects\EventTicketObject as EventTicketObject;
use Google\Service\Walletobjects\Resource\Eventticketobject as EventTicketObjectResource;
use LogicException;

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

        // If it exists, update it
        return $existing
            ? $this->updateEventObject($eventObject, $existing)
            : $this->createEventObject($eventObject, $existing);
    }

    /**
     * Returns an array of data that's expected to be present on the event object, to
     * allow for PATCH updates.
     */
    protected function buildTicketObjectData(EventObject $eventObject): array
    {
        $eventClass = $eventObject->class;

        return [
            'id' => $eventObject->wallet_id,
            'classId' => $eventClass->wallet_id,
            'ticketHolderName' => $eventObject->owner->name,
            'ticketNumber' => $eventObject->ticket_number,
            'ticketType' => [
                'defaultValue' => [
                    'language' => 'nl',
                    'value' => $eventObject->ticket_type,
                ],
            ],
            'faceValue' => [
                'micros' => round(($eventObject->value ?? Money::zero('EUR'))->getAmount()->toFloat() * 1_000_000),
                'currencyCode' => 'EUR',
            ],
            'state' => $eventObject->state->value,
            'barcode' => [
                'type' => $eventObject->barcode_type,
                'value' => $eventObject->barcode,
                'alternateText' => $eventObject->barcode,
            ],
            'validTimeInterval' => [
                'start' => [
                    'date' => $eventClass->start_time->clone()->subHours(6)->toIso8601String(),
                ],
                'end' => [
                    'date' => $eventClass->end_time->toIso8601String(),
                ],
            ],
            'messages' => $eventObject->messages->map(fn (Message $message) => [
                'id' => $message->id,
                'header' => $message->header,
                'body' => $message->body,
                'displayInterval' => [
                    'start' => [
                        'date' => $message->start_time->toIso8601String(),
                    ],
                    'end' => [
                        'date' => $message->end_time->toIso8601String(),
                    ],
                ],
            ]),
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
