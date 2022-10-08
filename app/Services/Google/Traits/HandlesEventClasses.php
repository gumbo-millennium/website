<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Enums\Models\GoogleWallet\ReviewStatus;
use App\Models\GoogleWallet\EventClass;
use Google_Service_Exception as ServiceException;
use Google_Service_Walletobjects_EventTicketClass  as EventTicketClass;
use Google_Service_Walletobjects_Eventticketclass_Resource as EventTicketClassResource;
use Illuminate\Support\Facades\URL;

trait HandlesEventClasses
{
    use DeepComparesArraysAndObjects;

    abstract protected function getEventTicketClassApi(): EventTicketClassResource;

    /**
     * Writes a given EventClass to the Google Wallet API.
     */
    public function writeEventClass(EventClass $eventClass): EventClass
    {
        throw_unless(
            $eventClass->exists,
            LogicException::class,
            'The event class must exist before it can be created in Google Wallet.',
        );

        // Try to fetch the event class
        $existing = $this->findEventClass($eventClass);

        try {
            return $this->updateEventClass($eventClass, $existing);
        } catch (ServiceException $e) {
            if ($e->getCode() === 404) {
                return $this->createEventClass($eventClass);
            }

            throw $e;
        }
    }

    /**
     * Returns an array of data that's expected to be present on the event class, to
     * allow for PATCH updates.
     */
    protected function buildTicketClassData(EventClass $eventClass): array
    {
        return [
            'id' => $eventClass->wallet_id,
            'eventId' => $eventClass->wallet_id,
            'eventName' => [
                'defaultValue' => $eventClass->name,
            ],
            'logo' => [
                'sourceUri' => [
                    'uri' => URL::to(mix('images/logo-google-wallet.png')),
                ],
                'contentDescription' => [
                    'defaultValue' => 'Gumbo Millennium logo',
                ],
            ],
            'venue' => array_filter([
                'name' => [
                    'defaultValue' => $eventClass->location,
                ],
                'address' => $eventClass->address ? [
                    'defaultValue' => $eventClass->address,
                ] : null,
            ]),
            'dateTime' => [
                'start' => $eventClass->start_time->toIso8601String(),
                'end' => $eventClass->end_time->toIso8601String(),
            ],
            'confirmationCodeLabel' => 'ORDER_NUMBER',
            'finePrint' => [
                'defaultValue' => 'Dit ticket is niet inwisselbaar voor geld. Voor alle voorwaarden tijdens het evenement, zie ons privacybeleid.',
                'localizedValue' => [
                    'en' => 'This ticket is non-refundable. For all terms and conditions, please refer to our privacy policy.',
                ],
            ],
            'issuerName' => 'Gumbo Millennium',
            'homepageUri' => [
                'uri' => URL::to('/'),
            ],
            'countryCode' => 'NL',
            'heroImage' => $eventClass->hero_image ? [
                'sourceUri' => [
                    'uri' => $eventClass->hero_image,
                ],
            ] : null,
            'hexBackgroundColor' => '#006b00',
            'securityAnimation' => [
                'animationType' => 'FOIL_SHIMMER',
            ],
            'viewUnlockRequirement' => 'UNLOCK_REQUIRED_TO_VIEW',
        ];
    }

    /**
     * Finds the Google Wallet EventTicketClass for a given EventClass.
     *
     * @throws ServiceException
     */
    protected function findEventClass(EventClass $eventClass): ?EventTicketClass
    {
        try {
            return $this->getEventTicketClassApi()->get($eventClass->wallet_id);
        } catch (ServiceException $exception) {
            if ($exception->getCode() === 404) {
                return null;
            }

            throw $exception;
        }
    }

    protected function updateEventClass(EventClass $eventClass, EventTicketClass $existing): EventClass
    {
        $ticketClassData = $this->buildTicketClassData($eventClass);

        /** @var null|EventTicketClass $differences */
        $differences = $this->deepCompareArrayToObject($ticketClassData, $existing, EventTicketClass::class);

        if (empty($differences)) {
            return $eventClass;
        }

        $differences->reviewStatus = ReviewStatus::UnderReview;
        $result = $this->getEventTicketClassApi()->patch($existing->id, $differences);

        $eventClass->review_status = ReviewStatus::tryFrom($result->getReviewStatus()) ?? ReviewStatus::Unspecficied;
        $eventClass->review = $result->getReviewStatus();
        $eventClass->save();

        return $eventClass;
    }

    protected function createEventClass(EventClass $eventClass): EventClass
    {
        $ticketClassData = array_merge($this->buildTicketClassData($eventClass), [
            'reviewStatus' => $eventClass->review_status === ReviewStatus::Draft ? 'DRAFT' : 'UNDER_REVIEW',
        ]);

        $result = $this->getEventTicketClassApi()->insert(new EventTicketClass($ticketClassData));

        $eventClass->review_status = ReviewStatus::tryFrom($result->getReviewStatus()) ?? ReviewStatus::Unspecficied;
        $eventClass->review = $result->getReviewStatus();
        $eventClass->save();

        return $eventClass;
    }
}
