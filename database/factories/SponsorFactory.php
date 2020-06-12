<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Sponsor;
use App\Models\SponsorClick;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

// Get sponsor directory
$scandir = require __DIR__ . '/../helpers/files.php';
$colorSponsors = $scandir('test-assets/images/logos/src/logos/color', 'svg');
$grayscaleSponsors = $scandir('test-assets/images/logos/src/logos/grayscale', 'svg');
$backdropImage = $scandir('test-assets/images', 'jpg');
$editorjs = require __DIR__ . '/../helpers/editorjs.php';


// Generate sponsor
// phpcs:ignore Generic.Files.LineLength.TooLong
$factory->define(Sponsor::class, static function (Faker $faker) use ($colorSponsors, $grayscaleSponsors, $backdropImage, $editorjs) {
    // Add start and end date
    $startDate = $faker->dateTimeBetween('-6 months', '+6 months');
    $endDate = (clone $startDate)->add($faker->dateTimeThisMonth()->diff(now()));

    // Remove end date in 50% of the time
    if ($faker->boolean) {
        $endDate = null;
    }

    // Copy logo's to filesystem
    $colorLogo = Storage::disk(Sponsor::LOGO_DISK)
        ->putFile(Sponsor::LOGO_PATH, $faker->randomElement($colorSponsors));
    $grayscaleLogo = Storage::disk(Sponsor::LOGO_DISK)
        ->putFile(Sponsor::LOGO_PATH, $faker->randomElement($grayscaleSponsors));

    // Build base data
    $data = [
        'deleted_at' => $faker->optional(0.2)->passthrough(now()),
        'name' => $faker->company,
        'url' => $faker->url,
        'starts_at' => $startDate,
        'ends_at' => $endDate,
        'logo_gray' => $grayscaleLogo,
        'logo_color' => $colorLogo,
    ];

    // Add backdrop and caption if possible
    if ($faker->boolean(75)) {
        $data['caption'] = $faker->sentence;
        $data['backdrop'] = $faker->randomElement($backdropImage);
    }

    // Add contents and associated title
    if ($faker->boolean(50)) {
        $data['contents_title'] = $faker->sentence;
        $data['contents'] = \json_encode($editorjs($faker));
    }

    // Done :)
    return $data;
});

$factory->afterCreating(Sponsor::class, static function (Sponsor $sponsor) {
    factory(SponsorClick::class, 10)->create([
        'sponsor_id' => $sponsor->id
    ]);
});
