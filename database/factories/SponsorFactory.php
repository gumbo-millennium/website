<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sponsor;
use App\Models\SponsorClick;
use Database\Factories\Traits\HasEditorjs;
use Database\Factories\Traits\HasFileFinder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class SponsorFactory extends Factory
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
        // Get sponsor imagery
        $colorSponsors = $this->findFiles('test-assets/images/logos/src/logos/color', 'svg');
        $grayscaleSponsors = $this->findFiles('test-assets/images/logos/src/logos/grayscale', 'svg');

        // Copy logo's to filesystem
        $colorLogo = Storage::disk(Sponsor::LOGO_DISK)
            ->putFile(Sponsor::LOGO_PATH, $colorSponsors->random());
        $grayscaleLogo = Storage::disk(Sponsor::LOGO_DISK)
            ->putFile(Sponsor::LOGO_PATH, $grayscaleSponsors->random());

        // Build base data
        $data = [
            'name' => $this->faker->company,
            'url' => $this->faker->url,
            'starts_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'logo_gray' => $grayscaleLogo,
            'logo_color' => $colorLogo,
        ];

        // Add contents and associated title
        if ($this->faker->boolean(50)) {
            $data['contents_title'] = $this->faker->sentence;
            $data['contents'] = json_encode($this->getEditorBlock());
        }

        // Done :)
        return $data;
    }

    public function hasBackdrop(): self
    {
        $backdropImage = $this->findImages('test-assets/images');

        // Add backdrop and caption
        return $this->state([
            'caption' => $this->faker->sentence,
        ])->afterMaking(function (Sponsor $sponsor) use ($backdropImage) {
            $sponsor->cover = Storage::disk('public')
                ->putFile('seeded/sponsors', $backdropImage->random());
        });
    }

    public function configure()
    {
        return $this->has(SponsorClick::factory()->times(10), 'clicks');
    }
}
