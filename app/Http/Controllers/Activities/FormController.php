<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Contracts\EnrollmentServiceContract;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kris\LaravelFormBuilder\Facades\FormBuilder;
use Kris\LaravelFormBuilder\Form;

/**
 * Handles forms on activities
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FormController extends Controller
{
    use HasEnrollments;

    private const MEDICAL_LABELS = [
        'huisarts',
        'nood',
        'allergieen',
        'allergie',
        'medic',
        'allergy',
        'allergies',
        'emergency'
    ];

    /**
     * Shows the Activity's from
     * @return \Illuminate\Http\Response
     */
    public function show(EnrollmentServiceContract $enrollService, Request $request, Activity $activity)
    {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Retrieve form
        $form = $this->getForm($activity, [
            'method' => 'PATCH',
            'route' => route('enroll.edit', compact('activity')),
            'model' => $enrollment->form
        ]);

        // Skip if empty
        if (!$form) {
            // Forward
            $enrollService->advanceEnrollment($activity, $enrollment);

            // Re-check state
            if ($enrollment->wanted_state instanceof Seeded) {
                // Report
                Log::error("Failed to advance enrollment {enrollment}", compact('enrollment'));

                // Flash
                \flash('Sorry, er is even iets mis met je aanmelding, probeer het later nogmaals', 'warning');

                // Redirect
                return \response()
                    ->redirectToRoute('activity.show', compact('activity'));
            }

            // State updated, redirect to tunnel
            return \response()
                ->redirectToRoute('enroll.show', compact('activity'));
        }

        // Get data about form
        $isMedical = $this->formIsMedical($activity);

        // Render view
        return \response()
            ->view('activities.enrollments.form', compact(
                'form',
                'isMedical'
            ));
    }

    /**
     * Stores changes to the activity
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

        // Add submit
        $formFields[] = [
            'type' => 'submit',
            'value' => 'Versturen'
        ];

        // Build form
        $form = FormBuilder::createByArray($formFields, $options);

        // Get form
        \assert($form instanceof Form);

        // Return
        return $form;
    }

    /**
     * Returns true if this form contains medical data
     * @param Activity $activity
     * @return bool
     */
    private function formIsMedical(Activity $activity): bool
    {
        foreach ($activity->form as $formField) {
            // Get some fields
            $match = implode(" ", [
                Arr::get($formField, 'label'),
                Arr::get($formField, 'name'),
                Arr::get($formField, 'help'),
            ]);

            // Convert to simple
            $match = Str::lower(Str::ascii($match, 'nl'));

            // Test against list
            if (Str::contains($match, self::MEDICAL_LABELS)) {
                return true;
            }
        }

        return false;
    }
}
