<?php

declare(strict_types=1);

namespace App\Jobs\GoogleWallet;

use App\Fluent\Image;
use App\Models\Activity;
use Google\Client as GoogleClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config ;
use LogicException;

class CreateEventClassJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const DEFAULT_EVENT_CLASS = [
        'issuerName' => 'Gumbo Millennium',
        'homepageUri' => [
            'uri' => 'https://www.gumbo-millennium.nl/',
            'description' => 'gumbo-millennium.nl',
        ],
        'reviewStatus' => 'underReview',
        'countryCode' => 'NL',
        'hexBackgroundColor' => '#006b00',
        'multipleDevicesAndHoldersAllowedStatus' => 'ONE_USER_ALL_DEVICES',
        'securityAnimation' => 'FOIL_SHIMMER',
        'viewUnlockRequirement' => 'UNLOCK_NOT_REQUIRED',
        'confirmationCodeLabel' => 'ORDER_NUMBER',
        'finePrint' => <<<'TEXT'
        Aan dit e-ticket kunnen geen rechten worden ontleend.
        Een Google Wallet ticket is geen gegarandeerd entreebewijs, hiervoor heb je een PDF in je e-mail ontvangen.
        TEXT,
    ];

    public Activity $activity;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Construct issuer ID
        $issuerId = Config::get('services.google.wallet.issuer_id');

        // Construct event class
        $eventClass = self::DEFAULT_EVENT_CLASS + [
            'id' => sprintf('%s.A%05d', $issuerId, $this->activity->id),
            'eventId' => sprintf('A%05d', $this->activity->id),
            'eventName' => [
                'defaultValue' => [
                    'language' => 'nl',
                    'value' => $this->activity->title,
                ],
            ],
            'logo' => [
                'sourceUri' => [
                    'uri' => mix('images/logo-square.svg'),
                ],
            ],
            'dateTime' => [
                'start' => $this->activity->start_date->toIso8601String(),
                'end' => $this->activity->end_date->toIso8601String(),
            ],
            'linksModulesData' => [
                'uris' => [
                    [
                        'uri'
                        => route('activity.show', $this->activity),
                        'description' => 'Naar activiteit',
                    ],
                    [
                        'uri' => route('enroll.show', $this->activity),
                        'description' => 'Toon inschrijving',
                    ],
                ],
            ],
        ];

        // Add header image
        if ($this->activity->poster) {
            $eventClass['heroImage'] = [
                'sourceUri' => [
                    'uri' => Image::make($this->activity->poster)
                        ->width(1023)
                        ->height(336)
                        ->fit('crop')
                        ->png()
                        ->getUrl(),
                ],
            ];
        }

        // Add address if required
        if ($this->activity->location_address) {
            $eventClass['venue'] = [
                'name' => [
                    'defaultValue' => [
                        'language' => 'nl',
                        'value' => $this->activity->location_name,
                    ],
                ],
                'address' => [
                    'defaultValue' => [
                        'language' => 'nl',
                        'value' => $this->activity->location_address,
                    ],
                ],
            ];
        }

        // Use the Google Client to post or patch the event
        /** @var GoogleClient $apiClient */
        $apiClient = App::make('google_wallet_api');
        throw_unless($apiClient instanceof GoogleClient, new LogicException('Failed to create a Google Client for the Google Wallet'));

        // TODO
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return sprintf('%05d', $this->activity->id);
    }
}
