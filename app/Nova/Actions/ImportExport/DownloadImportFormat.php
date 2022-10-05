<?php

declare(strict_types=1);

namespace App\Nova\Actions\ImportExport;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @method static self make(string $format)
 */
class DownloadImportFormat extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private string $format)
    {
        $this
            ->standalone()
            ->withoutConfirmation();
    }

    public function name()
    {
        return __('Download import format');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        return Action::download(route('admin.import.template', $this->format), 'Import template.ods');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
