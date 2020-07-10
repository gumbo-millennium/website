<?php

namespace Tests\Feature;

use App\Helpers\Str;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\DebugsResponses;

class JoinControllerTest extends TestCase
{
    use RefreshDatabase;
    use DebugsResponses;

    /**
     * Test without an event
     */
    public function testViewBasic()
    {
        // Check if NO intro activity exists
        $introActivity = Activity::query()
            ->where(['slug', 'like', 'intro-%'])
            ->exists();

        // Make sure
        $this->assertFalse($introActivity);

        // Get main form
        $response = $this->get(URL::route('join.form', [], false));

        // Debug helper
        $this->debugResponse($response);

        // Check if OK
        $response->assertStatus(200);
        $response->assertSee('Wat leuk dat je lid wil worden van Gumbo Millennium');
        $response->assertDontSee('mee op de introductieweek');
    }

    /**
     * Test without an event
     */
    public function testViewBasicWithIntro()
    {
        // Check if NO intro activity exists
        $introActivity = $this->createIntroActivity();

        // Make sure
        $this->assertTrue($introActivity->exists());

        // Get main form
        $response = $this->get(URL::route('join.form', [], false));

        // Debug helper
        $this->debugResponse($response);

        // Check if OK
        $response->assertStatus(200);
        $response->assertSee('Wat leuk dat je lid wil worden van Gumbo Millennium');
        $response->assertSee(Str::price($introActivity->total_price));
        $response->assertSee($introActivity->end_date->isoFormat('D MMMM'));
        $response->assertDontSee('mee op de introductieweek');
    }

    /**
     * Returns an activity for the intro
     * @param bool $enrollOpen
     * @return Activity
     * @throws BindingResolutionException
     */
    public function createIntroActivity(bool $enrollOpen = true): Activity
    {
        $data = [
            'start_date' => \now()->addWeek(),
            'end_date' => \now()->addWeek()->addDays(4),
            'enrollment_start' => \now()->subWeek(),
            'enrollment_end' => \now()->addWeek(),
            'price' => 100,
            'discount_price' => null
        ];

        if (!$enrollOpen) {
            $data['enrollment_end'] = \now()->subDay();
        }

        return \factory(Activity::class)->create($data)->first();
    }
}
