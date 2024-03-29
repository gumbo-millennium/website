<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\User;
use Database\Factories\Traits\HasEditorjs;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    use HasEditorjs;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::title($this->faker->words($this->faker->numberBetween(2, 8), true)),
            'contents' => '[]',
            'author_id' => optional(User::inRandomOrder()->first())->id,
        ];
    }

    public function withSummary(): self
    {
        return $this->state([
            'summary' => $this->faker->sentence,
        ]);
    }

    public function withContents(): self
    {
        return $this->state([
            'contents' => json_encode($this->getEditorBlocks()),
        ]);
    }

    public function hidden(bool $hidden = true): self
    {
        return $this->state([
            'hidden' => $hidden,
        ]);
    }
}
