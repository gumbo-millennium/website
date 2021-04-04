<?php

declare(strict_types=1);

namespace App\Forms;

use App\Models\Activity;
use App\Models\FormLayout;
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
     * @return Activity
     * @throws RuntimeException
     */
    public function getActivity(): Activity
    {
        $activity = $this->formOptions['activity'] ?? null;

        if (!$activity || !$activity instanceof Activity) {
            throw new RuntimeException('Activity is not set or invalid');
        }

        return $activity;
    }

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $activityForm = $this->getActivity()->form ?? [];

        foreach ($activityForm as $formField) {
            assert($formField instanceof FormLayout);

            $this->add(
                $formField->getName(),
                $formField->getType(),
                $formField->getOptions()
            );
        }

        // Always add a submit button
        $this
            ->add('submit', 'submit', [
                'label' => __('Continue'),
            ]);
    }
}
