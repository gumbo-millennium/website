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
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    /**
     * Creates an activity if it's not already there yet
     *
     * @param string $slug
     * @param array $args
     * @param bool $withEnrollments Automatically register some users?
     * @return Activity|null Created activity, or null if it already exists
     *
     * Seeder is allowed to use boolean flag.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function safeCreate(string $slug, array $args, bool $withEnrollments = true): ?Activity
    {
        // Lookup slug
        if (Activity::whereSlug($slug)->exists()) {
            return null;
        }

        $activity = factory(Activity::class, 1)->create(array_merge(
            ['slug' => $slug],
            $args
        ))->first();

        // Don't register users if we don't want to
        if (!$withEnrollments) {
            return $activity;
        }

        // Get a random number of users in a random order
        $count = app(Faker::class)->numberBetween(2, User::count());

        // make sure we don't overpopulate the event
        if ($activity->seats && ($activity->seats * 0.8) < $count) {
            $count = (int) $activity->seats * 0.8;
        }

        // Find the $count numebr of users
        $users = User::query()
            ->where('email', 'NOT LIKE', '%@example.gumbo-millennium.nl')
            ->inRandomOrder()
            ->take($count)
            ->get();

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

        // Create payments events
        $this->seedPaymentTest();

        // Create seat tests
        $this->seedSeatTest();
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
            'price' => $eventPrice * 100, // Price in cents

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
        $memberDiscount = max(0, $guestPrice - $memberPrice);

        // Create Activity 2
        $slug = "kerstdiner-{$year}";
        $this->safeCreate($slug, [
            'name' => "Kerstdiner {$shortYear}",
            'tagline' => 'Haha, voer',
            'start_date' => $fridayBefore->setTime(17, 30, 0, 0),
            'end_date' => $fridayBefore->setTime(21, 0, 0, 0),
            'member_discount' => $memberDiscount * 100,
            'price' => $guestPrice * 100
        ]);
    }

    /**
     * Creates an event on the lhw role, to test role-level
     * access
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
            'start_date' => $startDate,
            'end_date' => $endDate,
            'role_id' => Role::findByName('lhw')->id
        ]);
    }

    /**
     * Seeds a bunch of events that start soon
     *
     * @return void
     */
    private function seedPaymentTest(): void
    {
        // [     slug     ] => [public, price, discount]
        $sets = [
            'private-free' => [false, null, null],
            'private-paid' => [false, 1500, null],
            'public-free' => [true, null, null],
            'public-member' => [true, 1500, 1500],
            'public-paid' => [true, 1500, null],
            'public-discount' => [true, 3000, 1500]
        ];

        // Date
        $startDate = today()->addWeek(1)->setTime(20, 0, 0);
        $endDate = (clone $startDate)->addHour(3);

        // Iterate
        foreach ($sets as $slug => list($paid, $price, $discount)) {
            $name = Str::studly($slug);
            $this->safeCreate($slug, [
                'name' => "[test] {$name}",
                'tagline' => 'Ik ben een test',
                'statement' => Str::limit($name, 16, ''),
                'start_date' => $startDate,
                'enrollment_start' => today(),
                'enrollment_end' => $startDate,
                'end_date' => $endDate,
                'is_public' => $paid,
                'member_discount' => max(0, $discount),
                'price' => $price,
                'seats' => 15
            ]);

            // Increase both
            $startDate = $startDate->addDay();
            $endDate = $endDate->addDay();
        }
    }

    private function seedSeatTest(): void
    {
        // [     slug     ] => [seats, taken-member, taken-guest]
        $sets = [
            'popstar-event' => [100, 60, 40],
            'private-popstar-event' => [100, 75, 0],
            'limited-popstar-event' => [100, 60, 10],
        ];

        // Date
        $date = today()->addWeek(3)->setTime(20, 0, 0)->toImmutable();

        // Iterate
        foreach ($sets as $slug => list($seats, $memberEnroll, $guestEnroll)) {
            $name = Str::studly($slug);
            $activity = $this->safeCreate($slug, [
                'name' => "[test] {$name}",
                'tagline' => 'Testing seat occupancy',
                'location' => 'Het Vliegende Paard',
                'location_address' => 'Het Vliegende Paard, Voorstraat 17, 8011 MK Zwolle, Netherlands',
                'statement' => Str::limit($name, 16, ''),
                'start_date' => $date,
                'enrollment_start' => today(),
                'enrollment_end' => $date->subHour(3),
                'end_date' => $date->addHour(2),
                'is_public' => true,
                'member_discount' => null,
                'price' => null,
                'seats' => $seats
            ], false);

            // Add member enrollments
            if ($memberEnroll) {
                $enrollments = factory(Enrollment::class, $memberEnroll)->create([
                    'activity_id' => $activity->id
                ]);

                // Make all users member
                foreach ($enrollments as $enrollment) {
                    $enrollment->user->assignRole('member');
                }
            }

            // Add guest enrollments
            if ($guestEnroll) {
                $enrollments = factory(Enrollment::class, $guestEnroll)->create([
                    'activity_id' => $activity->id
                ]);

                // Make all users member
                foreach ($enrollments as $enrollment) {
                    $enrollment->user->removeRole('member');
                }
            }

            // Increase date
            $date = $date->addDay();
        }
    }
}
