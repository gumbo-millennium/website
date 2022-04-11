<?php

declare(strict_types=1);

namespace Tests\Feature\Nova;

use App\Helpers\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use LogicException;
use Tests\Fixtures\Nova\ActionResponse;
use Tests\TestCase;

abstract class NovaTestCase extends TestCase
{
    /**
     * Runs a Laravel Nova action on the given action.
     */
    protected function callActionOnModel(Action $action, Model $model, array $fields = []): ActionResponse
    {
        // if (is_string($action)) {
        //     $action = $this->findActionForNameOnModel($action, $model);
        // }

        throw_unless(method_exists($action, 'handle'), LogicException::class, sprintf(
            'Failed asserting handle method exists on action %s.',
            class_basename($action),
        ));

        $actionFields = new ActionFields(
            Collection::make($fields),
            Collection::make(),
        );

        $result = App::call([$action, 'handle'], [
            'fields' => $actionFields,
            'models' => Collection::make([$model]),
        ]);

        return new ActionResponse($result);
    }

    /**
     * Finds the action on the given model with the given class name or URI key.
     * Throws LogicExceptions in case it's not found, since something got broken by the user.
     *
     * @throws LogicException in case action cannot be find for a reason
     */
    private function findActionForNameOnModel(string $actionName, Model $model): Action
    {
        $modelResourceName = Nova::resourceForModel($model);
        throw_unless($modelResourceName, LogicException::class, 'Could not find a resource for the given model.');

        $modelResource = new $modelResourceName($model);
        assert($modelResource instanceof Resource);

        $actions = $modelResource->actions(FacadesRequest::instance());
        $action = Arr::first($actions, fn (Action $action) => is_a($action, $actionName, true) || $action->uriKey() === $actionName);
        throw_unless($action, LogicException::class, 'Could not find a matching action for the name.');

        return $action;
    }
}
