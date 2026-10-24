<?php

namespace App\Http\Controllers;

use App\Services\ImportKodeKelasService;
use App\Services\ImportSiswaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ImportSiswaController extends Controller
{
    public function __construct(
        private ImportKodeKelasService $importKodeKelasService,
        private ImportSiswaService $importSiswaService,
    ) {}

    public function index()
    {
        return view('import-siswa.index');
    }

    public function storeKodeKelas(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        try {
            $result = $this->importKodeKelasService->import($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal memproses file: '.$e->getMessage()]);
        }

        return redirect()
            ->route('import-siswa.index')
            ->with('import_kode_kelas_result', [
                'total' => $result->total,
                'success' => $result->success,
                'failed' => $result->failed,
                'errors' => $result->errors,
            ]);
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        try {
            $result = $this->importSiswaService->import($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withErrors(['file_siswa' => 'Gagal memproses file: '.$e->getMessage()]);
        }

        return redirect()
            ->route('import-siswa.index')
            ->with('import_siswa_result', [
                'total' => $result->total,
                'success' => $result->success,
                'failed' => $result->failed,
                'errors' => $result->errors,
            ]);
    }

    public function previewKodeKelas(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        try {
            $result = $this->importKodeKelasService->preview($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memproses file: '.$e->getMessage()], 422);
        }

        return response()->json($result->toArray());
    }

    public function previewSiswa(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        try {
            $result = $this->importSiswaService->preview($request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memproses file: '.$e->getMessage()], 422);
        }

        return response()->json($result->toArray());
    }
}
