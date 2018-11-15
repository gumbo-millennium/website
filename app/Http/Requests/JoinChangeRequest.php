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

        if ($request->status === 'accepted') {
            return $user->can('accept', $joinRequest);
        } elseif ($request->status === 'declined') {
            return $user->can('decline', $joinRequest);
        } else {
            return $user->can('update', $joinRequest);
        }
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
