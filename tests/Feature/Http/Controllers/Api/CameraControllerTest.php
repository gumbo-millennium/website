<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\Str;
use App\Models\User;
use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CameraControllerTest extends TestCase
{
    public function test_information_exposure(): void
    {
        $webcam = Webcam::factory()->create();
        $user = User::factory()->create();

        $this->get(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => 986598, // should not exist in our small scope
        ]))->assertForbidden();

        $this->get(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => 'null',
        ]))->assertForbidden();

        $this->get(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertForbidden();

        $this->put(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => 986598, // should not exist in our small scope
        ]))->assertForbidden();

        $this->put(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => 'null',
        ]))->assertForbidden();

        $this->put(route('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertForbidden();
    }

    public function test_no_user_auth(): void
    {
        $webcam = Webcam::factory()->create();
        $user = User::factory()->create();
        $user->delete();

        $this->get(URL::signedRoute('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user->id,
        ]))->assertForbidden();

        $this->put(URL::signedRoute('api.webcam.store', [
            'webcam' => $webcam,
            'user' => $user->id,
        ]))->assertForbidden();
    }

    public function test_user_auth(): void
    {
        $webcam = Webcam::factory()->create();
        $user = User::factory()->create();

        $this->get(URL::signedRoute('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertForbidden();

        $this->put(URL::signedRoute('api.webcam.store', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertForbidden();
    }

    public function test_member_auth(): void
    {
        $webcam = Webcam::factory()->create();

        $webcamImage = WebcamUpdate::factory()->withImage()->make();
        $webcam->updates()->save($webcamImage);

        $user = User::factory()->create()->givePermissionTo('plazacam-view');

        $this->get(URL::signedRoute('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertOk();

        $this->put(URL::signedRoute('api.webcam.store', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertForbidden();
    }

    public function test_updater_auth(): void
    {
        Storage::fake();

        $webcam = Webcam::factory()->create();

        $webcamImage = WebcamUpdate::factory()->withImage()->make();
        $webcam->updates()->save($webcamImage);

        $user = User::factory()->create()->givePermissionTo([
            'plazacam-view',
            'plazacam-update',
        ]);

        $this->get(URL::signedRoute('api.webcam.view', [
            'webcam' => $webcam,
            'user' => $user,
        ]))->assertOk();

        $putUrl = URL::signedRoute('api.webcam.store', [
            'webcam' => $webcam,
            'user' => $user,
        ]);

        $this->put($putUrl, [
            'file' => UploadedFile::fake()->image('plazacam.jpg'),
        ])->assertSuccessful();
    }

    public function test_updating_images(): void
    {
        Storage::fake();

        $webcam = Webcam::factory()->create();

        $webcamImage = WebcamUpdate::factory()->withImage()->make();
        $webcam->updates()->save($webcamImage);

        $this->assertSame(1, $webcam->updates()->count());

        $user = User::factory()->create()->givePermissionTo('plazacam-update');

        $putUrl = URL::signedRoute('api.webcam.store', [
            'webcam' => $webcam,
            'user' => $user,
        ]);

        $this->put($putUrl, [])->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->put($putUrl, [
            'file' => UploadedFile::fake()->image('plazacam.png'),
        ])->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->put($putUrl, [
            'file' => $file = UploadedFile::fake()->image('plazacam.jpg'),
        ])->assertSuccessful();

        $this->assertSame(2, $webcam->updates()->count());

        $this->assertTrue(Str::endsWith(
            $webcam->refresh()->lastUpdate->path,
            $file->hashName(),
        ));
    }
}
