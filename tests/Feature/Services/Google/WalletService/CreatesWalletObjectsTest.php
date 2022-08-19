<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google\WalletService;

use App\Facades\Enroll;
use App\Helpers\Arr;
use App\Models\Activity;
use App\Models\States\Enrollment as EnrollmentStates;
use App\Models\User;
use App\Services\Google\Traits\CreatesWalletIds;
use App\Services\Google\Traits\CreatesWalletObjects;
use App\Services\Google\WalletObjects;
use App\Services\Google\WalletObjects\EventTicketClass;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class CreatesWalletObjectsTest extends TestCase
{
    use CreatesWalletIds;
    use CreatesWalletObjects;

    /**
     * @before
     */
    public function setupIssuerIdAndTraitBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            Config::set('services.google.wallet.issuer_id', '1001337');
        });
    }

    /**
     * Ensure the default values are correct to not trigger an auto-decline by Google.
     *
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function test_base_class_contains_google_required_fields(): void
    {
        $base = $this->makeBaseTicketClass();

        $required = [
            'issuerName',
            'homepageUri',
            'reviewStatus',
            'countryCode',
            'hexBackgroundColor',
        ];

        foreach ($required as $field) {
            $this->assertNotEmpty($base->{$field});
            $this->assertNotEquals(\Google\Model::NULL_VALUE, $base->{$field});
        }

        $this->assertSame(WalletObjects\ReviewStatus::UNDER_REVIEW, $base->reviewStatus);
    }

    /**
     * Test basic creation of a ticket class.
     */
    public function test_ticket_class_basics(): void
    {
        $activity = Activity::factory()->create();
        $activityClassId = $this->getActivityClassId($activity);

        $result = $this->makeActivityTicketClass($activity);

        $this->assertInstanceOf(EventTicketClass::class, $result);
        $this->assertSame($activityClassId, $result->id);
        $this->assertSame($activityClassId, $result->eventId);

        $this->assertEquals(new WalletObjects\DateTime([
            'start' => $activity->start_date->toIso8601String(),
            'end' => $activity->end_date->toIso8601String(),
        ]), $result->dateTime);

        $this->assertInstanceOf(WalletObjects\Image::class, $result->logo);

        $this->assertInstanceOf(WalletObjects\LinksModuleData::class, $result->linksModuleData);

        $links = Arr::pluck($result->linksModuleData->uris, 'uri');
        $this->assertContains(route('activity.show', $activity), $links);
        $this->assertContains(route('enroll.show', $activity), $links);

        $this->assertEquals(WalletObjects\LocalizedString::create('nl', $activity->name), $result->eventName);
    }

    /**
     * Check for the two conditional rows on the ticket.
     */
    public function test_ticket_class_conditionals()
    {
        $activity = Activity::factory()->withImage()->create([
            'location' => 'Damrak',
            'location_address' => 'Damrak 1, Amsterdam',
        ]);
        $activityClassId = $this->getActivityClassId($activity);

        $result = $this->makeActivityTicketClass($activity);

        $this->assertSame($activityClassId, $result->id);

        $this->assertInstanceOf(WalletObjects\Image::class, $result->heroImage);
        $this->assertInstanceOf(WalletObjects\ImageUri::class, $result->heroImage->sourceUri);

        $this->assertInstanceOf(WalletObjects\EventVenue::class, $result->venue);
        $this->assertEquals(WalletObjects\LocalizedString::create('nl', $activity->location), $result->venue->name);
        $this->assertEquals(WalletObjects\LocalizedString::create('nl', $activity->location_address), $result->venue->address);
    }

    public function test_ticket_objects_defaults(): void
    {
        $this->actingAs($user = User::factory()->create());
        $activity = Activity::factory()->withImage()->withTickets()->create();
        $activityClassId = $this->getActivityClassId($activity);

        $ticket = $activity->tickets->first();
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        $enrollmentObjectId = $this->getEnrollmentObjectId($activity->enrollments->first());
        $result = $this->makeEnrollmentTicketObject($enrollment);

        $this->assertSame($enrollmentObjectId, $result->id);
        $this->assertSame($activityClassId, $result->classId);

        $this->assertInstanceOf(WalletObjects\EventReservationInfo::class, $result->reservationInfo);
        $this->assertSame($enrollment->id, $result->reservationInfo->confirmationCode);

        $this->assertSame($user->name, $result->ticketHolderName);
        $this->assertSame($enrollment->id, $result->ticketNumber);

        $this->assertEquals(
            WalletObjects\LocalizedString::create('nl', $ticket->title),
            $result->ticketType,
        );
        $this->assertEquals(
            WalletObjects\Money::createForCents($ticket->price),
            $result->faceValue,
        );

        $this->assertEquals(WalletObjects\State::INACTIVE, $result->state);

        $this->assertInstanceOf(WalletObjects\Barcode::class, $result->barcode);
        $this->assertSame(WalletObjects\BarcodeType::QR_CODE, $result->barcode->type);
        $this->assertSame($enrollment->ticket_code, $result->barcode->value);
    }

    public function test_ticket_object_state_on_past_events(): void
    {
        $this->actingAs(User::factory()->create());

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $enrollment = Enroll::createEnrollment($activity, $ticket);
        $enrollment->transitionTo(EnrollmentStates\Confirmed::class);

        $activity->start_date = Date::now()->subDays(1);
        $activity->end_date = Date::now()->subDays(1)->addHours(3);
        $activity->save();

        $result = $this->makeEnrollmentTicketObject($enrollment->fresh());

        $this->assertSame(WalletObjects\State::EXPIRED, $result->state);
    }

    public function test_ticket_object_state_transitions(): void
    {
        $this->actingAs(User::factory()->create());

        $activity = Activity::factory()->withTickets()->create();
        $ticket = $activity->tickets->first();

        $enrollment = Enroll::createEnrollment($activity, $ticket);

        $resultCreated = $this->makeEnrollmentTicketObject($enrollment);
        $this->assertSame(WalletObjects\State::INACTIVE, $resultCreated->state);

        $enrollment->transitionTo(EnrollmentStates\Paid::class);

        $resultPaid = $this->makeEnrollmentTicketObject($enrollment);
        $this->assertSame(WalletObjects\State::ACTIVE, $resultPaid->state);

        $enrollment2 = clone $enrollment;
        $enrollment2->consumed_at = Date::now();

        $resultConsumed = $this->makeEnrollmentTicketObject($enrollment2);
        $this->assertSame(WalletObjects\State::COMPLETED, $resultConsumed->state);

        $enrollment->transitionTo(EnrollmentStates\Cancelled::class);
        $enrollment->save(['state']);

        $resultCancelled = $this->makeEnrollmentTicketObject($enrollment);
        $this->assertSame(WalletObjects\State::EXPIRED, $resultCancelled->state);
    }
}
