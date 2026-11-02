<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Services\LmsGuruScopeService;
use Illuminate\View\View;

class LmsMyClassController extends Controller
{
    public function __construct(private LmsGuruScopeService $scope) {}

    public function index(): View
    {
        $user = $this->scope->currentUser();
        if (! $this->scope->isGuru($user) && ! $this->scope->isAdmin($user)) {
            abort(403);
        }

        $query = Jadwal::query()->with('guru')->orderBy('kelas')->orderBy('hari');
        if ($user->guru_id) {
            $query->where('guru_id', $user->guru_id);
        }

        $jadwals = $query->get();

        return view('lms.my-classes.index', compact('jadwals'));
    }
}
