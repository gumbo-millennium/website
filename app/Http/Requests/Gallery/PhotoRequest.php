<?php

declare(strict_types=1);

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @var null|\App\Models\Gallery\Photo $photo
 */
abstract class PhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        $photo = $this->photo;

        return $user && $photo && $user->can('view', $photo);
    }

    // No rules, childs can determine rules.
}
