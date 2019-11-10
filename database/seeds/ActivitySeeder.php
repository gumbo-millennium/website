<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Faker\Generator as Faker;

class ActivitySeeder extends Seeder
{
    /**
     * Creates an activity if it's not already there yet
     *
     * @param string $slug
     * @param array $args
     * @return Activity|null
     */
    private function safeCreate(string $slug, array $args): ?Activity
    {
        // Lookup slug
        if (Activity::whereSlug($slug)->exists()) {
            return null;
        }

        $activity = factory(Activity::class, 1)->create(array_merge(
            ['slug' => $slug],
            $args
        ))->first();

        // Get a random number of users in a random order
        $count = app(Faker::class)->numberBetween(2, User::count());
        $users = User::inRandomOrder()->take($count)->get();

        // Pair each user with the activity
        foreach ($users as $user) {
            factory(Enrollment::class, 1)->create([
                'activity_id' => $activity->id,
                'user_id' => $user->id
            ]);
        }

        return $activity;
    }
    /**
     * Returns start of semester, as immutable element
     *
     * @return \DateTimeImmutable
     */
    protected static function getStartOfYear(): CarbonImmutable
    {
        static $start;

        if ($start) {
            return $start;
        }

        // Determine start of university year
        $start = (new Carbon('First Monday of September', 'UTC'))->toImmutable();

        // Get next start of university year if this year has already started.
        if ($start < now()) {
            $start = (new Carbon('First Monday of September next year', 'UTC'))->toImmutable();
        }

        // Return the start of the college year
        return $start;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Bruisweken event
        $this->seedBruisweken();

        // Create introduction period event
        $this->seedGumboIntro();

        // Create Christmas events
        $this->seedChristmasEvents();

        // Create permission test events
        $this->seedBasicAccessEvent();
    }

    /**
     * Adds a Bruisweken event
     *
     * @return void
     */
    public function seedBruisweken(): void
    {
        // Bruisweken are one week before start of semester
        $startDate = $this->getStartOfYear()->sub('P1W');
        $year = $startDate->year;

        // Get slug
        $slug = "bruisweken-{$year}";

        // Find or create Activity
        $this->safeCreate($slug, [
            'name' => 'Bruisweken',
            'tagline' => 'De Bruisweken zijn de algemene introductieweken voor nieuwe studenten in Zwolle.',
            'start_date' => $startDate->setTime(10, 0, 0, 0),
            'end_date' => $startDate->addDays(2)->setTime(15, 0, 0, 0),
            'enrollment_start' => null,
            'enrollment_end' => null,
        ]);
    }

    /**
     * Adds an introduction week event
     *
     * @return void
     */
    public function seedGumboIntro(): void
    {
        // Bruisweken are one week before start of semester
        $date = $this->getStartOfYear()->add('P7W');
        $year = $date->year;

        // Get slug
        $slug = "intro-{$year}";

        // Enrollment price
        $eventPrice = 50;

        // Find or create Activity
        $this->safeCreate($slug, [
            'name' => 'Introductieweek',
            'tagline' =>
            'Maak kennis met je mede eerstejaars Gumbo leden tijdens onze spectaculaire introductieweek.',
            'start_date' => $date->setTime(10, 0, 0, 0),
            'end_date' => $date->addDays(5)->setTime(10, 0, 0, 0),
            'seats' => null,
            'price_member' => $eventPrice * 100, // Price in cents
            'price_guest' => $eventPrice * 100, // Price in cents

            // Make sure users can enroll until friday
            'enrollment_end' => $date->sub('P2DT1S'),
        ]);
    }

    /**
     * Adds Christmas drinks and Christmas dinner
     *
     * @return void
     */
    public function seedChristmasEvents(): void
    {
        // Find christmas
        $christmasDate = Carbon::parse('December 25th', 'UTC')->toImmutable();

        // Make sure it's in the future
        if ($christmasDate > now()) {
            $christmasDate = $christmasDate->addYears(1);
        }

        // Create events yet to happen
        $this->createChristmasEvents($christmasDate);

        // Create events already happened
        $this->createChristmasEvents($christmasDate->subYear(1));
    }

    /**
     * Creates all christmas events for this given date
     */
    private function createChristmasEvents(DateTimeImmutable $date): void
    {
        // Get friday and monday
        $fridayBefore = Carbon::parse(strtotime('Last Friday', $date->getTimestamp()))->toImmutable();
        $mondayBefore = Carbon::parse(strtotime('Last Monday', $fridayBefore->getTimestamp()))->toImmutable();

        // Bruisweken are one week before start of semester
        $year = $fridayBefore->year;
        $shortYear = $fridayBefore->format('\'y');

        // create Activity 1
        $slug = "kerstborrel-{$year}";
        $this->safeCreate($slug, [
            'slug' => "kerstborrel-{$year}",
            'name' => "Kerstborrel {$shortYear}",
            'tagline' => 'Haha, Glühwein',
            'start_date' => $mondayBefore->setTime(15, 0, 0, 0),
            'end_date' => $mondayBefore->setTime(22, 0, 0, 0),
        ]);

        // Determine estimate activity price, raised € 0.50 each year since '17
        $memberPrice = (12 + ($fridayBefore->year - 2017) / 2.0);
        $guestPrice = ceil($memberPrice * 1.15);

        // Create Activity 2
        $slug = "kerstdiner-{$year}";
        $this->safeCreate($slug, [
            'name' => "Kerstdiner {$shortYear}",
            'tagline' => 'Haha, voer',
            'start_date' => $fridayBefore->setTime(17, 30, 0, 0),
            'end_date' => $fridayBefore->setTime(21, 0, 0, 0),
            'price_member' => $memberPrice * 100,
            'price_guest' => $guestPrice * 100
        ]);
    }

    /**
     * Creates two events, one for the 'lhw' committee to test
     * access per group, and one for the `event-owner@example.com`
     * user to test user-level access
     *
     * @return void
     */
    private function seedBasicAccessEvent(): void
    {
        // 3rd week of april
        $aprilWeek = (Carbon::parse('First Friday of April'))->addWeeks(3)->setTime(19, 0)->toImmutable();
        $startDate = ($aprilWeek < today()) ? $aprilWeek->addYear(1) : $aprilWeek;
        $endDate = $startDate->addDays(2)->setTime(12, 00);

        // LHW event
        $this->safeCreate("lhw-{$startDate->year}", [
            'name' => 'Landhuisweekend',
            'start_date' => $aprilWeek,
            'end_date' => $endDate,
            'role_id' => Role::findByName('lhw')->id
        ]);
    }
}
