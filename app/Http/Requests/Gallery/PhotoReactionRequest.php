<?php

declare(strict_types=1);

namespace App\Http\Requests\Gallery;

use App\Enums\PhotoReactionType;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * @property PhotoReactionType $reaction
 * @property string $reaction_type
 */
class PhotoReactionRequest extends PhotoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reaction_type' => [
                'required',
                'string',
                Rule::in(array_map(fn (PhotoReactionType $value) => $value->value, PhotoReactionType::cases())),
            ],
        ];
    }

    private function getReactionTypeAsEnum(): PhotoReactionType
    {
        try {
            return PhotoReactionType::from($this->input('reaction_type'));
        } catch (InvalidArgumentException $e) {
            return PhotoReactionType::Like;
        }
    }

    public function __get($key)
    {
        if ($key === 'reaction') {
            return $this->getReactionTypeAsEnum();
        }

        return parent::__get($key);
    }
}
