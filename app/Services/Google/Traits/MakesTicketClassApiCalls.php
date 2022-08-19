<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Helpers\Arr;
use App\Models\Activity;
use App\Services\Google\WalletObjects;
use App\Services\Google\WalletObjects\EventTicketClass;
use App\Services\Google\WalletObjects\Message;
use Generator;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

trait MakesTicketClassApiCalls
{
    abstract public function getIssuerId(): string;

    abstract public function getActivityClassId(Activity $activity): string;

    abstract protected function sendRequest(string $method, string $url, array $options = []): mixed;

    /**
     * Returns all existing Ticket Objects for the given Activity or EventTicketClass.
     * @return EventTicketClass[]
     */
    public function listActivityTicketClasses(): Generator
    {
        $requestUriParams = http_build_query([
            'issuerId' => $this->getIssuerId(),
        ]);

        return array_map(
            fn ($resource) => new EventTicketClass(Arr::only($resource, ['id', 'eventId', 'reviewStatus'])),
            $this->sendRequest('GET', "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketClass?{$requestUriParams}")['resources'],
        );
    }

    /**
     * Returns the TicketClass listed on the Google Wallet API, returns null if not found.
     * @throws GuzzleException
     */
    public function getActivityTicketClass(Activity|EventTicketClass $eventTicketClass): ?EventTicketClass
    {
        $ticketClassId = $eventTicketClass instanceof Activity ? $this->getActivityClassId($eventTicketClass) : $eventTicketClass->id;

        try {
            return new EventTicketClass($this->sendRequest('GET', "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketClass/{$ticketClassId}"));
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Creates a new EventTicketClass on the Google Wallet API. Returns the created EventTicketClass.
     * @throws GuzzleException
     */
    public function insertActivityTicketClass(EventTicketClass $ticketClass): EventTicketClass
    {
        return new EventTicketClass($this->sendRequest(
            'POST',
            'https://walletobjects.googleapis.com/walletobjects/v1/eventTicketClass',
            ['body' => $ticketClass],
        ));
    }

    /**
     * Updates an existing EventTicketClass on the Google Wallet API. Returns the updated EventTicketClass.
     * @throws GuzzleException
     */
    public function updateActivityTicketClass(EventTicketClass $ticketClass): EventTicketClass
    {
        return new EventTicketClass($this->sendRequest(
            'PUT',
            "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketClass/{$ticketClass->id}",
            ['body' => $ticketClass],
        ));
    }

    /**
     * Adds a message to the given EventTicketClass (or Activity), returns the updated EventTicketClass.
     * @param Message $message
     * @throws GuzzleException
     */
    public function addActivityTicketClassMessage(Activity|EventTicketClass $ticketClass, WalletObjects\Message $message): EventTicketClass
    {
        $ticketClassId = $ticketClass instanceof Activity ? $this->getActivityClassId($ticketClass) : $ticketClass->id;

        return new EventTicketClass($this->sendRequest(
            'PUT',
            "https://walletobjects.googleapis.com/walletobjects/v1/eventTicketClass/{$ticketClassId}/addMessage",
            ['body' => ['message' => $message]],
        )['resource']);
    }
}
