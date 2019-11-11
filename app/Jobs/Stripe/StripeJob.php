<?php

namespace App\Jobs\Stripe;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event as StripeEvent;

/**
 * Basic Stripe job, with a webhook
 */
abstract class StripeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Provided webhook
     *
     * @var WebhookCall
     */
    protected $webhook;

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
     *
     * @return void
     */
    final public function handle(): void
    {
        // Get event
        $event = StripeEvent::constructFrom($this->webhook->payload);

        // Ensure that the application is in the same mode as the source of the event.
        // This ensures that test data is never read by systems in production
        if ($event->livemode !== (bool) config('stripe.test_mode', false)) {
            abort(403, "Event's origin mode is mismatching with the website mode.");
        }

        // Assign stripe event
        app()->call(
            [$this, 'process'],
            [$event]
        );
    }

    /**
     * Actually execute the job
     *
     * @param StripeEvent $event
     * @return void
     */
    abstract public function process(StripeEvent $event): void;
}
