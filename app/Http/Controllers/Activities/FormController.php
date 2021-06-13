<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Contracts\EnrollmentServiceContract;
use App\Forms\ActivityForm;
use App\Http\Controllers\Activities\Traits\HasEnrollments;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Kris\LaravelFormBuilder\Facades\FormBuilder;
use Kris\LaravelFormBuilder\Form;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Handles forms on activities.
 */
class FormController extends Controller
{
    use HasEnrollments;

    /**
     * Shows the Activity's from.
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
            'url' => route('enroll.edit', compact('activity')),
            'data' => $enrollment->form_data,
        ]);

        // Epicly fail if empty
        throw_unless($form, BadRequestHttpException::class, 'Why are you seeing this?');

        // Render form, but instruct browsers not to cache
        return Response::view('activities.enrollments.form', [
            'activity' => $activity,
            'form' => $form,
            'enrollment' => $enrollment,
        ])->setPrivate()->header('Cache-Control', 'no-cache, no-store');
    }

    /**
     * Stores changes to the activity.
     *
     * @return Response
     */
    public function save(
        Request $request,
        Activity $activity,
        EnrollmentServiceContract $enrollmentService
    ) {
        // Get enrollment
        $enrollment = $this->findActiveEnrollmentOrFail($request, $activity);

        // Get form
        $form = $this->getForm($activity);

        // Validate form
        $form->redirectIfNotValid(route('enroll.show', compact('activity')));

        // Assign values
        $enrollment->setFormData($form->getFieldValues());

        // Store changes
        $enrollment->save();

        // Try to advance, if we're still in that stage.
        if ($enrollmentService->canAdvanceTo($enrollment, Seeded::class)) {
            $enrollmentService->advanceEnrollment($activity, $enrollment);
        }

        // Redirect back if done
        if ($enrollment->state->isStable()) {
            flash("That's it, je bent nu ingeschreven voor {$activity->name}", 'success');

            return Response::redirectToRoute('activity.show', [$activity]);
        }

        // Or to the payment page
        flash('Je gegevens zijn opgeslagen. Je kunt nu betalen.', 'success');

        return Response::redirectToRoute('enroll.show', [$activity]);
    }

    /**
     * Returns form for this activity.
     *
     * @return Form
     */
    protected function getForm(Activity $activity, array $options = []): ?Form
    {
        // Get data and empty form
        $formdata = $activity->form;

        // Return no form if no form is set
        if (empty($formdata)) {
            return null;
        }

        // Prep form via helper
        return FormBuilder::create(ActivityForm::class, array_merge($options, [
            'activity' => $activity,
        ]));
    }
}
