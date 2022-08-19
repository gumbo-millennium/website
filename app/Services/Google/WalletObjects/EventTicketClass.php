<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

use App\Services\Google\SmartModel;

class EventTicketClass extends SmartModel
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
     * @var string
     */
    public $eventId;

    /**
     * @var Image
     */
    public $logo;

    /**
     * @var EventVenue
     */
    public $venue;

    /**
     * @var EventDateTime
     */
    public $dateTime;

    /**
     * @var ConfirmationCodeLabel|string
     */
    public $confirmationCodeLabel;

    /**
     * @var LocalizedString
     */
    public $customConfirmationCodeLabel;

    /**
     * @var SeatLabel|string
     */
    public $seatLabel;

    /**
     * @var LocalizedString
     */
    public $customSeatLabel;

    /**
     * @var RowLabel|string
     */
    public $rowLabel;

    /**
     * @var LocalizedString
     */
    public $customRowLabel;

    /**
     * @var SetionLabel|string
     */
    public $sectionLabel;

    /**
     * @var LocalizedString
     */
    public $customSectionLabel;

    /**
     * @var GateLabel|string
     */
    public $gateLabel;

    /**
     * @var LocalizedString
     */
    public $customGateLabel;

    /**
     * @var LocalizedString
     */
    public $finePrint;

    /**
     * @var ClassTemplateInfo
     */
    public $classTemplateInfo;

    /**
     * @var string
     */
    public $id;

    /**
     * @deprecated
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $issuerName;

    /**
     * @var Message[]
     */
    public $messages;

    /**
     * @deprecated
     * @var bool
     */
    public $allowMultipleUsersPerObject;

    /**
     * @var Uri
     */
    public $homepageUri;

    /**
     * @var LatLongPoint[]
     */
    public $locations;

    /**
     * @var ReviewStatus
     */
    public $reviewStatus;

    /**
     * @var Review
     */
    public $review;

    /**
     * @deprecated
     * @var InfoModuleData
     */
    public $infoModuleData;

    /**
     * @var ImageModuleData[]
     */
    public $imageModuleData;

    /**
     * @var TextModuleData[]
     */
    public $textModulesData;

    /**
     * @var LinksModuleData
     */
    public $linksModuleData;

    /**
     * @var string[]
     */
    public $redemptionIssuers;

    /**
     * @var string
     */
    public $countryCode;

    /**
     * @var Image
     */
    public $heroImage;

    /**
     * @deprecated
     * @var Image
     */
    public $wordMark;

    /**
     * @var bool
     */
    public $enableSmartTap;

    /**
     * @var string
     */
    public $hexBackgroundColor;

    /**
     * @var LocalizedString
     */
    public $localizedIssuerName;

    /**
     * @var MultipleDevicesAndHoldersAllowedStatus
     */
    public $multipleDevicesAndHoldersAllowedStatus;

    /**
     * @var CallbackOptions
     */
    public $callbackOptions;

    /**
     * @var SecurityAnimation
     */
    public $securityAnimation;

    /**
     * @var ViewUnlockRequirements
     */
    public $viewUnlockRequirements;

    protected array $casts = [
        'eventName' => LocalizedString::class,
        'logo' => Image::class,
        'venue' => EventVenue::class,
        'dateTime' => EventDateTime::class,
        'customConfirmationCodeLabel' => LocalizedString::class,
        'customSeatLabel' => LocalizedString::class,
        'customRowLabel' => LocalizedString::class,
        'customSectionLabel' => LocalizedString::class,
        'customGateLabel' => LocalizedString::class,
        'finePrint' => LocalizedString::class,
        'classTemplateInfo' => ClassTemplateInfo::class,
        'messages' => [Message::class],
        'homepageUri' => Uri::class,
        'locations' => [LatLongPoint::class],
        'review' => Review::class,
        'imageModuleData' => [ImageModuleData::class],
        'textModulesData' => [TextModuleData::class],
        'linksModuleData' => LinksModuleData::class,
        'heroImage' => Image::class,
        'localizedIssuerName' => LocalizedString::class,
        'callbackOptions' => CallbackOptions::class,
        'securityAnimation' => SecurityAnimation::class,
    ];

    protected array $enums = [
        'confirmationCodeLabel' => ConfirmationCodeLabel::class,
        'seatLabel' => SeatLabel::class,
        'rowLabel' => RowLabel::class,
        'sectionLabel' => SetionLabel::class,
        'reviewStatus' => ReviewStatus::class,
        'gateLabel' => GateLabel::class,
        'multipleDevicesAndHoldersAllowedStatus' => MultipleDevicesAndHoldersAllowedStatus::class,
        'viewUnlockRequirements' => ViewUnlockRequirements::class,
    ];
}
