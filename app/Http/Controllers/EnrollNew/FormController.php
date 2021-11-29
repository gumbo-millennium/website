<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Facades\Enroll;
use App\Forms\ActivityForm;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Models\Activity;
use App\Models\States\Enrollment as States;
use App\Models\States\Enrollment\Seeded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Kris\LaravelFormBuilder\Facades\FormBuilder;
use Kris\LaravelFormBuilder\Form;

class FormController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
        ]);
    }

    /**
     * Render the form for this form.
     * @return HttpResponse|RedirectResponse
     */
    public function edit(Request $request, Activity $activity)
    {
        // User not enrolled, fail
        $enrollment = Enroll::getEnrollment($activity);
        abort_unless($enrollment, HttpResponse::HTTP_BAD_REQUEST);

        // No form for this activity, redirect to show page
        if (! $activity->form) {
            // Transition if required
            if ($enrollment->state instanceof States\Created) {
                $enrollment->transitionTo(States\Seeded::class);
                $enrollment->save();
            }

            // Redirect to index
            return Response::redirectToRoute('enroll.show', [$activity]);
        }

        // Preserve session
        $request->session()->reflash();

        // Build the real form
        $form = $this->getForm($activity, [
            'method' => 'PATCH',
            'url' => route('enroll.formStore', [$activity]),
            'data' => $enrollment->form_data,
        ]);

        // Epicly fail if empty
        abort_unless($form, HttpResponse::HTTP_INTERNAL_SERVER_ERROR, __(
            'Could not construct a form from the given data...',
        ));

        // Render the form
        return Response::view('enrollments.form', [
            'activity' => $activity,
            'enrollment' => $enrollment,
            'form' => $form,
            'submitted' => ! empty($enrollment->form),
        ]);
    }

    /**
     * Save the form (or form changes), advance the state and
     * redirect to the show route, which figures out where to redirect.
     */
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $enrollment = Enroll::getEnrollment($activity);
        abort_unless($enrollment, HttpResponse::HTTP_BAD_REQUEST);
        abort_unless($activity->form, HttpResponse::HTTP_BAD_REQUEST);

        // Get form
        $form = $this->getForm($activity);

        // Validate form
        $form->redirectIfNotValid(route('enroll.form', [$activity]));

        // Assign values
        $enrollment->setFormData($form->getFieldValues());

        // Advance stage to seeded, if not yet seeded
        if ($enrollment->canTransitionTo(States\Seeded::class)) {
            $enrollment->transitionTo(States\Seeded::class);
        }

        // Store changes
        $enrollment->save();

        // Redirect to show, that'll determine the next target
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
