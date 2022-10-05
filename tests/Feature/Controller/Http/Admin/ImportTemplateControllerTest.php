<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Http\Admin;

use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportTemplateControllerTest extends TestCase
{
    /**
     * Test authentication and proper response.
     */
    public function test_fetch_unknown(): void
    {
        $route = route('admin.import.template', 'steve');

        // Assert guest users are redirected to login
        $this->get($route)->assertRedirect(route('login'));

        // Assert guest and all others get a 404
        $this->actingAs($this->getGuestUser());
        $this->get($route)->assertNotFound();

        $this->actingAs($this->getCommissionUser());
        $this->get($route)->assertNotFound();

        $this->actingAs($this->getBoardUser());
        $this->get($route)->assertNotFound();
    }

    /**
     * Test authentication and proper response.
     */
    public function test_fetch_activity_sheet(): void
    {
        $route = route('admin.import.template', 'activity');

        // Assert guest users are redirected to login
        $this->get($route)->assertRedirect(route('login'));

        // Assert 403 for unauthorized user
        $this->actingAs($this->getGuestUser());
        $this->get($route)->assertForbidden();

        Excel::fake();

        // Assume 200 for commission user
        $this->actingAs($this->getCommissionUser());
        $this->get($route)->assertOk();

        // Check download was sent
        Excel::assertDownloaded('template-activity.ods');
    }
}
