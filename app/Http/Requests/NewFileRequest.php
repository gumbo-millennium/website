<?php

namespace App\Http\Requests;

use App\Http\Requests\FileRequest;
use App\User;

/**
 * A new file, which also checks the uploaded mime
 */
class NewFileRequest extends FileRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'file' => 'required|file|mimes:pdf'
        ]);
    }
}
