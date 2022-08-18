<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Helpers\Arr;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Services\Google\WalletObjects;
use App\Services\Google\WalletObjects\EventTicketClass;
use App\Services\Google\WalletObjects\EventTicketObject;
use App\Services\Google\WalletObjects\Message;
use Generator;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

trait MakesTicketObjectApiCalls
{
    abstract public function getIssuerId(): string;

    abstract public function getActivityClassId(Activity $activity): string;

    abstract public function getEnrollmentObjectId(Enrollment $enrollment): string;

    abstract protected function sendRequest(string $method, string $url, array $options = []): mixed;

    /**
     * Returns all existing Ticket Objects for the given Activity or EventTicketClass.
     * @return EventTicketObject[]
     */
    public function listEnrollmentTicketObjects(Activity|EventTicketClass $eventTicketClass): Generator
    {
        $eventTicketClassId = $eventTicketClass instanceof Activity ? $this->getActivityClassId($eventTicketClass) : $eventTicketClass->id;

        $requestUriParams = http_build_query([
            'classId' => $eventTicketClassId,
        ]);

        return array_map(
            fn ($resource) => new EventTicketObject(Arr::only($resource, ['id', 'classId', 'state'])),
            $this->sendRequest('GET', "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketObject?{$requestUriParams}")['resources'],
        );
    }

    /**
     * Returns the EventTicketObject listed on the Google Wallet API, returns null if not found.
     * @throws GuzzleException
     */
    public function getEnrollmentTicketObject(Enrollment|EventTicketObject $ticketObject): ?EventTicketObject
    {
        $ticketObjectId = $ticketObject instanceof Enrollment ? $this->getEnrollmentObjectId($ticketObject) : $ticketObject->id;

        try {
            return new EventTicketObject($this->sendRequest('GET', "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketObject/{$ticketObjectId}"));
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Creates a new EventTicketObject on the Google Wallet API. Returns the created EventTicketObject.
     * @throws GuzzleException
     */
    public function insertEnrollmentTicketObject(EventTicketObject $ticketObject): EventTicketObject
    {
        return new EventTicketObject($this->sendRequest(
            'POST',
            'https://walletobjects.googleapis.com/walletobjects/v1/eventTicketObject',
            ['body' => $ticketObject],
        ));
    }

    /**
     * Updates an existing EventTicketObject on the Google Wallet API. Returns the updated EventTicketObject.
     * @throws GuzzleException
     */
    public function updateEnrollmentTicketObject(EventTicketObject $ticketObject): EventTicketObject
    {
        return new EventTicketObject($this->sendRequest(
            'PUT',
            "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketObject/{$ticketObject->id}",
            ['body' => $ticketObject],
        ));
    }

    /**
     * Adds a message to the given EventTicketObject, returns the updated EventTicketObject.
     * @param Message $message
     * @throws GuzzleException
     */
    public function addEnrollmentTicketObjectMessage(Enrollment|EventTicketObject $ticketObject, WalletObjects\Message $message): EventTicketObject
    {
        $ticketObjectId = $ticketObject instanceof Enrollment ? $this->getEnrollmentObjectId($ticketObject) : $ticketObject->id;

        return new EventTicketObject($this->sendRequest(
            'PUT',
            "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketObject/{$ticketObjectId}/addMessage",
            ['body' => ['message' => $message]],
        )['resource']);
    }
}
