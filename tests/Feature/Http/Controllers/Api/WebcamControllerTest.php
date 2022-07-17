<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

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

        Sanctum::actingAs($user = $this->getTemporaryUser(['member']));

        $deviceName = 'Test Device';
        $cameraName = 'video-0';

        $this->post(route('api.webcam.update'), [
            'device' => $deviceName,
            'name' => $cameraName,
            'image' => $image = UploadedFile::fake()->image('image.jpg'),
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
}
