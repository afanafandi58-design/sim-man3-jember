<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalChangeLog;
use Illuminate\Http\Request;

class JadwalChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $guruId = $request->integer('guru_id') ?: null;
        $action = $request->get('action');

        $logs = JadwalChangeLog::query()
            ->with(['guru', 'user'])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $gurus = Guru::query()->orderBy('nama')->get(['id', 'nama']);

        return view('jadwal-changes.index', compact('logs', 'gurus', 'guruId', 'action'));
    }
}
