<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Excel\Imports\ActivityImport;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ImportTemplateController extends Controller
{
    private const FORMATS = [
        'activity' => [
            'model' => Activity::class,
            'export' => ActivityImport::class,
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return the import template for activities.
     */
    public function downloadTemplate(string $format): SymfonyResponse
    {
        abort_unless(Arr::has(self::FORMATS, $format), 404);

        $formatData = self::FORMATS[$format];

        abort_unless(Gate::allows('manage', $formatData['model']), 403);

        $className = $formatData['export'];

        return Excel::download(new $className(), "template-{$format}.ods");
    }
}
