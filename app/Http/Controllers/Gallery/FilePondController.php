<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gallery;

use App\Enums\PhotoVisibility;
use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FilePondController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle a Filepond 'process', which is an upload. Will be saved as pending and stored in a special
     * director that's regularly pruned.
     */
    public function handleProcess(Request $request, Album $album): HttpResponse
    {
        $this->authorize('upload', $album);

        // Check request
        $request->validate([
            'file' => [
                'required',
                'file',
                'image',
                'max:' . Config::get('gumbo.gallery.max_photo_size'),
            ],
        ]);

        // Get exif info about taken date
        $takenDate = null;
        if ($data = exif_read_data($request->file('file')->path()) !== false) {
            if ($exifDate = $data['DateTimeOriginal'] ?? null) {
                try {
                    $takenDate = Date::parse($exifDate);
                } catch (InvalidDateException) {
                    // Ignore
                }
            }
        }

        // Store file in storage
        $storagePath = $request->file('file')->store(
            Config::get('gumbo.gallery.filepond.path'),
            Config::get('gumbo.gallery.filepond.disk'),
        );

        // Make photo on album
        $photo = $album->photos()->make([
            'path' => $storagePath,
            'name' => $request->file('file')->getClientOriginalName(),
            'visibility' => PhotoVisibility::Pending,
            'taken_at' => $takenDate,
        ]);

        // Associate with user
        $photo->user()->associate($request->user());

        // Save photo
        $photo->save();

        // Return the ID as plain text
        return Response::make($photo->id)
            ->withHeaders([
                'Content-Type' => 'text/plain',
            ]);
    }

    /**
     * Handle when the user issues a revert of an upload. Can only be
     * used if the file isn't published.
     */
    public function handleRevert(Request $request, Album $album): HttpResponse
    {
        // Build valid response from raw body contents
        $valid = Validator::make([
            'id' => $request->getContent(),
        ], [
            'id' => [
                'required',
                'integer',
            ],
        ])->validate();

        // Get photo with the given ID
        $photo = Photo::query()
            ->whereHas('album', fn (Builder $query) => $query->where('id', $album->id))
            ->whereHas('user', fn (Builder $query) => $query->where('id', $request->user()->id))
            ->with(['album', 'user'])
            ->findOrFail($valid['id']);

        // $this->authorize('delete', $photo);
        // $this->authorize('upload', $photo->album);

        // Check if the photo is pending
        abort_unless(
            $photo->visibility === PhotoVisibility::Pending,
            HttpResponse::HTTP_CONFLICT,
        );

        // Check if the photo is still in the Filepond directory. If it's moved
        // it was probably processed but not yet transitioned from Pending
        abort_unless(
            Str::startsWith($photo->path, Config::get('gumbo.gallery.filepond.path')),
            HttpResponse::HTTP_CONFLICT,
        );

        // Delete model
        $photo->forceDelete();

        // Delete from disk
        $disk = Storage::disk(Config::get('gumbo.gallery.filepond.disk'));
        if ($disk->exists($photo->path)) {
            $disk->delete($photo->path);
        }

        // OK
        return Response::noContent(HttpResponse::HTTP_OK);
    }
}
