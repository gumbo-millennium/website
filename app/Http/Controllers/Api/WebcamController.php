<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class WebcamController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed');
    }

    /**
     * Renders a given webcam, unless it's not present or older than 3 hours.
     */
    public function show(?string $user, Webcam $webcam)
    {
        // Find user
        $user = User::find($user) ?? new User();

        // Check permissions
        abort_unless($user->hasPermissionTo('plazacam-view'), HttpResponse::HTTP_FORBIDDEN);

        // Check if expired
        abort_if($webcam->is_expired, HttpResponse::HTTP_NOT_FOUND, 'No recent webcam image available', [
            'Retry-After' => Date::parse('next hour')->toRfc7231String(),
        ]);

        // Get update
        $update = $webcam->lastUpdate;

        // Check if found
        abort_if(Storage::missing($update->path), HttpResponse::HTTP_INTERNAL_SERVER_ERROR, 'Webcam image seems lost', [
            'Retry-After' => Date::parse('next hour')->toRfc7231String(),
        ]);

        // Send as file
        return Storage::response($update->path, Str::ascii("{$update->name}.jpg"), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'max-age=60, must-revalidate, no-transform',
            'Expires' => Date::now()->addMinute()->toRfc7231String(),
            'Last-Modified' => $update->created_at->toRfc7231String(),
        ]);
    }

    /**
     * Updates camera images using updates, requires jpeg uploads and proper rights.
     */
    public function store(Request $request, ?string $user, Webcam $webcam)
    {
        // Find user
        $user = User::find($user) ?? new User();

        // Check permissions
        abort_unless($user->hasPermissionTo('plazacam-update'), HttpResponse::HTTP_FORBIDDEN);

        // Make sure file is present
        abort_unless($request->hasFile('file'), HttpResponse::HTTP_BAD_REQUEST, 'Expected a file on [file], but none was found');

        // Make sure the file is valid
        $file = $request->file('file');
        $fileMime = $file->getMimeType();

        // Make sure the image is a jpg
        abort_unless($fileMime === 'image/jpeg', HttpResponse::HTTP_BAD_REQUEST, "Expected a JPEG image, got [{$fileMime}].");

        // Create update
        $webcam->updates()->create([
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'path' => $file->store(WebcamUpdate::STORAGE_LOCATION),
        ]);

        // Return accepted header
        return Response::noContent(HttpResponse::HTTP_ACCEPTED);
    }
}
