<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gallery;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\PhotoReportRequest;
use App\Models\Gallery\Photo;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PhotoController extends Controller
{
    private const LARAVEL_NO_TEMPORARY_URL_SUPPORT = 'This driver does not support creating temporary URLs.';

    public function download(Photo $photo): HttpFoundationResponse
    {
        $this->authorize('view', $photo);

        $safeName = (string) Str::of($photo->name)->ascii()->replace('"', '\'');

        try {
            Storage::disk(Config::get('gumbo.images.disk'))->temporaryUrl($photo->path, Date::now()->addMinutes(5), [
                'ResponseContentDisposition' => "attachment; filename=\"{$safeName}\"",
            ]);
        } catch (Exception $exception) {
            if ($exception->getMessage() !== self::LARAVEL_NO_TEMPORARY_URL_SUPPORT) {
                Log::error('Failed to generate temporary URL for photo', [
                    'photo' => $photo,
                    'exception' => $exception,
                ]);
            }

            return Storage::disk(Config::get('gumbo.images.disk'))
                ->download($photo->path, $safeName)
                ->setCache([
                    'no_store' => true,
                    'must_revalidate' => true,
                ]);
        }
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
