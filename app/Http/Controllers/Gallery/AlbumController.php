<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gallery;

use App\Enums\AlbumVisibility;
use App\Enums\PhotoVisibility;
use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\Gallery\Album;
use App\Models\Gallery\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            'member',
        ]);
    }

    public function index(Request $request): HttpResponse
    {
        $this->authorize('viewAny', Album::class);

        $albums = Album::query()
            ->forUser($request->user())
            ->withCount('photos')
            ->with('photos')
            ->get();

        return Response::view('gallery.index', [
            'albums' => $albums,
        ]);
    }

    public function create(Request $request): HttpResponse
    {
        $this->authorize('create', Album::class);

        return Response::view('gallery.album-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Album::class);

        $valid = $request->validate([
            'accept-terms' => [
                'required',
                'accepted',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $album = Album::make([
            'name' => $valid['name'],
            'description' => $valid['description'],
            'visibility' => AlbumVisibility::Private,
        ]);

        $album->user()->associate($request->user());
        $album->save();

        flash()->success(__('Your album is created, now go and add some photos!'));

        return Response::redirectToRoute('gallery.album.upload', $album);
    }

    public function show(Request $request, Album $album): HttpResponse
    {
        $this->authorize('view', $album);

        $album = Album::query()
            ->forUser($request->user())
            ->with('photos', fn ($query) => $query->visible())
            ->findOrFail($album->getKey());

        return Response::view('gallery.album-show', [
            'album' => $album,
        ]);
    }

    public function upload(Request $request, Album $album): HttpResponse
    {
        $this->authorize('upload', $album);

        $album = Album::query()
            ->forUser($request->user())
            ->findOrFail($album->getKey());

        $disk = Storage::disk(Config::get('gumbo.gallery.filepond.disk'));

        $pendingPhotos = $album
            ->allPhotos()
            ->whereHas('user', fn ($query) => $query->where('id', $request->user()->id))
            ->where('visibility', PhotoVisibility::Pending)
            ->where('path', 'LIKE', Config::get('gumbo.gallery.filepond.path') . '%')
            ->get()
            ->filter(fn (Photo $photo) => $disk->exists($photo->path))
            ->map(fn (Photo $photo) => [
                'source' => (string) $photo->id,
                'options' => [
                    'type' => 'limbo',
                    'file' => [
                        'name' => $photo->name,
                        'size' => $disk->size($photo->path),
                        'type' => $disk->mimeType($photo->path),
                    ],
                ],
            ])
            ->values();

        return Response::view('gallery.album-upload', [
            'album' => $album,
            'pendingPhotos' => $pendingPhotos,
        ]);
    }

    public function storeUpload(Request $request, Album $album): RedirectResponse
    {
        $this->authorize('upload', $album);

        $valid = $request->validate([
            'file' => [
                'required',
                'array',
            ],
        ]);

        $photos = Photo::query()
            ->whereHas('album', fn ($query) => $query->where('id', $album->id))
            ->whereHas('user', fn ($query) => $query->where('id', $request->user()->id))
            ->where('visibility', PhotoVisibility::Pending)
            ->whereIn('id', $valid['file'])
            ->get();

        $fromDisk = Storage::disk($fromDiskName = Config::get('gumbo.gallery.filepond.disk'));
        $fromPath = Config::get('gumbo.gallery.filepond.path');

        $toDisk = Storage::disk($toDiskName = Config::get('gumbo.images.disk'));
        $toPath = Str::finish(Config::get('gumbo.images.path'), '/') . 'galleries/' . $album->id . '/';

        $sameDisk = $fromDiskName === $toDiskName;

        $updatedPhotoCount = 0;

        foreach ($photos as $photo) {
            if (! Str::startsWith($photo->path, $fromPath)) {
                Log::warning("Skipping {$photo->path} because it doesn't start with {$fromPath}, likely not uploaded");

                continue;
            }

            $newPhotoPath = $toPath . '/' . basename($photo->path);

            $moveOk = $sameDisk
                ? $toDisk->move($photo->path, $newPhotoPath)
                : $toDisk->put($newPhotoPath, $fromDisk->read($photo->path));

            if (! $moveOk) {
                Log::warning("Failed to move file from {$fromDiskName}:{$photo->path} to {$toDiskName}:{$newPhotoPath}");

                continue;
            }

            if ($moveOk && ! $sameDisk) {
                Log::info("Moved photo to {$toDiskName}, deleting {$fromDiskName}:{$photo->path}");
                $fromDisk->delete($photo->path);
            }

            $photo->path = $newPhotoPath;

            $photo->visibility = PhotoVisibility::Visible;
            $photo->save();

            Log::debug("Updated photo {$photo->id} to {$photo->path}");

            $updatedPhotoCount++;
        }

        flash()->success(__('Successfully uploaded :success of :total photos', [
            'success' => $updatedPhotoCount,
            'total' => $photos->count(),
        ]));

        return Response::redirectToRoute('gallery.album', $album);
    }

    public function edit(Request $request, Album $album): HttpResponse
    {
        $this->authorize('update', $album);

        $photos = $this->getEditablePhotos($request, $album);

        return Response::view('gallery.album-edit', [
            'photos' => $photos,
            'album' => $album,
        ]);
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $this->authorize('update', $album);

        DB::beginTransaction();

        $photos = $this->getEditablePhotos($request, $album);

        $deleteCount = 0;
        $updateCount = 0;
        $visibilityCount = 0;

        foreach ($photos as $photo) {
            $newDescription = $request->input("photo.{$photo->id}.description");
            $newVisibility = $request->input("photo.{$photo->id}.visible") == 'visible'
                ? PhotoVisibility::Visible
                : PhotoVisibility::Hidden;

            $shouldDelete = $request->input("photo.{$photo->id}.delete") === 'delete';

            if ($shouldDelete) {
                $photo->delete();
                $deleteCount++;

                continue;
            }

            if ($newDescription != $photo->description) {
                $photo->description = Str::of($newDescription)->limit(200);
                $updateCount++;
            }

            if ($newVisibility !== $photo->visibility) {
                $photo->visibility = $newVisibility;
                $visibilityCount++;
            }

            $photo->save();
        }

        $message = Collection::make()
            ->push($updateCount ? __('Updated :count photos', ['count' => $updateCount]) : null)
            ->push($visibilityCount ? __('Changed visibility of :count photos', ['count' => $visibilityCount]) : null)
            ->push($deleteCount ? __('Deleted :count photos', ['count' => $deleteCount]) : null)
            ->filter()
            ->join(', ', ' ' . __('and') . ' ');

        if ($message) {
            flash()->success($message);
        } else {
            flash()->info(__('No changes have been made, album left unchanged'));
        }

        DB::commit();

        return Response::redirectToRoute('gallery.album', $album);
    }

    private function getEditablePhotos(Request $request, Album $album): Collection
    {
        $user = $request->user();

        return $album
            ->allPhotos()
            ->unless(
                $user->hasPermissionTo('gallery-manage'),
                fn ($query) => $query->whereHas(
                    'user',
                    fn ($query) => $query->where('id', $user->id),
                ),
            )
            ->editable()
            ->with(['user'])
            ->get();
    }
}
