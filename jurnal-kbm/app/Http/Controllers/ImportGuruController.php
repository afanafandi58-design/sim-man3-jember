<?php

namespace App\Http\Controllers;

use App\Services\ImportGuruService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ImportGuruController extends Controller
{
    public function __construct(
        private ImportGuruService $importGuruService,
    ) {}

    public function index()
    {
        return view('import-guru.index');
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
            $result = $this->importGuruService->import($request->file('file'));
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
            ->route('import-guru.result')
            ->with('import_result', [
                'total' => $result->total,
                'success' => $result->success,
                'failed' => $result->failed,
                'errors' => $result->errors,
                'progress' => $result->progressPercent(),
                'accounts_provisioned' => $result->accountsProvisioned,
                'accounts_updated' => $result->accountsUpdated,
            ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        try {
            $result = $this->importGuruService->preview($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memproses file: '.$e->getMessage()], 422);
        }

        return response()->json($result->toArray());
    }

    public function result()
    {
        $result = session('import_result');

        if (! $result) {
            return redirect()->route('import-guru.index');
        }

        return view('import-guru.result', compact('result'));
    }
}
