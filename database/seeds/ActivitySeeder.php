<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class ActivitySeeder extends Seeder
{
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
        Activity::firstOrCreate([
            'slug' => $slug
        ], [
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
        Activity::firstOrCreate([
            'slug' => $slug
        ], [
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

        // Find or create Activity 1
        Activity::firstOrCreate([
            'slug' => "kerstborrel-{$year}"
        ], [
            'name' => "Kerstborrel {$shortYear}",
            'tagline' => 'Haha, Glühwein',
            'start_date' => $mondayBefore->setTime(15, 0, 0, 0),
            'end_date' => $mondayBefore->setTime(22, 0, 0, 0),
        ]);

        // Determine estimate activity price, raised € 0.50 each year since '17
        $memberPrice = (12 + ($fridayBefore->year - 2017) / 2.0);
        $guestPrice = ceil($memberPrice * 1.15);

        // Find or create Activity 2
        Activity::firstOrCreate([
            'slug' => "kerstdiner-{$year}"
        ], [
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
        $aprilWeek = (Carbon::parse('First Monday of April'))->addWeeks(3)->setTime(19, 0)->toImmutable();
        $startDate = ($aprilWeek < today()) ? $aprilWeek->addYear(1) : $aprilWeek;
        $endDate = $startDate->addDays(2)->setTime(12, 00);

        // LHW event
        factory(Activity::class, 1)->create([
            'name' => 'Landhuisweekend',
            'start_date' => $aprilWeek,
            'end_date' => $endDate,
            'role_id' => Role::findByName('lhw')->id
        ]);
    }
}
