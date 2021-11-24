<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use LogicException;
use Tests\TestCase;

class EnrollmentServiceTest extends TestCase
{
    /**
     * Test the enrollment service.
     * @dataProvider enrollmentOptions
     */
    public function test_get_enrollment_function(bool $shouldMatch, bool $makeUser, ?array $enrollmentData = null): void
    {
        $user = $makeUser ? factory(User::class)->create() : null;

        $activity = factory(Activity::class)->create();

        if ($enrollmentData !== null) {
            $enrollment = factory(Enrollment::class)->make();
            $enrollment->forceFill($enrollmentData);

            $enrollment->user()->associate($user);

            $activity->enrollments()->save($enrollment);
        }

        if ($user) {
            $this->actingAs($user);
        }

        $foundEnrollment = Enroll::getEnrollment($activity);

        if (! $shouldMatch) {
            $this->assertNull($foundEnrollment);

            return;
        }

        if ($enrollmentData === null) {
            throw new LogicException('Enrollment data unset but expected to find an enrollment');
        }

        $this->isInstanceOf(Enrollment::class, $foundEnrollment);
        $this->assertTrue($enrollment->is($foundEnrollment), "Returned enrollment isn't the expected enrollment");
    }

    public function enrollmentOptions(): array
    {
        return [
            'no user' => [false, false],
            'not enrolled' => [false, true, null],
            'enrolled' => [false, true, [
                'state' => States\Confirmed::class,
            ]],
            'confirmed' => [false, true, [
                'state' => States\Confirmed::class,
            ]],
            'cancelled' => [false, true, [
                'state' => States\Cancelled::class,
            ]],
            'deleted' => [false, true, [
                'deleted_at' => now(),
            ]],
        ];
    }
}
