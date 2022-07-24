<?php

declare(strict_types=1);

namespace Tests\Feature\Nova;

use App\Nova\Resources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Nova\Resource;
use Tests\TestCase;

class ResourceBaselineTest extends TestCase
{
    /**
     * Tests listing, viewing create, viewing.
     *
     * @dataProvider resourceProvider
     */
    public function test_resource(string $resourceClass): void
    {
        $this->assertTrue(is_a($resourceClass, Resource::class, true), 'Requested resource is not a Nova resource');

        $modelClass = $resourceClass::$model;
        $this->assertTrue(is_a($modelClass, Model::class, true), 'Resource model is not a model');

        /** @var Model $model */
        $model = $modelClass::factory()->create();
        $this->assertInstanceOf($modelClass, $model, 'Model class is not the same as the class passed to the test');

        $resource = new $resourceClass($model);
        assert($resource instanceof \Laravel\Nova\Resource);

        $uriKey = call_user_func([get_class($resource), 'uriKey']);
        $routeKey = $model->getKey();

        $this->actingAs($this->getSuperAdminUser());

        $this->get("/nova-api/{$uriKey}/creation-fields")
            ->assertOk();

        $this->get("/nova-api/{$uriKey}/{$routeKey}/update-fields")
            ->assertOk();
    }

    public function resourceProvider(): array
    {
        $models = [
            Resources\Activity::class,
            Resources\EmailList::class,
            Resources\FileBundle::class,
            Resources\FileBundle::class,
            Resources\JoinSubmission::class,
            Resources\JoinSubmission::class,
            Resources\Page::class,
            Resources\RedirectInstruction::class,
            Resources\User::class,
            Resources\Webcam\Camera::class,
            Resources\Webcam\Device::class,
        ];

        return Collection::make($models)
            ->mapWithKeys(fn ($row) => [class_basename($row) => [$row]])
            ->all();
    }
}
