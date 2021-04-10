<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FileExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileExportControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testBasicFunctioning(): void
    {
        Storage::fake();

        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $validFile = factory(FileExport::class)->create(['owner_id' => $user1->id]);
        $expiredFile = factory(FileExport::class)->state('expired')->create(['owner_id' => $user1->id]);

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

    public function testInvalidFileLocation(): void
    {
        Storage::fake();

        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $movedFile = factory(FileExport::class)->create(['owner_id' => $user1->id]);

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

    public function testDeletedFile(): void
    {
        Storage::fake();

        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $removedFile = factory(FileExport::class)->create(['owner_id' => $user1->id]);

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
