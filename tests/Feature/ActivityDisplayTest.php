<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\CreatesDummyActivityModels;
use Tests\Traits\MutatesActivities;

class ActivityDisplayTest extends TestCase
{
    use RefreshDatabase;
    use CreatesDummyActivityModels;
    use MutatesActivities;

    /**
     * View activity list and detail as guest
     * @return void
     */
    public function testViewAnonymous(): void
    {
        // Prep activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Get the views
        $indexView = $this->get(URL::route('activity.index'));
        $responsePublic = $this->get(URL::route('activity.show', ['activity' => $publicActivity]));
        $responsePrivate = $this->get(URL::route('activity.show', ['activity' => $privateActivity]));

        // Check result
        $indexView->assertOk();
        $responsePublic->assertOk();
        $responsePrivate->assertRedirect(URL::route('login'));

        // Check results
        $indexView->assertSeeText($publicActivity->name);
        $indexView->assertDontSeeText($privateActivity->name);
        $responsePublic->assertSeeText($publicActivity->name);
    }

    /**
     * View activity list and detail as normal, unroled user
     * @return void
     */
    public function testViewUser(): void
    {
        // Prep activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Prep user
        $user = $this->getGuestUser();

        // Get the views
        $indexView = $this->actingAs($user)->get(URL::route('activity.index'));
        $responsePublic = $this->actingAs($user)->get(URL::route('activity.show', ['activity' => $publicActivity]));
        $responsePrivate = $this->actingAs($user)->get(URL::route('activity.show', ['activity' => $privateActivity]));

        // Check result
        $indexView->assertOk();
        $responsePublic->assertOk();
        $responsePrivate->assertForbidden();

        // Check results
        $indexView->assertSeeText($publicActivity->name);
        $indexView->assertDontSeeText($privateActivity->name);
        $responsePublic->assertSeeText($publicActivity->name);
    }

    /**
     * View activity list and detail as member
     * @return void
     */
    public function testViewMember(): void
    {
        // Prep activities
        $publicActivity = $this->createDummyActivity(null, true);
        $privateActivity = $this->createDummyActivity(null, false);

        // Prep user
        $user = $this->getMemberUser();

        // Get the views
        $indexView = $this->actingAs($user)->get(URL::route('activity.index'));
        $responsePublic = $this->actingAs($user)->get(URL::route('activity.show', ['activity' => $publicActivity]));
        $responsePrivate = $this->actingAs($user)->get(URL::route('activity.show', ['activity' => $privateActivity]));

        // Check result
        $indexView->assertOk();
        $responsePublic->assertOk();
        $responsePrivate->assertOk();

        // Check results
        $indexView->assertSeeText($publicActivity->name);
        $indexView->assertSeeText($privateActivity->name);
        $responsePublic->assertSeeText($publicActivity->name);
        $responsePrivate->assertSeeText($privateActivity->name);
    }
}
