<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\User;
use App\JoinRequest;

class JoinChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(JoinRequest $joinRequest)
    {
        $user = $this->user();
        if (!$user || !$user) {
            return false;
        }

        $permissions = ['join.update'];
        $status = $request->get('status');

        // Check if setting the action to the given value is allowed.
        // Permission required for setting AND unsetting the joinRequest.
        if ($status === 'accepted' || $joinRequest->status === 'accepted') {
            $permissions[] = 'join.accept';
        }
        if ($status === 'declined' || $joinRequest->status === 'declined') {
            $permissions[] = 'join.decline';
        }

        // Check if they're allowed
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'accepted',
                    'declined'
                ])
            ]
        ];
    }
}
