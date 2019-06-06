<?php

namespace App\Http\Controllers;

use App\Models\JoinSubmission;
use Illuminate\Http\Request;
use App\Http\Requests\JoinSubmitRequest;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class JoinSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Redirect to sign-up form if no user is logged in
        if (!$user) {
            return response()->redirectToRoute('join.form');
        }

        // Get submissions on this e-mail address
        $submissions = JoinSubmission::where('email', $user->email)->get();

        // Render submissions
        return view('join.submitted', compact('submissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('join.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JoinSubmitRequest $request)
    {
        // Get submitted data
        $requestData = collect($request->only([
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
            'country'
        ]));

        // Format email, house number, city and country
        $requestData->email = mb_strtolower($requestData->email);
        $requestData->city = mb_strtoupper($requestData->city);
        $requestData->country = mb_strtoupper($requestData->country);

        // Format phone number
        $phoneUtil = PhoneNumberUtil::getInstance();
        $userNumber = $phoneUtil->parse($requestData->phone, 'NL');
        $requestData->phone = $phoneUtil->format($userNumber, PhoneNumberFormat::INTERNATIONAL);

        // Format house number, separate suffix using dashes
        if (preg_match('/^(\d+)[\s-]*([a-z]+)$/i', $requestData->number, $matches)) {
            $requestData->number = sprintf('%s-%s', $matches[1], mb_strtoupper($matches[2]));
        }

        // Format postal code, if in The Netherlands
        if ($requestData->country === 'NL' &&
            preg_match('/^(\d{4})\s*([A-Z]{2})$/i', $requestData->postal_code, $matches)) {
            $requestData->postal_code = sprintf('%s %s', $matches[1], mb_strtoupper($matches[2]));
        }

        return response()->json($requestData->all());

        // Store data
        $submission = JoinSubmission::create($requestData->only([
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\JoinSubmission  $joinSubmission
     * @return \Illuminate\Http\Response
     */
    public function show(JoinSubmission $joinSubmission)
    {
        if ($user->can(''))
        if ($user->email)
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\JoinSubmission  $joinSubmission
     * @return \Illuminate\Http\Response
     */
    public function edit(JoinSubmission $joinSubmission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\JoinSubmission  $joinSubmission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JoinSubmission $joinSubmission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\JoinSubmission  $joinSubmission
     * @return \Illuminate\Http\Response
     */
    public function destroy(JoinSubmission $joinSubmission)
    {
        //
    }
}
