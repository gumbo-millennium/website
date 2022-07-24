<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\Str;
use App\Models\Webcam\Device;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests device uploads and webcam retrieval.
 */
class WebcamControllerTest extends TestCase
{
    /**
     * Try uploading a webcam.
     */
    public function test_submit_device(): void
    {
        Storage::fake(Config::get('gumbo.images.disk'));

        Sanctum::actingAs(
            $user = $this->getTemporaryUser(['member']),
            ['openapi'],
        );

        $deviceName = (string) Str::uuid();
        $cameraName = 'video-0';

        $this->put(route('api.webcam.update'), [
            'device' => $deviceName,
            'name' => $cameraName,
            'image' => UploadedFile::fake()->image('image.jpg', 640, 480),
        ])->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseHas('webcam_devices', [
            'device' => $deviceName,
            'name' => $cameraName,
        ]);

        $device = Device::where([
            'device' => $deviceName,
            'name' => $cameraName,
        ])->first();

        $this->assertNotNull($device);
        $this->assertTrue($user->is($device->owner), 'Device owner doesn\'t match user');

        Storage::disk(Config::get('gumbo.images.disk'))->assertExists($device->path);
    }

    /**
     * Test that devices can only be updated by their owner, not
     * by everyone.
     */
    public function test_overwriting_exising_cameras(): void
    {
        $user = $this->getTemporaryUser(['member']);
        $otherUser = $this->getTemporaryUser(['member']);

        Storage::fake(Config::get('gumbo.images.disk'));

        $device1 = Device::factory()->for($user, 'owner')->create();
        $device2 = Device::factory()->for($otherUser, 'owner')->create();

        Sanctum::actingAs($user, ['openapi']);

        $this->put(route('api.webcam.update'), [
            'device' => $device1->device,
            'name' => $device1->name,
            'image' => UploadedFile::fake()->image('image.jpg', 640, 480),
        ])->assertStatus(Response::HTTP_ACCEPTED);

        $this->put(route('api.webcam.update'), [
            'device' => $device2->device,
            'name' => $device2->name,
            'image' => UploadedFile::fake()->image('image.jpg', 640, 480),
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
