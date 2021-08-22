<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\Avg\CreateUserDataExport;
use App\Models\DataExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\Flash\Flash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $exports = DataExport::query()
            ->withTrashed()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Response::view('account.export.index', [
            'exports' => $exports,
        ])->withHeaders([
            'Cache-Control' => 'no-cache, no-store',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $export = DataExport::create([
            'user_id' => $request->user()->id,
        ]);

        CreateUserDataExport::dispatchAfterResponse($export);

        flash()->success(
            __('A data export has been requested. It may take a while for it to be ready.'),
        );

        return Response::redirectToRoute('account.export.show', [$export->id, $export->token]);
    }

    /**
     * Displays the data export, but only if the user is the owner.
     */
    public function show(Request $request): HttpResponse
    {
        $user = $request->user();

        $export = DataExport::query()
            ->withTrashed()
            ->with(['user'])
            ->where('user_id', $user->id)
            ->where('token', $request->token)
            ->findOrFail($request->id);

        return Response::view('account.export.show', [
            'export' => $export,
        ])->withHeaders([
            'Cache-Control' => 'no-cache, no-store',
        ]);
    }

    /**
     * Downlaads the data export, if it's still valid and the user is the owner.
     */
    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();

        $export = DataExport::query()
            ->withTrashed()
            ->with(['user'])
            ->where('user_id', $user->id)
            ->where('token', $request->token)
            ->whereNotNull('path')
            ->findOrFail($request->id);

        abort_if($export->is_expired, 410, __('This export is no longer available.'));

        return Storage::disk('local')->download($export->path, $export->file_name, [
            'Cache-Control' => 'no-cache, no-store, no-transform',
            'Max-Age' => 0,
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 UTC',
        ]);
    }
}
