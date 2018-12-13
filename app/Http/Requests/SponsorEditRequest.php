<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A request to add or edit a sponsor
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class SponsorEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        return $user && $user->hasPermissionTo(['sponsor-edit']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Required meta
            'name' => 'required|string|between:1,30',
            'url' => 'required|url',

            // Optional meta
            'description' => 'nullable|string|between:2,255',
            'action' => 'nullable|string|between:1,25',

            // End date
            'classic' => 'nullable|boolean',

            // Display dates
            'starts_at' => 'nullable|date_format:d-m-Y',
            'ends_at' => 'nullable|date_format:d-m-Y',

            // Uploads
            'image' => [
                'nullable',
                'mimes:jpeg,png,svg',
                Rule::dimensions()
                    ->minHeight(256)
                    ->ratio(4 / 1),
            ],
            'logo' => [
                'nullable',
                'mimes:png,svg',
                Rule::dimensions()
                    ->height(60)
                    ->minWidth(60)
                    ->maxWidth(180)
            ],
        ];
    }
}
