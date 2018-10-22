<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\User;

/**
 * A new file, which also checks the uploaded mime
 */
class NewFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'file' => 'required|file|mimes:pdf,doc,docx,odt'
        ]);
    }
}
