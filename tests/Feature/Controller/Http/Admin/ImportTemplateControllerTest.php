<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Http\Admin;

use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportTemplateControllerTest extends TestCase
{
    /**
     * Test authentication and proper response.
     */
    public function test_fetch_import_sheet(): void
    {
        $route = URL::route('admin.activity.import-template');

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
