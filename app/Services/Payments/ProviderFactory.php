<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\PaymentProvider;
use App\Models\Enrollment;
use App\Models\Invoice;
use Cache;
use Illuminate\Support\Facades\App;
use LogicException;
use UnderflowException;

final class ProviderFactory
{
    /**
     * Registered providers
     * @var array<string>
     */
    private array $providers;

    /**
     * Default provider
     * @var string
     */
    private string $default;

    /**
     * Created instances of providers
     * @var array<PaymentProvider>
     */
    private array $providerInstances = [];

    /**
     * Creates a new factory, with the given providers and the given default provider
     * @param array $providers
     * @param string $default
     * @return void
     * @throws LogicException
     */
    public function __construct(array $providers, string $default)
    {
        $checkedProviders = [];
        foreach ($providers as $name => $provider) {
            if (!\is_string($name) || !\is_a($provider, PaymentProvider::class, true)) {
                throw new LogicException(
                    "Attempted to register provider {$name} of type {$provider}, but that's not allowed"
                );
            }

            $checkedProviders[$name] = $provider;
        }

        if (!\array_key_exists($default, $checkedProviders)) {
            throw new LogicException(
                "Attempted to use default provider {$default}, but it's not registered"
            );
        }

        $this->default = $default;
        $this->providers = $checkedProviders;
    }

    /**
     * Returns a mapping of name => instance
     * @return array<PaymentProvider>
     */
    public function getProviders(): array
    {
        // We only loop the keys, getProviderInstance returns the class
        foreach (\array_keys($this->providers) as $provider) {
            $out[$provider] = $this->getProviderInstance($provider);
        }

        // Done
        return $out;
    }

    /**
     * Returns the default provider
     * @return PaymentProvider
     */
    public function getDefaultProvider(): PaymentProvider
    {
        return $this->getProviderInstance($this->default);
    }

    /**
     * Returns the payment provider responsible for this enrollment
     * @param Enrollment $enrollment
     * @return PaymentProvider
     * @throws UnderflowException If the provider for the enrollment is not available
     */
    public function getProvider(Enrollment $enrollment): PaymentProvider
    {
        // Make cache key
        $cacheKey = "payments.providers.for-enrollment.{$enrollment->id}";
        $cacheDuration = \now()->addWeeks(2);

        // Check cache first
        if (Cache::has($cacheKey)) {
            return $this->getProviderInstance(Cache::get($cacheKey));
        }

        // Quickly check for legacy
        if (
            !empty($this->payment_intent) ||
            !empty($this->payment_source) ||
            !empty($this->payment_invoice)
        ) {
            Cache::put($cacheKey, 'stripe', $cacheDuration);
            return $this->getProviderInstance('stripe');
        }

        // Check existing invoices
        $provider = Invoice::query()
            ->whereEnrollmentId($enrollment->id)
            ->pluck('provider')
            ->first();

        // Return the provider if it's set
        if (!empty($provider)) {
            Cache::put($cacheKey, $provider, $cacheDuration);
            return $this->getProviderInstance($provider);
        }

        // Return the default provider
        return $this->getProviderInstance($this->default);
    }

    /**
     * Returns the provider instance for the given name, created only once.
     * @param mixed $name
     * @return PaymentProvider
     * @throws UnderflowException If a provider named $name is not registered
     */
    private function getProviderInstance($name): PaymentProvider
    {
        // Fail if unknown
        if (!isset($this->providers[$name])) {
            throw new UnderflowException("Attepted to load provider {$name}, but it's not registered");
        }

        // Create new if missing
        if (!isset($this->providerInstances[$name])) {
            $this->providerInstances[$name] = App::make($this->providers[$name]);
        }

        // Return result
        return $this->providerInstances[$name];
    }
}
