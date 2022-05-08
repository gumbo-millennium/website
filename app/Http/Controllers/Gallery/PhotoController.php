<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\PhotoReportRequest;
use App\Models\Gallery\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class PhotoController extends Controller
{
    public function show(Request $request, Photo $photo): HttpResponse
    {
        $this->authorize('view', $photo);

        $photo = Photo::query()
            ->withUserInteraction($request->user())
            ->with('album')
            ->find($photo->getKey());

        return Response::view('gallery.photo-show', [
            'photo' => $photo,
        ]);
    }

    public function report(Request $request, Photo $photo): HttpResponse
    {
        $this->authorize('report', $photo);

        return Response::view('gallery.photo-report', [
            'photo' => $photo,
            'reasonOptions' => $this->getReportReasons(),
        ]);
    }

    public function storeReport(PhotoReportRequest $request, Photo $photo): RedirectResponse
    {
        $this->authorize('report', $photo);

        $valid = $request->validated();
        $reason = $valid['reason'];

        if ($reason === 'other') {
            $reason = $valid['reason-text'];
        }

        $report = $photo->reports()->make([
            'reason' => $reason,
        ]);

        $report->user()->associate($request->user());
        $report->save();

        flash()->success(__('The photo has been reported succesfully'));

        return Response::redirectToRoute('gallery.album', $photo->album);
    }

    public function destroy(Request $request, Photo $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);

        $photo->delete();

        flash()->success(__('Foto is verwijderd'));

        return Response::redirectToRoute('gallery.album', [
            'album' => $photo->album,
        ]);
    }

    /**
     * @return string[]
     */
    private function getReportReasons(): array
    {
        $configOptions = Config::get('gumbo.gallery.report-reasons');

        return array_merge(
            array_combine($configOptions, $configOptions),
            ['other' => 'Anders'],
        );
    }
}
