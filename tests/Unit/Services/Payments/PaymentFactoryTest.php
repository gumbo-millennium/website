<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Helpers\Str;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Services\Payments\MolliePaymentService;
use App\Services\Payments\ProviderFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use LogicException;
use Tests\Stubs\DummyPaymentProvider;
use Tests\TestCase;
use Tests\Traits\UsesFakeEnrollments;

class PaymentFactoryTest extends TestCase
{
    use UsesFakeEnrollments;

    /**
     * Test default function
     * @return void
     */
    public function testDefaultProvider()
    {
        // Set config
        $this->setFactoryConfig();

        // Get factory
        $factory = App::make(ProviderFactory::class);
        \assert($factory instanceof ProviderFactory);

        // Test creation
        $instance = $factory->getDefaultProvider();
        $this->assertInstanceOf(DummyPaymentProvider::class, $instance);

        // Test instance persistence
        $instance2 = $factory->getDefaultProvider();
        $this->assertSame($instance, $instance2);

        // Test method
        $enroll = new Enrollment();
        $instance3 = $factory->getProvider($enroll);
        $this->assertSame($instance, $instance3);

        // Test providers list
        $providers = $factory->getProviders();
        $this->assertArrayHasKey('test', $providers);
        $this->assertSame($instance, $providers['test']);
    }

    /**
     * Test with a dumb default
     * @return void
     * @throws BindingResolutionException
     */
    public function testInvalidDefaultConfig()
    {
        // Set config
        $this->setFactoryConfig(null, 'not-found');

        // Configure assertion
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('default');

        // Get factory
        App::make(ProviderFactory::class);
    }

    /**
     * Test with a provider that's not, in fact, a provider
     */
    public function testInvalidProvider()
    {
        // Set config
        $this->setFactoryConfig(['invoice-model' => Invoice::class]);

        // Configure assertion
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Attempted to register provider invoice-model of type App\\Models\\Invoice');

        // Get factory
        App::make(ProviderFactory::class);
    }

    /**
     * Returns safe invoice ID
     * @return string
     */
    private function random(): string
    {
        return 'test_' . Str::random(16);
    }

    /**
     * Tests if a Stripe invoice causes the factory to create a Stripe service
     * @return void
     */
    public function testEnrollmentDetection()
    {
        // Set config
        $this->setFactoryConfig(null, 'test');

        // Get enrollment
        $enrollmentDefault = $this->createPaidEnrollment();
        $enrollmentMollie = $this->createPaidEnrollment();

        // Add Stripe invoice
        Invoice::createSupplied('mollie', $this->random(), $enrollmentMollie);

        // Get factory
        $factory = App::make(ProviderFactory::class);
        \assert($factory instanceof ProviderFactory);

        // Validate defautl returns the test provider
        $result = $factory->getProvider($enrollmentDefault);
        $this->assertNotInstanceOf(MolliePaymentService::class, $result);

        // Run checks
        $result = $factory->getProvider($enrollmentMollie);
        $this->assertInstanceOf(MolliePaymentService::class, $result);
    }

    private function setFactoryConfig(?array $factories = null, ?string $default = null): void
    {
        $factories ??= [
            'test' => DummyPaymentProvider::class,
            'mollie' => MolliePaymentService::class,
        ];
        $default ??= 'test';

        Config::set([
            'services.payments.providers' => $factories,
            'services.payments.default-provider' => $default,
        ]);
    }
}
