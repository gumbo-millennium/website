<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FileExport;

class FileExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function download(FileExport $export)
    {
        return $export;
    }
}
