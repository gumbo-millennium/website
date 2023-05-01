<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Admin;

use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ActivityImportControllerTest extends TestCase
{
    /**
     * Test authentication and proper response.
     */
    public function test_fetch_unknown(): void
    {
        $route = route('admin.activity.import-template');

        // Assert guest users are redirected to login
        $this->get($route)->assertRedirect(route('login'));

        // Assert guest and regular member gets denied access
        $this->actingAs($this->getGuestUser());
        $this->get($route)->assertForbidden();

        $this->actingAs($this->getMemberUser());
        $this->get($route)->assertForbidden();

        // Assert commission and board member gets access
        $this->actingAs($this->getCommissionUser());
        $this->get($route)->assertOk();

        $this->actingAs($this->getBoardUser());
        $this->get($route)->assertOk();
    }

    /**
     * Test authentication and proper response.
     */
    public function test_fetch_activity_sheet(): void
    {
        $route = route('admin.activity.import-template');

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
        Excel::assertDownloaded('template.xlsx');
    }
}
