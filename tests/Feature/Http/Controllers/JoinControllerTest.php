<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Helpers\Arr;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class JoinControllerTest extends TestCase
{
    private const VALID_FIELDS = [
        'first-name' => 'Sam',
        'last-name' => 'Smith',
        'email' => 'sam.smith@example.com',
        'phone' => '038 845 0100',
        'date-of-birth' => '2000-01-01',
        'gender' => '-',
        'street' => 'Dorpsweg',
        'number' => '1',
        'postal-code' => '1234AS',
        'city' => 'Zwolle',
        'accept-terms' => 1,
    ];

    /**
     * @return void
     */
    public function test_show_index()
    {
        $response = $this->get(route('join.form'));

        $response->assertOk();
        $response->assertSee(sprintf('action="%s"', route('join.submit')), false);
    }

    /**
     * @dataProvider formSubmissionFields
     */
    public function test_submit_index(array $data, array $errors): void
    {
        $response = $this->post(route('join.submit'), $data);

        // Next section is fail-state
        if (! empty($errors)) {
            $response->assertSessionHasErrors($errors);

            return;
        }

        // Next section is success-state
        $response
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('join.complete'));
    }

    public function test_intro_activity_determination(): void
    {
        $this->markTestSkipped('Test is broken');

        $introDate = Date::parse('Next Wednesday, 10:00')->toImmutable();
        if ($introDate->lessThan(Date::now())) {
            $introDate = $introDate->addWeek();
        }

        $nextActivity = Activity::factory()->createMany([
            [
                'start_date' => $introDate->subYear(),
                'end_date' => $introDate->subYear()->addDays(3),
                'enrollment_start' => $introDate->subYear()->subMonths(3),
            ], [
                'start_date' => $introDate,
                'end_date' => $introDate->addDays(3),
                'enrollment_start' => $introDate->subMonths(3),
            ], [
                'start_date' => $introDate->addYear(),
                'end_date' => $introDate->addYear()->addDays(3),
                'enrollment_start' => $introDate->addYear()->subMonths(3),
            ],
        ])->each(function (Activity $activity) {
            $activity->tickets()->createMany([
                [
                    'title' => 'Regular price',
                    'price' => 50_00,
                    'is_public' => true,
                ],
                [
                    'title' => 'Cheaper',
                    'price' => 25_00,
                    'is_public' => true,
                ],
                [
                    'title' => 'Private',
                    'price' => 40_00,
                    'is_public' => false,
                ],
            ]);
        })->get(1);

        // $this->get(route('join.form'))
        //     ->assertOk()
        //     ->assertSee('data-intro-state="open"', false)
        //     ->assertSee("data-intro-ticket=\"{$nextActivity->tickets->firstWhere('title', 'Cheaper')->id}\"", false);

        $this->get(route('join.form-intro'))
            ->assertOk()
            ->assertSee('data-intro-state="open"', false)
            ->assertSee("data-intro-ticket=\"{$nextActivity->tickets->firstWhere('title', 'Cheaper')->id}\"", false);
    }

    public function test_submit_with_intro_request(): void
    {
        $introActivity = Activity::factory()->public()->create([
            'slug' => 'intro-test',
            'start_date' => Date::now()->addWeek(),
            'end_date' => Date::now()->addWeek()->addDays(3),
            'enrollment_start' => Date::now()->subYear(),
            'enrollment_end' => Date::now()->addWeek(),
        ]);

        $introActivity->tickets()->create([
            'title' => 'Regular price',
            'price' => 50_00,
            'is_public' => true,
        ]);

        $inputFields = array_merge(self::VALID_FIELDS, [
            'join-intro' => '1',
        ]);

        $this->post(route('join.submit'), $inputFields)
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('enroll.show', [$introActivity]));

        $this->assertDatabaseHas(User::make()->getTable(), [
            'email' => self::VALID_FIELDS['email'],
        ]);

        $user = User::where('email', self::VALID_FIELDS['email'])->first();
        $this->assertNotNull($user, 'Failed finding created user');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'activity_id' => $introActivity->id,
        ]);
    }

    public function formSubmissionFields(): array
    {
        $validFields = self::VALID_FIELDS;

        // Firstly test happy path
        $output = [
            'valid' => [
                'data' => $validFields,
                'errors' => [],
            ],
        ];

        // Then skip each field separately
        foreach (array_keys($validFields) as $field) {
            $invalidFields = Arr::except($validFields, $field);
            $output["missing {$field}"] = [
                'data' => $invalidFields,
                'errors' => [$field],
            ];
        }

        return $output;
    }
}
