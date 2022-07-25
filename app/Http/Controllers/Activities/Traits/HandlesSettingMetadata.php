<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Helpers\Arr;
use App\Models\Activity;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\URL;

trait HandlesSettingMetadata
{
    /**
     * Assigns active meta.
     */
    public function setActivityMeta(Activity $activity): void
    {
        // Get props
        $url = $this->getCanonical($activity);

        // Set SEO
        SEOTools::setTitle($activity->name);
        SEOTools::setDescription($activity->tagline);
        SEOTools::setCanonical($this->getCanonical($activity));
        SEOTools::addImages([
            image_asset($activity->cover)->preset('social')->getUrl(),
            image_asset($activity->cover)->preset('social')->dpi(2)->getUrl(),
        ]);

        // Set Open Graph
        OpenGraph::setUrl($url);
        OpenGraph::addImages([
            image_asset($activity->cover)->preset('social')->getUrl(),
            image_asset($activity->cover)->preset('social')->dpi(2)->getUrl(),
        ]);

        // Build JSON
        $json = $this->getActivityJsonMeta($activity);
        JsonLd::setType($json['type']);
        JsonLd::setUrl($json['url']);
        JsonLd::setTitle($json['name']);
        JsonLd::setDescription($json['description']);
        JsonLd::setImage($json['image']);

        // Add remaining values
        $ignore = [
            'type',
            'url',
            'name',
            'description',
            'image',
        ];

        JsonLd::addValues(Arr::except($json, $ignore));
    }

    /**
     * Location meta.
     *
     * @return null|array<string>|string
     */
    public function buildLocationMeta(Activity $activity)
    {
        // Return null if there's no location
        if (! $activity->location) {
            return null;
        }

        // Check if online
        if (filter_var($activity->location_address, FILTER_VALIDATE_URL)) {
            return [
                '@type' => 'VirtualLocation',
                'name' => $activity->location,
                'url' => URL::to($activity->location_address),
            ];
        }

        // Add proper location
        if ($activity->location_address) {
            return [
                '@type' => 'Place',
                'name' => $activity->location,
                'address' => $activity->location_address,
            ];
        }

        return $activity->location;
    }

    /**
     * Get activity URL.
     */
    private function getCanonical(Activity $activity): string
    {
        return route('activity.show', compact('activity'));
    }

    /**
     * Returns metadata for this activity.
     */
    private function getActivityJsonMeta(Activity $activity): array
    {
        // Get props
        $url = $this->getCanonical($activity);

        $performer = [
            '@type' => 'Organization',
            'name' => 'Gumbo Millennium',
            'url' => 'https://www.gumbo-millennium.nl/',
            'department' => [
                '@type' => 'Organization',
                'name' => $activity->organiser,
            ],
        ];

        // Build JSON+LD
        $data = [
            'type' => 'SocialEvent',
            'url' => $url,
            'identifier' => $url,
            'name' => $activity->name,
            'description' => $activity->tagline,
            'image' => image_asset($activity->cover)->preset('social')->getUrl(),
            'startDate' => $activity->start_date->toIso8601String(),
            'endDate' => $activity->start_date->toIso8601String(),
            'organizer' => $performer,
            'performer' => $performer,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => $this->buildLocationMeta($activity),
        ];

        // Add location
        $isOnlineEvent = filter_var($activity->location_address, FILTER_VALIDATE_URL);
        $data['eventAttendanceMode'] = $isOnlineEvent ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode';

        if ($activity->is_cancelled) {
            // Check if the event was cancelled
            $data['eventStatus'] = 'https://schema.org/EventCancelled';
        } elseif ($activity->is_rescheduled) {
            // Check if the event was rescheduled
            $data['eventStatus'] = 'https://schema.org/EventRescheduled';
            $data['previousStartDate'] = $activity->rescheduled_from->toIso8601String();
        } elseif ($activity->is_postponed) {
            // Check if the event was postponed
            $data['eventStatus'] = 'https://schema.org/EventPostponed';
        }

        // Add offers
        $data['offers'] = $this->buildPricingAndTicketMeta($activity);

        // Add seat count, if applicable
        if (! $activity->is_cancelled && $activity->seats > 0 && $activity->available_seats > 0) {
            $data['remainingAttendeeCapacity'] = $activity->available_seats;
        }

        // Return data
        return $data;
    }

    /**
     * Adds pricing info.
     *
     * @return void
     */
    private function buildPricingAndTicketMeta(Activity $activity): ?array
    {
        if ($activity->tickets->isEmpty()) {
            JsonLd::addValue('isAccessibleForFree', $activity->tickets->isEmpty());

            return [];
        }
        // Prep list
        $offerList = [];

        // Add regular ticket
        $url = $this->getCanonical($activity);

        // Build default ticket
        $defaultOffer = [
            '@type' => 'Offer',
            'url' => $url,
            'priceCurrency' => 'EUR',
            'eligibleQuantity' => 1,
            'eligibleRegion' => [
                '@type' => 'Country',
                'name' => 'Netherlands',
                'alternateName' => 'Holland',
                'identifier' => 'NL',
                'sameAs' => 'https://en.wikipedia.org/wiki/Netherlands',
            ],
            'eligibleCustomerType' => 'http://purl.org/goodrelations/v1#Enduser',
            'acceptedPaymentMethod' => 'http://purl.org/goodrelations/v1#ByBankTransferInAdvance',
            'availableDeliveryMethod' => 'http://purl.org/goodrelations/v1#DeliveryModeDirectDownload',
            'offeredBy' => [
                '@type' => 'Organization',
                'name' => 'Gumbo Millennium',
                'url' => route('home'),
            ],
        ];

        foreach ($activity->tickets as $ticket) {
            $ticketId = sprintf('%04d-%04d', $activity->id, $ticket->id);

            $availability = $ticket->quantity_available === 0
                ? 'https://schema.org/SoldOut'
                : ($ticket->quantity > 0 ? 'https://schema.org/LimitedAvailability' : 'https://schema.org/InStock');

            $offerList[] = [
                ...$defaultOffer,
                'price' => sprintf('%.2f', $ticket->price / 100),
                'availability' => $availability,
                'validFrom' => $activity->start_date->toIso8601String(),
                'validThrough' => $activity->end_date->toIso8601String(),
                'name' => $ticket->name,
                'description' => $ticket->description,
                'sku' => $ticketId,
                'identifier' => $ticketId,
            ];
        }

        // Return offers
        return $offerList;
    }
}
