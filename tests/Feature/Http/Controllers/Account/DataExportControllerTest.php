<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\DataExport;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class DataExportControllerTest extends TestCase
{
    protected User $user;

    protected ?User $otherUser = null;

    /**
     * @before
     */
    public function ensureActingAsSomeone(): void
    {
        $this->afterApplicationCreated(function () {
            $this->user = User::factory()->create();

            $this->actingAs($this->user);
        });
    }

    public function test_empty_index(): void
    {
        $this->get(route('account.export.index'))
            ->assertOk()
            ->assertSee('data-x-group="no-export-card"', false)
            ->assertSee('data-x-group="request-form"', false);
    }

    public function test_some_items_index(): void
    {
        DataExport::factory()->times(3)->expired()->create(['user_id' => $this->user->id]);
        DataExport::factory()->times(2)->withData()->create(['user_id' => $this->user->id]);
        DataExport::factory()->times(1)->create(['user_id' => $this->user->id]);

        $this->get(route('account.export.index'))
            ->assertOk()
            ->assertSee('data-x-group="has-exports"', false)
            ->assertDontSee('data-x-group="has-pages"', false)
            ->assertSee('data-x-group="request-form"', false);
    }

    public function test_many_items(): void
    {
        DataExport::factory()->times(20)->expired()->create(['user_id' => $this->user->id]);
        DataExport::factory()->times(2)->withData()->create(['user_id' => $this->user->id]);
        DataExport::factory()->times(1)->create(['user_id' => $this->user->id]);

        $this->get(route('account.export.index'))
            ->assertOk()
            ->assertSee('data-x-group="has-exports"', false)
            ->assertSee('data-x-group="has-pages"', false)
            ->assertSee('data-x-group="request-form"', false);
    }

    public function test_request_item(): void
    {
        Date::setTestNow('2021-05-05 14:00:00+01:00');

        $response = $this->post(route('account.export.store'))
            ->assertRedirect();

        $this->assertDatabaseHas(DataExport::make()->getTable(), [
            'user_id' => $this->user->id,
            'created_at' => Date::now(),
            'expires_at' => Date::now()->add(Config::get('gumbo.retention.data-exports')),
        ]);

        $expectedDataExport = DataExport::latest()->first();
        $nextUrl = $response->headers->get('location');

        $this->assertSame(
            route('account.export.show', [$expectedDataExport->id, $expectedDataExport->token]),
            $nextUrl,
        );

        $this->get($nextUrl)
            ->assertOk();

        $this->checkNotAccessibleByOther($nextUrl);
    }

    public function test_display_fresh_item(): void
    {
        $item = DataExport::factory()->create(['user_id' => $this->user->id]);

        $showRoute = route('account.export.show', [$item->id, $item->token]);
        $downloadRoute = route('account.export.download', [$item->id, $item->token]);

        $this->checkNotAccessibleByOther($showRoute);
        $this->checkNotAccessibleByOther($downloadRoute);

        $this->get($showRoute)
            ->assertOk()
            ->assertDontSee($downloadRoute);

        $this->get($downloadRoute)
            ->assertNotFound();
    }

    public function test_display_ready_item(): void
    {
        $item = DataExport::factory()->withData()->create(['user_id' => $this->user->id]);

        $showRoute = route('account.export.show', [$item->id, $item->token]);
        $downloadRoute = route('account.export.download', [$item->id, $item->token]);

        $this->checkNotAccessibleByOther($showRoute);
        $this->checkNotAccessibleByOther($downloadRoute);

        $this->get($showRoute)
            ->assertOk()
            ->assertSee($downloadRoute);

        $this->get($downloadRoute)
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=\"{$item->file_name}\"");
    }

    public function test_display_expired_item(): void
    {
        $item = DataExport::factory()->expired()->create(['user_id' => $this->user->id]);

        $showRoute = route('account.export.show', [$item->id, $item->token]);
        $downloadRoute = route('account.export.download', [$item->id, $item->token]);

        $this->checkNotAccessibleByOther($showRoute);
        $this->checkNotAccessibleByOther($downloadRoute);

        $this->get($showRoute)
            ->assertOk()
            ->assertDontSee($downloadRoute);

        $this->get($downloadRoute)
            ->assertNotFound();
    }

    private function checkNotAccessibleByOther(string $url): void
    {
        $this->otherUser ??= User::factory()->create();

        $this->actingAs($this->otherUser);

        $this->get($url)->assertNotFound();

        $this->actingAs($this->user);
    }
}
