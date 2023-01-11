<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Excel\Imports\ActivityImport;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ActivityImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return the import template for activities.
     */
    public function downloadImportFormat(): SymfonyResponse
    {
        Gate::authorize('create', Activity::class);

        return Excel::download(new ActivityImport(), 'Activity Template.xslsx');
    }
}
