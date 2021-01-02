<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\Facades\FormBuilder;
use Kris\LaravelFormBuilder\Form;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Handles forms on activities
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FormController extends Controller
{
    use HasEnrollments;

    /**
     * Shows the Activity's from
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Retrieve form
        $form = $this->getForm($activity, [
            'method' => 'PATCH',
            'route' => route('enroll.edit', compact('activity')),
            'model' => $enrollment->form,
        ]);

        // Skip if empty
        if (!$form) {
            throw new BadRequestHttpException('Why are you seeing this?');
        }

        // TODO
        abort(501, 'Not yet supported');
    }

    /**
     * Stores changes to the activity
     *
     * @param  Request  $request
     * @return Response
     */
    public function save(Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Get form
        $form = $this->getForm($activity);

        // Validate form
        $form->redirectIfNotValid(route('enroll.show', compact('activity')));

        // Store data
        $enrollment->form = $form->getFieldValues();
    }

    /**
     * Returns form for this activity
     *
     * @param Activity $activity
     * @param array $options
     * @return Kris\LaravelFormBuilder\Form
     */
    protected function getForm(Activity $activity, array $options = []): ?Form
    {
        // Get data and empty form
        $formdata = $activity->form;

        // Return no form if no form is set
        if (empty($formdata)) {
            return null;
        }

        $formFields = $activity->form;
        $formFields[] = [
            'type' => 'submit',
            'value' => 'Versturen',
        ];

        // Build form
        $form = FormBuilder::createByArray($formFields, $options);

        // Get form
        \assert($form instanceof Form);

        // Return
        return $form;
    }
}
