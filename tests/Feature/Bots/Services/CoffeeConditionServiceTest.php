<?php

declare(strict_types=1);

namespace Tests\Feature\Bots\Services;

use App\Bots\Services\CoffeeConditionService;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoffeeConditionServiceTest extends TestCase
{
    /**
     * @before
     */
    public function setupEnvironmentBeforehand(): void
    {
        $this->afterApplicationCreated(function () {
            Storage::fake();
            Date::setTestNow('2022-01-01T07:00:00+01:00');
        });
    }

    /**
     * Test reading without a file.
     */
    public function test_read_empty(): void
    {
        $service = $this->getService();

        $this->assertEquals(__('The coffee condition is unknown.'), $service->getCoffeeCondition());
    }

    /**
     * Test status is written properly.
     */
    public function test_writing(): void
    {
        $service = $this->getService();
        $user = $this->getMemberUser();

        $translationArgs = [
            'user' => $user->first_name,
            'ago' => Date::now()->diffForHumans(),
        ];

        $result = $service->setCoffee($user, true);
        $this->assertStringContainsString(__('Thank you for brewing coffee :name!', ['name' => $user->first_name]), $result);

        $condition = $service->getCoffeeCondition();
        $this->assertStringContainsString(__('The coffee is :condition.', ['condition' => __('very fresh')]), $condition);
        $this->assertStringContainsString(__('The coffee was brewed by :user :ago.', $translationArgs), $condition);

        $result = $service->setCoffee($user, false);
        $this->assertStringContainsString(__('Thank you for letting us know there is no more coffee :name!', ['name' => $user->first_name]), $result);

        $condition = $service->getCoffeeCondition();
        $this->assertStringContainsString(__('There is no more coffee.'), $condition);
        $this->assertStringContainsString(__('The state was last updated by :user :ago.', $translationArgs), $condition);
    }

    /**
     * Test coffee quality is only preserved for the same day.
     */
    public function test_expiration(): void
    {
        $service = $this->getService();

        // Day 1
        $service->setCoffee($this->getMemberUser(), true);
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('very fresh')]),
            $service->getCoffeeCondition(),
        );

        // The next day
        $this->travel(1)->days();
        $this->assertStringContainsString(
            __('The coffee condition is unknown.'),
            $service->getCoffeeCondition(),
        );
    }

    /**
     * Test coffee quality decays over time.
     */
    public function test_decay(): void
    {
        $service = $this->getService();

        // 07:00
        $service->setCoffee($this->getMemberUser(), true);
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('very fresh')]),
            $service->getCoffeeCondition(),
        );

        // 07:30 (00:30)
        $this->travel(30)->minutes();
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('fresh')]),
            $service->getCoffeeCondition(),
        );

        // 08:30 (01:30)
        $this->travel(1)->hour();
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('okay')]),
            $service->getCoffeeCondition(),
        );

        // 10:30 (03:30)
        $this->travel(2)->hours();
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('stale')]),
            $service->getCoffeeCondition(),
        );

        // 12:30 (05:30)
        $this->travel(2)->hours();
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('old')]),
            $service->getCoffeeCondition(),
        );

        // 17:30 (10:30)
        $this->travel(5)->hours();
        $this->assertStringContainsString(
            __('The coffee is :condition.', ['condition' => __('yeasty')]),
            $service->getCoffeeCondition(),
        );
    }

    private function getService(): CoffeeConditionService
    {
        return $this->app->make(CoffeeConditionService::class);
    }
}
