<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Excel\Imports\ActivityImport;
use App\Excel\Imports\EnrollmentBarcodeImport;
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

    /**
     * Returns the template to replace barcodes on the activities.
     */
    public function downloadReplaceBarcodesTemplate(Activity $activity): SymfonyResponse
    {
        Gate::authorize('manage', $activity);

        return Excel::download(new EnrollmentBarcodeImport($activity), 'barcodes.xslx');
    }
}
