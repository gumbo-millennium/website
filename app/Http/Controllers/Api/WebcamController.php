<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Webcam\Camera;
use App\Models\Webcam\Device;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebcamController extends Controller
{
    /**
     * Render the given camera, if any.
     */
    public function show(Camera $camera): StreamedResponse
    {
        $device = $camera->device;

        // Never had an upload, or not linked
        abort_unless($device, HttpResponse::HTTP_NOT_FOUND);

        // Upload expired
        abort_if($device->is_expired, HttpResponse::HTTP_FORBIDDEN);

        // Check if the file exists
        $disk = Config::get('gumbo.images.disk');
        $path = $device->path;

        // Skip if the file is not on the disk, or no file exists
        abort_unless($path && Storage::disk($disk)->exists($path), HttpResponse::HTTP_NOT_FOUND);

        // Return as inline response
        return Storage::disk($disk)->response($device->path, Str::ascii("{$camera->name}.jpg"), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'max-age=60, must-revalidate, no-transform',
            'Expires' => Date::now()->addMinute()->toRfc7231String(),
            'Last-Modified' => $device->created_at->toRfc7231String(),
        ]);
    }

    /**
     * Submits a new photo for the given device.
     */
    public function update(Request $request): HttpResponse
    {
        // Validate the request
        $request->validate([
            'device' => [
                'required',
                'string',
                'uuid',
            ],
            'name' => [
                'required',
                'string',
                'between:5,60',
            ],
            'image' => [
                'required',
                'image',
                'mimes:jpeg',
                // Maximum of 1MB
                'max:1024',
                // Require something like 4:3 between 240p and 1080p
                Rule::dimensions()
                    ->minWidth(240)
                    ->minHeight(180)
                    ->maxWidth(1920)
                    ->maxHeight(1080),
            ],
        ]);

        $device = $request->input('device');
        $name = $request->input('name');
        $image = $request->file('image');

        // Check if an entry exists for this device
        $model = Device::firstOrNew([
            'device' => $device,
            'name' => $name,
        ]);

        abort_if($model->owner?->is($request->user()) === false, HttpResponse::HTTP_FORBIDDEN);
        if (! $model->owner) {
            $model->owner()->associate($request->user());
        }

        // Store old path
        $disk = Config::get('gumbo.images.disk');
        $oldPath = $model->path;

        // Save new photo
        $model->path = $image->store(Device::STORAGE_FOLDER, $disk);
        $model->save();

        // Delete the old file
        if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
            Storage::disk($disk)->delete($oldPath);
        }

        // Return an accepted response
        return Response::noContent(HttpResponse::HTTP_ACCEPTED);
    }
}
