<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class EventTicketObject extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var LocalizedString
     */
    public $eventName;

    /**
     * @var EventTicketClass
     */
    public $classReference;

    /**
     * @var EventSeat
     */
    public $seatInfo;

    /**
     * @var EventReservationInfo
     */
    public $reservationInfo;

    /**
     * @var string
     */
    public $ticketHolderName;

    /**
     * @var string
     */
    public $ticketNumber;

    /**
     * @var LocalizedString
     */
    public $ticketType;

    /**
     * @var Money
     */
    public $faceValue;

    /**
     * @var GroupingInfo
     */
    public $groupingInfo;

    /**
     * @var string[]
     */
    public $linkedOfferIds;

    /**
     * @var string
     */
    public $hexBackgroundColor;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $classId;

    /**
     * @var string
     */
    public $version;

    /**
     * @var State
     */
    public $state;

    /**
     * @var Barcode
     */
    public $barcode;

    /**
     * @var Message[]
     */
    public $messages;

    /**
     * @var TimeInterval
     */
    public $validTimeInterval;

    /**
     * @var LatLongPoint[]
     */
    public $locations;

    /**
     * @var bool
     */
    public $hasUsers;

    /**
     * @var string
     */
    public $smartTapRedemptionValue;

    /**
     * @var bool
     */
    public $hasLinkedDevice;

    /**
     * @var bool
     */
    public $disableExpirationNotification;

    /**
     * @var InfoModuleData
     */
    public $infoModuleData;

    /**
     * @var ImageModuleData[]
     */
    public $imageModulesData;

    /**
     * @var TextModuleData[]
     */
    public $textModulesData;

    /**
     * @var LinksModuleData
     */
    public $linksModuleData;

    /**
     * @var AppLinkData
     */
    public $appLinkData;

    /**
     * @var RotatingBarcode
     */
    public $rotatingBarcode;

    /**
     * @var Image
     */
    public $heroImage;
}
