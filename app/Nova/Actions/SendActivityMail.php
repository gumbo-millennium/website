<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\SendActivityMessageJob;
use App\Models\ActivityMessage;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\Markdown as MarkdownField;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class SendActivityMail extends Action
{
    use BlocksCancelledActivityRuns;

    public function __construct()
    {
        $this
            // The 'confirmation' is the body
            ->confirmText(
                implode(PHP_EOL, [
                    __('Please specify the target audience, the message title and the message body.'),
                    __('Optionally, you can specify when the message should be sent, and to which tickets.'),
                ]),
            )

            // The buttons
            ->confirmButtonText(__('Send message'))
            ->cancelButtonText(__('Cancel'))

            // And make sure it's not chainable
            ->onlyOnDetail();
    }

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Send mail to participants');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $audience = $fields->get('audience');
        $subject = $fields->get('title');
        $body = $fields->get('body');

        foreach ($models as $activity) {
            $message = new ActivityMessage([
                'target_audience' => $audience,
                'subject' => $subject,
                'body' => $body,
            ]);

            $message->activity()->associate($activity);
            $message->sender()->associate(Auth::user());

            $message->save();

            SendActivityMessageJob::dispatch($message);
        }

        return $this->message(__('The message has been scheduled.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__('Target audience'), 'audience')
                ->options(
                    collect(ActivityMessage::VALID_AUDIENCES)->mapWithKeys(static fn ($val) => [
                        $val => __("gumbo.target-audiences.{$val}"),
                    ])->toArray(),
                )
                ->rules([
                    'required',
                    Rule::in(ActivityMessage::VALID_AUDIENCES),
                ]),

            Text::make(__('Mail title'), 'title')
                ->rules([
                    'required',
                    'string',
                    'between:5,70',
                ]),

            MarkdownField::make(__('Mail body'), 'body')
                ->help(__('Body of the mail, basic markdown formatting is supported.'))
                ->rules([
                    'required',
                ]),
        ];
    }
}
