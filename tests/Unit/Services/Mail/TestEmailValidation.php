<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Mail;

use App\Services\Mail\Traits\ValidatesEmailRequests;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TestEmailValidation extends TestCase
{
    /**
     * Returns the trait
     * @return MockObject|ValidatesEmailRequests
     */
    public function getValidationMock()
    {
        // Be predictable
        Config::set('services.google.domains', [
            'example.com',
            'example.eu'
        ]);

        // Mock
        return $this->getMockForTrait(ValidatesEmailRequests::class);
    }

    /**
     * Tests if mutating emails restricts to non-org emails
     * @return void
     */
    public function testAllowEmailMutation(): void
    {
        // Get mock
        $mock = $this->getValidationMock();

        // Validate basic
        $this->assertFalse($mock->canMutate('media@example.eu'));
        $this->assertFalse($mock->canMutate('sales@foo.example.eu'));
        $this->assertTrue($mock->canMutate('info@preview.co'));
        $this->assertTrue($mock->canMutate('sales@foo.example.com.au'));

        // Validate invalid email addresses (should not check)
        $this->assertFalse($mock->canMutate('info@cake@example.com'));
        $this->assertTrue($mock->canMutate('info@example.com@example.net'));
    }

    /**
     * Tests if processing is limited to top-level domains
     * @return void
     */
    public function testProcessList(): void
    {
        // Get mock
        $mock = $this->getValidationMock();

        // Validate basic
        $this->assertTrue($mock->canProcessList('media@example.com'));
        $this->assertFalse($mock->canProcessList('sales@foo.example.com'));
        $this->assertFalse($mock->canProcessList('sales@foo.example.com.au'));
        $this->assertFalse($mock->canProcessList('sales@foo.bar'));

        // Validate invalid email addresses (should not check)
        $this->assertTrue($mock->canProcessList('info@cake@example.com'));
        $this->assertFalse($mock->canProcessList('info@example.com@example.net'));
    }

    /**
     * Tests if names match the expected form
     * @return void
     */
    public function testValidateName(): void
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        // Get mock
        $mock = $this->getValidationMock();

        // Validate basic
        $this->assertTrue($mock->validateListNameAgainstEmail('Bestuur', 'bestuur@example.com'));
        $this->assertTrue($mock->validateListNameAgainstEmail('Spam box', 'randombox-with-spam-and-such@example.com'));

        // Validate valid committees
        $this->assertTrue($mock->validateListNameAgainstEmail('Activiteiten Commissie', 'ac@example.com'));
        $this->assertTrue($mock->validateListNameAgainstEmail('Lustrum Commissie', 'lucie@example.com'));
        $this->assertTrue($mock->validateListNameAgainstEmail('Commissie - Landhuisweekend', 'lhwcommissie@example.com'));

        // Validate invalid committees
        $this->assertFalse($mock->validateListNameAgainstEmail('Activiteiten Commissie', 'activiteiten@example.com'));
        $this->assertFalse($mock->validateListNameAgainstEmail('Lustrum Commissie', 'lustrum@example.com'));
        $this->assertFalse($mock->validateListNameAgainstEmail('Commissie - Landhuisweekend', 'lhw@example.com'));

        // Validate valid project group
        $this->assertTrue($mock->validateListNameAgainstEmail('Projectgroep - Public Relations', 'pr@example.com'));
        $this->assertTrue($mock->validateListNameAgainstEmail('BBQPG', 'bbq@example.com'));

        // Validate invalid project group
        $this->assertFalse($mock->validateListNameAgainstEmail('Projectgroep - Public Relations', 'prpg@example.com'));
        $this->assertFalse($mock->validateListNameAgainstEmail('BBQPG', 'bbqpg@example.com'));
        // phpcs:enable
    }
}
