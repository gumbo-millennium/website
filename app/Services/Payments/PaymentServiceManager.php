<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentManager;
use App\Contracts\Payments\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\ForwardsCalls;
use LogicException;
use RuntimeException;

class PaymentServiceManager implements PaymentManager
{
    use ForwardsCalls;

    /**
     * @var PaymentService[]
     */
    protected $services = [];

    public static function make(string $default, array $providers): self
    {
        $instance = new self();

        foreach ($providers as $provider) {
            $instance->addService($provider);
        }

        $instance->setDefaultService($default);

        return $instance;
    }

    /**
     * Adds the specified service to the manager, should be the name of the class,
     * not an instance.
     *
     * @throws LogicException If the service isn't a PaymentService class name
     */
    public function addService(string $service): self
    {
        throw_unless(
            is_a($service, PaymentService::class, true),
            new LogicException('Payment service must be a class implementing PaymentService.'),
        );

        $this->services[$service::getName()] = $service;

        $this->defaultService ??= $service::getName();

        return $this;
    }

    /**
     * @throws LogicException if the service isn't registered
     */
    public function setDefaultService($paymentService): self
    {
        if (is_a($paymentService, PaymentService::class, true)) {
            $paymentService = $paymentService::getName();
        }

        if (! array_key_exists($paymentService, $this->services)) {
            throw new LogicException('Payment service does not exist.');
        }

        $this->defaultService = $paymentService;

        return $this;
    }

    /**
     * @throws RuntimeException If the specified service isn't registered
     * @throws BindingResolutionException If the service cannot be instantiated by Laravel
     */
    public function find(string $service): ?PaymentService
    {
        if (! array_key_exists($service, $this->services)) {
            throw new RuntimeException('Payment service does not exist.');
        }

        $service = $this->services[$service];

        if (is_string($service)) {
            $service = App::make($service);
            $this->services[$service::getName()] = $service;
        }

        return $service;
    }

    public function default(): PaymentService
    {
        return $this->find($this->defaultService);
    }

    public function getDefault(): string
    {
        return $this->defaultService;
    }

    public function __call($method, $arguments)
    {
        return $this->forwardCallTo($this->default(), $method, $arguments);
    }
}
