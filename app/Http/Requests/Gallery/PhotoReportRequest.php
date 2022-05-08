<?php

declare(strict_types=1);

namespace App\Http\Requests\Gallery;

/**
 * @property string $reason
 */
class PhotoReportRequest extends PhotoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'reason-text' => [
                'required_when:reason,other',
                'string',
                'max:255',
            ],
        ];
    }
}
