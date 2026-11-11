<?php

namespace App\Http\Controllers;

use App\Services\ImportJadwalService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ImportJadwalController extends Controller
{
    public function __construct(
        private ImportJadwalService $importJadwalService,
    ) {}

    public function index()
    {
        return view('import-jadwal.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'File harus berformat .xlsx.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $result = $this->importJadwalService->import($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'Gagal memproses file: '.$e->getMessage(),
                ]);
        }

        return redirect()
            ->route('import-jadwal.result')
            ->with('import_result', [
                'total' => $result->total,
                'success' => $result->success,
                'failed' => $result->failed,
                'errors' => $result->errors,
                'progress' => $result->progressPercent(),
            ]);
    }

    public function result()
    {
        $result = session('import_result');

        if (! $result) {
            return redirect()->route('import-jadwal.index');
        }

        return view('import-jadwal.result', compact('result'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        try {
            $result = $this->importJadwalService->preview($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memproses file: '.$e->getMessage()], 422);
        }

        return response()->json($result->toArray());
    }
}
