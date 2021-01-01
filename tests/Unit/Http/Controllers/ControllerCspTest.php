<?php

namespace Tests\Unit\Http\Controllers;

use Spatie\Csp\Policies\Policy;
use Tests\Fixtures\Http\Controllers\Controller;
use Tests\TestCase;

class ControllerCspTest extends TestCase
{
    /**
     * Overriding only works if the container will start returning the same
     * instance of the Policy, so we make sure it does.
     */
    public function testPolicyStaysTheSame()
    {
        $controller = new Controller();

        $first = $controller->alterCspPolicy();
        $second = $controller->alterCspPolicy();

        $this->assertInstanceOf(Policy::class, $first);
        $this->assertSame($first, $second);
    }

    public function testImageWhitelisting()
    {
        $controller = new Controller();

        $policy = $controller->alterCspPolicy();
        assert($policy instanceof Policy);

        $controller->addImageUrlsToCspPolicy([
            'https://imgur.com/logo.png',
            '//example.com',
            'ftp://code.google.com/my-images/hello-world.png',
        ]);

        $policyText = (string) $policy;
        $this->assertStringContainsString('https://imgur.com', $policyText);
        $this->assertStringContainsString('ftp://code.google.com', $policyText);
        $this->assertStringNotContainsString('example.com', $policyText);
    }
}
