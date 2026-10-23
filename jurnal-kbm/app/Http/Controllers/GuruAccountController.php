<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Services\GuruAccountService;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GuruAccountController extends Controller
{
    public function __construct(
        private GuruAccountService $guruAccountService,
    ) {}

    public function index(Request $request): View
    {
        $gurus = $this->guruAccountService->paginate($request->only(['nama', 'nip', 'mapel', 'status']));
        $mapels = $this->guruAccountService->mapelOptions();

        return view('guru-accounts.index', compact('gurus', 'mapels'));
    }

    public function store(Guru $guru): RedirectResponse
    {
        $result = $this->guruAccountService->createAccount($guru);

        return redirect()
            ->route('guru-accounts.index')
            ->with('success', "Akun guru untuk {$guru->displayName()} berhasil dibuat.")
            ->with('generated_credentials', [
                'kode_guru' => $guru->kode_guru,
                'email' => $result['email'],
                'password' => $result['password'],
                'nama' => $guru->displayName(),
            ]);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $password = $this->guruAccountService->resetPassword($user);
        $user->load('guru');

        return redirect()
            ->route('guru-accounts.index')
            ->with('success', "Password akun {$user->email} berhasil direset.")
            ->with('generated_credentials', [
                'kode_guru' => $user->guru?->kode_guru,
                'email' => $user->email,
                'password' => $password,
                'nama' => $user->guru?->displayName() ?? $user->name,
            ]);
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->guruAccountService->deactivate($user);

        return redirect()
            ->route('guru-accounts.index')
            ->with('success', "Akun {$user->email} berhasil dinonaktifkan.");
    }

    public function activate(User $user): RedirectResponse
    {
        $this->guruAccountService->activate($user);

        return redirect()
            ->route('guru-accounts.index')
            ->with('success', "Akun {$user->email} berhasil diaktifkan.");
    }

    public function cetak(Request $request): Response
    {
        $accounts = $this->guruAccountService->getPrintableAccounts(
            $request->input('guru_ids')
        );

        $pdf = PDF::loadView('guru-accounts.pdf', [
            'accounts' => $accounts,
            'tahunPelajaran' => \App\Services\GuruService::DEFAULT_TAHUN_PELAJARAN,
        ]);

        return $pdf->stream('Akun-Guru-MAN3-Jember.pdf');
    }
}
