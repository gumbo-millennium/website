<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Helpers\Arr;
use App\Models\Activity;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;

trait HandlesSettingMetadata
{
    /**
     * Get activity URL
     * @param Activity $activity
     * @return string
     */
    private function getCanonical(Activity $activity): string
    {
        return route('activity.show', compact('activity'));
    }

    /**
     * Assigns active meta
     * @param Activity $activity
     * @return void
     */
    public function setActivityMeta(Activity $activity): void
    {
        // Get props
        $url = $this->getCanonical($activity);

        // Set SEO
        SEOMeta::setTitle($activity->name);
        SEOMeta::setDescription($activity->tagline);
        SEOMeta::setCanonical($this->getCanonical($activity));

        // Set Open Graph
        OpenGraph::setTitle($activity->name);
        OpenGraph::setDescription($activity->tagline);
        OpenGraph::setUrl($url);
        OpenGraph::addImages([
            $activity->image->url('social'),
            $activity->image->url('social-2x')
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
     * Returns metadata for this activity
     * @param Activity $activity
     * @return array
     */
    private function getActivityJsonMeta(Activity $activity): array
    {
        // Get props
        $url = $this->getCanonical($activity);

        // Build JSON+LD
        $data = [
            'type' => 'SocialEvent',
            'url' => $url,
            'identifier' => $url,
            'name' => $activity->name,
            'description' => $activity->tagline,
            'image' => $activity->image->url('social'),
            'organizer' => $activity->organiser,
            'startDate' => $activity->start_date->toIso8601String(),
            'endDate' => $activity->start_date->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => $this->buildLocationMeta($activity),
        ];

        // If cancelled, we're done here
        if ($activity->is_cancelled) {
            $data['eventStatus'] = 'https://schema.org/EventCancelled';
            return $data;
        }

        // Check if the event has been rescheduled
        if ($activity->is_rescheduled) {
            $data['eventStatus'] = 'https://schema.org/EventRescheduled';
            $data['previousStartDate'] = $activity->rescheduled_from->toIso8601String();
        }

        // Add offers
        $data['offers'] = $this->buildPricingAndTicketMeta($activity);

        // Add seat count
        if ($activity->seats > 0 && $activity->available_seats > 0) {
            $data['remainingAttendeeCapacity'] = $activity->available_seats;
        }

        // Return data
        return $data;
    }

    /**
     * Adds pricing info
     * @param Activity $activity
     * @return void
     */
    private function buildPricingAndTicketMeta(Activity $activity): ?array
    {

        // Add price info
        JsonLd::addValue('isAccessibleForFree', $activity->is_free);

        // Don't add if no price is set
        if (!$activity->total_price) {
            return null;
        }

        $url = $this->getCanonical($activity);
        $offers = [
            ["{$url}/regular", $activity->total_price, $activity->available_seats > 0]
        ];
        if ($activity->total_discount_price) {
            $offers[] = ["{$url}/discount", $activity->total_discount_price, $activity->discounts_available !== 0];
        }

        $offerList = [];

        $validFrom = ($activity->enrollment_start ?? now())->toIso8601String();
        $validThrough = ($activity->enrollment_end ?? $activity->end_date)->toIso8601String();
        $gumboOrg = [
            '@type' => 'Organization',
            'name' => 'Gumbo Millennium',
            'url' => url('/')
        ];

        $defaultOffer = [
            '@type' => 'Offer',
            'identifier' => null,
            'url' => null,
            'priceCurrency' => 'EUR',
            'price' => null,
            'eligibleQuantity' => 1,
            'eligibleRegion' => 'NL',
            'validFrom' => $validFrom,
            'validThrough' => $validThrough,
            'availability' => null,
            'acceptedPaymentMethod' => 'http://purl.org/goodrelations/v1#ByInvoice',
            'availableDeliveryMethod' => 'http://purl.org/goodrelations/v1#DeliveryModeDirectDownload',
            'offeredBy' => $gumboOrg
        ];

        foreach ($offers as [$id, $price, $available]) {
            $offerList[] = array_merge($defaultOffer, [
                'identifier' => $id,
                'url' => $url,
                'price' => $price / 100,
                'availability' => $available ? 'https://schema.org/OnlineOnly' : 'https://schema.org/SoldOut'
            ]);
        }

        // Add offers
        return $offerList;
    }

    public function buildLocationMeta(Activity $activity): ?array
    {
        // Add proper location
        if ($activity->location_address && $activity->location) {
            return [
                '@type' => 'Place',
                'name' => $activity->location,
                'address' => $activity->location_address
            ];
        }

        if ($activity->location) {
            return [
                '@type' => 'Place',
                'name' => $activity->location
            ];
        }

        return null;
    }
}
