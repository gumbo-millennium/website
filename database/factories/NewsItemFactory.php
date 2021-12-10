<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helpers\Str;
use App\Models\NewsItem;
use App\Models\User;
use Database\Factories\Traits\HasEditorjs;
use Database\Factories\Traits\HasFileFinder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class NewsItemFactory extends Factory
{
    use HasEditorjs;
    use HasFileFinder;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::title($this->faker->words($this->faker->numberBetween(2, 8), true)),
            'contents' => json_encode($this->getEditorBlock()),
            'author_id' => optional(User::inRandomOrder()->first())->id,
            'sponsor' => $this->faker->optional(0.1)->company,
            'category' => $this->faker->randomElement(Config::get('gumbo.news-categories')),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (NewsItem $item) {
            $item->cover = Storage::disk('public')->putFile(
                'tests/images',
                $this->faker->randomElement($this->findImages('test-assets/images')),
            );
        });
    }
}
