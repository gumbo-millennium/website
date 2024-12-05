<?php

declare(strict_types=1);

namespace App\Jobs\Exports;

use App\Models\ModelExport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelPdf\Enums\Orientation;

abstract class ExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Helper method to get the ModelExport for a model.
     * @param Model $model Model to fetch
     * @param User $user User acting upon (only if new)
     * @return ModelExport found or created export model
     */
    protected function findModelExport(Model $model, string $job, User $user): ModelExport
    {
        $model = ModelExport::forModel($model)->firstOrNew();
        if ($model->exists) {
            return $model;
        }

        $model->job = static::class;
        $model->user()->associate($user);
        $model->save();

        return $model;
    }

    /**
     * Use the given view with args to render a PDF and write it out to the
     * ModelExport.
     */
    protected function writeToPdf(string $view, array $args, ModelExport $model): void
    {
        $targetFile = tempnam(sys_get_temp_dir(), 'export');

        try {
            Pdf::view($view, $args)
                ->orentation(Orientation::Portrait)
                ->save($targetFile);

            $model->saveFile(new File($targetFile));
        } finally {
            @unlink($targetFile);
        }
    }
}
