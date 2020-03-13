<?php

declare(strict_types=1);

namespace App\Nova\Actions\Traits;

use Illuminate\Http\Request;

trait BlocksCancelledActivityRuns
{
    /**
     * Determine if the action is executable for the given request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        return
            !$model->is_cancelled &&
            $model->end_date > now() &&
            parent::authorizedToRun($request, $model);
    }
}
