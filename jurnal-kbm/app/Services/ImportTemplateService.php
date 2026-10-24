<?php

namespace App\Services;

use App\Support\Import\ImportTemplateRegistry;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportTemplateService
{
    public function download(string $module): BinaryFileResponse
    {
        $filename = ImportTemplateRegistry::filename($module);
        $path = public_path('templates/'.$filename);

        if (! File::exists($path)) {
            abort(404, "Template {$filename} tidak ditemukan di public/templates/.");
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function assetUrl(string $module): string
    {
        return asset('templates/'.ImportTemplateRegistry::filename($module));
    }
}
