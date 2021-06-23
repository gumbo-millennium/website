<?php

declare(strict_types=1);

namespace App\Jobs\Stripe\Hooks;

use App\Jobs\Stripe\StripeJob;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event;
use Stripe\StripeObject;
use Stripe\Util\Util;

/**
 * Basic Stripe job, with a webhook.
 */
abstract class StripeWebhookJob extends StripeJob
{
    /**
     * Provided webhook.
     */
    protected WebhookCall $webhook;

    /**
     * The event we're processing.
     */
    protected ?Event $event = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WebhookCall $webhook)
    {
        // Bind webhook
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     */
    final public function handle(): void
    {
        // Get event
        $this->event = $event = Event::constructFrom($this->webhook->payload);

        // Ensure that the application is in the same mode as the source of the event.
        // This ensures that test data is never read by systems in production
        $appInLiveMode = (bool) config('stripe.test_mode', true) === false;
        if ($event->livemode !== $appInLiveMode) {
            logger()->warning('Mismatch on event mode, got {request-mode}, but want {set-mode}', [
                'request-mode' => $event->livemode,
                'set-mode' => config('stripe.test_mode', true),
                'event' => $event,
            ]);
            abort(403, "Event's origin mode is mismatching with the website mode.");
        }

        // Log
        logger()->debug('Handling event on {class} with data {event}.', [
            'class' => static::class,
            'event-data' => $event->data,
        ]);

        // Get payload
        $payload = object_get($event, 'data.object');
        logger()->debug('Event payload: {payload}', compact('payload'));

        $stripeObject = Util::convertToStripeObject($payload, []);
        if (! $stripeObject instanceof StripeObject) {
            $stripeObject = null;
        }

        // Call process if it exists
        if (! \method_exists($this, 'process')) {
            return;
        }

        $this->process($stripeObject);
    }
}
