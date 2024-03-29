<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Files;

use App\Models\FileExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @group files
 */
class FileExportControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_basic_functioning(): void
    {
        Storage::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $validFile = FileExport::factory()->create(['owner_id' => $user1->id]);
        $expiredFile = FileExport::factory()->expired()->create(['owner_id' => $user1->id]);

        $response = $this->get(route('export.download', [$validFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertRedirect();

        $response = $this->get(route('export.download', [$expiredFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertRedirect();

        $this->actingAs($user1);

        $response = $this->get(route('export.download', [$validFile]));
        $response->assertHeader('Content-Disposition');
        $response->assertOk();

        $response = $this->get(route('export.download', [$expiredFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertStatus(410);

        $this->actingAs($user2);

        $response = $this->get(route('export.download', [$validFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertNotFound();

        $response = $this->get(route('export.download', [$expiredFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertNotFound();
    }

    public function test_invalid_file_location(): void
    {
        Storage::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $movedFile = FileExport::factory()->create(['owner_id' => $user1->id]);

        Storage::put('/test.txt', 'hi');

        $movedFile->path = 'test.txt';
        $movedFile->save();

        $response = $this->get(route('export.download', [$movedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertRedirect();

        $this->actingAs($user1);

        $response = $this->get(route('export.download', [$movedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertStatus(410);

        $this->actingAs($user2);

        $response = $this->get(route('export.download', [$movedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertNotFound();
    }

    public function test_deleted_file(): void
    {
        Storage::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $removedFile = FileExport::factory()->create(['owner_id' => $user1->id]);

        Storage::delete($removedFile->path);

        $response = $this->get(route('export.download', [$removedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertRedirect();

        $this->actingAs($user1);

        $response = $this->get(route('export.download', [$removedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertStatus(410);

        $this->actingAs($user2);

        $response = $this->get(route('export.download', [$removedFile]));
        $response->assertHeaderMissing('Content-Disposition');
        $response->assertNotFound();
    }
}
