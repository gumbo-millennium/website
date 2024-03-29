<?php

declare(strict_types=1);

namespace App\Forms;

use App\Models\Activity;
use App\Models\FormLayout;
use Illuminate\Support\HtmlString;
use Kris\LaravelFormBuilder\Form;
use RuntimeException;

/**
 * Form shown when users sign up.
 */
class ActivityForm extends Form
{
    /**
     * Returns the activity for this form, throws an error if it's not set.
     *
     * @throws RuntimeException
     */
    public function getActivity(): Activity
    {
        $activity = $this->formOptions['activity'] ?? null;

        if (! $activity || ! $activity instanceof Activity) {
            throw new RuntimeException('Activity is not set or invalid');
        }

        return $activity;
    }

    /**
     * Builds the form.
     */
    public function buildForm()
    {
        $activityForm = $this->getActivity()->form ?? [];

        foreach ($activityForm as $formField) {
            assert($formField instanceof FormLayout);

            $this->add(
                $formField->getName(),
                $formField->getType(),
                array_merge([
                    'value' => $this->getData($formField->getName()),
                ], $formField->getOptions()),
            );
        }

        // Add an "Accept Terms" button
        $this
            ->add('accept-terms', 'checkbox', [
                'label' => __('I accept the privacy policy and understand how my data will be processed.'),
                'rules' => 'required',
                'help_block' => [
                    'text' => new HtmlString(sprintf(
                        '<a href="/privacy-policy" target="_blank">%s</a>',
                        __('Read the Privacy Policy'),
                    )),
                ],
            ]);

        // Always add a submit button
        $this
            ->add('submit', 'submit', [
                'label' => __('Continue'),
            ]);
    }
}
