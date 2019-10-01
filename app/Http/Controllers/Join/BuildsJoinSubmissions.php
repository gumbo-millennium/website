<?php

namespace App\Http\Controllers\Join;

use App\Http\Requests\JoinRequest;
use App\Models\JoinSubmission;
use Illuminate\Http\Request;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

/**
 * Handles sending join requests
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait BuildsJoinSubmissions
{
    /**
     * The phone normalisation region
     *
     * @var string
     */
    protected $phoneRegion = 'NL';

    /**
     * Creates the request to join
     *
     * @param JoinRequest $request
     * @return void
     */
    protected function buildJoinSubmission(Request $request): ?JoinSubmission
    {
        $data = collect([
            // Personal data
            'first_name' => $request->get('first_name'),
            'insert' => $request->get('insert'),
            'last_name' => $request->get('last_name', $request->get('name')),

            // E-mail
            'email' => $request->get('email'),

            // Address
            'street' => $request->get('street'),
            'number' => $request->get('number'),
            'postal_code' => mb_strtoupper($request->get('zipcode')),
            'city' => $request->get('city'),
            'country' => $request->get('country'),

            // Contact info
            'phone' => $request->get('phone'),
            'date_of_birth' => $request->get('date_of_birth'),
            'gender' => $request->get('gender'),
            'windesheim_student' => $request->get('windesheim_student'),

            // Accepts policies
            'accept_policy' => $request->get('accept_policy'),
            'newsletter' => $request->get('accept_newsletter'),
        ]);

        // Format phone number, if possible
        if (!$data->has('phone') && !empty($data->phone)) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                // Get number
                $phoneNumber = $phoneUtil->parse($data->phone, $this->phoneRegion);

                // Format for local use if country is Netherlands or missing
                if (!$phoneNumber->hasCountryCode() || $phoneNumber->getCountryCode() === 31) {
                    $data->phone = $phoneUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL);
                }
            } catch (NumberParseException $e) {
                return false;
            }
        }

        // Add to Laravel Nova
        $submission = JoinSubmission::make($data->only([
            'first_name',
            'insert',
            'last_name',
            'phone',
            'email',
            'date_of_birth',
            'gender',
            'street',
            'number',
            'city',
            'postal_code',
            'country',
            'windesheim_student',
            'newsletter',
        ])->toArray());

        try {
            $submission->save();
            return $submission;
        } catch (\Exception $e) {
            logger()->alert("Failed to store join request!", [
                'exception' => $e,
                'submission' => $submission,
            ]);
            throw $e;
            return null;
        }
    }
}
