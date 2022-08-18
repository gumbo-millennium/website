<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Fluent\Image;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentState;
use App\Services\Google\WalletObjects;
use App\Services\Google\WalletObjects\EventTicketClass;
use Illuminate\Support\Facades\Date;

trait CreatesWalletObjects
{
    abstract public function getIssuerId(): string;

    abstract public function getActivityClassId(Activity $activity): string;

    abstract public function getEnrollmentObjectId(Enrollment $enrollment): string;

    /**
     * Returns the EventTicketClass for this Activity, can be used to create or update the class.
     * @return EventTicketClass
     */
    public function makeActivityTicketClass(Activity $activity): WalletObjects\EventTicketClass
    {
        $ticket = $this->makeBaseTicketClass();

        $ticket->id = $this->getActivityClassId($activity);
        $ticket->eventId = $ticket->id;
        $ticket->eventName = WalletObjects\LocalizedString::create('nl', $activity->name);

        $ticket->logo = WalletObjects\Image::create(url(mix('images/logo-google-wallet.png')), 'Logo Gumbo Millennium');

        $ticket->dateTime = new WalletObjects\DateTime([
            'start' => $activity->start_date->toIso8601String(),
            'end' => $activity->end_date->toIso8601String(),
        ]);

        $ticket->linksModuleData = new WalletObjects\LinksModuleData([
            'uris' => [
                WalletObjects\Uri::create(
                    route('activity.show', $activity),
                    'Naar activiteit',
                ),
                WalletObjects\Uri::create(
                    route('enroll.show', $activity),
                    'Toon inschrijving',
                ),
            ],
        ]);

        // Add header image
        if ($activity->poster) {
            $ticket->heroImage = new WalletObjects\Image([
                'sourceUri' => new WalletObjects\ImageUri([
                    'uri' => Image::make($activity->poster)
                        ->width(1023)
                        ->height(336)
                        ->fit('crop')
                        ->png()
                        ->getUrl(),
                ]),
            ]);
        }

        // Add address if required
        if ($activity->location_address) {
            $ticket->venue = new WalletObjects\EventVenue([
                'name' => WalletObjects\LocalizedString::create('nl', $activity->location ?? $activity->location_address),
                'address' => WalletObjects\LocalizedString::create('nl', $activity->location_address),
            ]);
        }

        return $ticket;
    }

    /**
     * Returns the Google Wallet Ticket Object for the given enrollment.
     */
    public function makeEnrollmentTicketObject(Enrollment $enrollment): WalletObjects\EventTicketObject
    {
        $object = new WalletObjects\EventTicketObject();

        // Set mandatory IDs
        $object->id = $this->getEnrollmentObjectId($enrollment);
        $object->classId = $this->getActivityClassId($enrollment->activity);

        // Set mandatory data
        $object->reservationInfo = new WalletObjects\EventReservationInfo([
            'confirmationCode' => $enrollment->id,
        ]);
        $object->ticketHolderName = $enrollment->user->name;
        $object->ticketNumber = $enrollment->id;
        $object->ticketType = WalletObjects\LocalizedString::create('nl', $enrollment->ticket->title);
        $object->faceValue = WalletObjects\Money::createForCents($enrollment->ticket->price);

        // Set State
        $object->state = WalletObjects\State::INACTIVE;
        if ($enrollment->activity->end_date < Date::now() || $enrollment->state instanceof EnrollmentState\Cancelled) {
            $object->state = WalletObjects\State::EXPIRED;
        } elseif ($enrollment->consumed_at !== null) {
            $object->state = WalletObjects\State::COMPLETED;
        } elseif ($enrollment->state instanceof EnrollmentState\Confirmed) {
            $object->state = WalletObjects\State::ACTIVE;
        }

        // Set barcode
        $object->barcode = new WalletObjects\Barcode([
            'type' => WalletObjects\BarcodeType::QR_CODE,
            'value' => $enrollment->ticket_code,
        ]);

        return $object;
    }

    /**
     * Returns a base event ticket class to use for all tickets.
     * @return EventTicketClass
     */
    private function makeBaseTicketClass(): WalletObjects\EventTicketClass
    {
        $ticket = new WalletObjects\EventTicketClass();

        $ticket->issuerName = 'Gumbo Millennium';
        $ticket->homepageUri = WalletObjects\Uri::create(url('/'), 'gumbo-millennium.nl');
        $ticket->reviewStatus = WalletObjects\ReviewStatus::UNDER_REVIEW;
        $ticket->countryCode = 'NL';
        $ticket->hexBackgroundColor = '#006b00';
        $ticket->multipleDevicesAndHoldersAllowedStatus = WalletObjects\MultipleDevicesAndHoldersAllowedStatus::ONE_USER_ALL_DEVICES;
        $ticket->securityAnimation = WalletObjects\SecurityAnimation::create(WalletObjects\AnimationType::FOIL_SHIMMER);
        $ticket->viewUnlockRequirement = WalletObjects\ViewUnlockRequirements::UNLOCK_NOT_REQUIRED;
        $ticket->confirmationCodeLabel = WalletObjects\ConfirmationCodeLabel::CONFIRMATION_NUMBER;
        $ticket->finePrint = WalletObjects\LocalizedString::create('nl', <<<'TEXT'
        Aan dit e-ticket kunnen geen rechten worden ontleend.
        Een Google Wallet ticket is geen gegarandeerd entreebewijs, hiervoor heb je een PDF in je e-mail ontvangen.
        TEXT);

        return $ticket;
    }
}
