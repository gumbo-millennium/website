<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class EmailList extends Resource
{
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = 'App\Models\EmailList';

    /**
     * The single value that should be used to represent the resource when being displayed.
     * @var string
     */
    public static $title = 'name';

    /**
     * Name of the group
     * @var string
     */
    public static $group = 'Bestuurszaken';

    /**
     * The columns that should be searched.
     * @var array
     */
    public static $search = [
        'email',
    ];

    /**
     * Get the displayable label of the resource.
     * @return string
     */
    public static function label()
    {
        return 'E-maillijsten';
    }

    /**
     * Get the displayable singular label of the resource.
     * @return string
     */
    public static function singularLabel()
    {
        return 'E-maillijst';
    }

    /**
     * Get the fields displayed by the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Lijst naam', 'name')
                ->readonly()
                ->exceptOnForms(),

            Text::make('E-mailadres', 'email')
            ->readonly()
                ->exceptOnForms(),

            Text::make('Service ID', 'service_id')
                ->readonly(),

            Markdown::make('Aliassen', 'aliasses')
                ->resolveUsing(static function ($values) {
                    if (empty($values)) {
                        return null;
                    }
                    return array_map(static fn ($val) => "* $val\n", $values);
                })
                ->readonly()
                ->onlyOnDetail(),

            Textarea::make('Accounts', 'members')
                ->resolveUsing(static function ($values) {
                    if (empty($values)) {
                        return null;
                    }
                    return \implode("\n", \array_map(
                        static fn ($val) => "- {$val['email']} ({$val['role']})",
                        $values
                    ));
                })
                ->readonly()
                ->onlyOnDetail()
                ->alwaysShow(),
        ];
    }
}
