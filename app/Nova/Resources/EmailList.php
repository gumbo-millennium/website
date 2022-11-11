<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields;

class EmailList extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\EmailList';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group.
     *
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'email',
    ];

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function fields(Request $request)
    {
        return [
            Fields\ID::make()->sortable(),

            Fields\Text::make('Lijst naam', 'name')
                ->readonly()
                ->exceptOnForms(),

            Fields\Text::make('E-mailadres', 'email')
                ->readonly()
                ->exceptOnForms(),

            Fields\Text::make('Service ID', 'service_id')
                ->readonly(),

            Fields\Markdown::make('Aliassen', 'aliasses')
                ->resolveUsing(static function ($values) {
                    if (empty($values)) {
                        return null;
                    }

                    return array_map(static fn ($val) => "* ${val}\n", $values);
                })
                ->readonly()
                ->onlyOnDetail(),

            Fields\Textarea::make('Accounts', 'members')
                ->resolveUsing(static function ($values) {
                    if (empty($values)) {
                        return null;
                    }

                    return \implode("\n", \array_map(
                        static fn ($val) => "- {$val['email']} ({$val['role']})",
                        $values,
                    ));
                })
                ->readonly()
                ->onlyOnDetail()
                ->alwaysShow(),
        ];
    }
}
