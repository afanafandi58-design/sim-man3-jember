<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\JurnalAttachment;
use App\Services\GuruJurnalService;
use App\Services\JurnalAttachmentService;
use Illuminate\Http\Request;

class GuruJurnalAttachmentController extends Controller
{
    public function __construct(
        private GuruJurnalService $guruJurnalService,
        private JurnalAttachmentService $attachmentService,
    ) {}

    public function destroy(Jurnal $jurnal, JurnalAttachment $attachment)
    {
        $this->guruJurnalService->assertJurnalOwnership($jurnal);

        if ((int) $attachment->jurnal_id !== (int) $jurnal->id) {
            abort(404);
        }

        $this->attachmentService->delete($attachment);

        return back()->with('success', 'Lampiran foto berhasil dihapus.');
    }

    public function store(Request $request, Jurnal $jurnal)
    {
        $this->guruJurnalService->assertJurnalOwnership($jurnal);

        $request->validate([
            'lampiran' => 'required|array|min:1|max:'.JurnalAttachment::MAX_PER_JURNAL,
            'lampiran.*' => 'file|mimes:jpg,jpeg,png,webp|max:2048',
            'lampiran_caption' => 'nullable|array',
            'lampiran_caption.*' => JurnalAttachmentService::captionFieldRules(),
        ]);

        $this->attachmentService->storeMany(
            $jurnal,
            $request->file('lampiran', []),
            $request->input('lampiran_caption', [])
        );

        return back()->with('success', 'Lampiran foto berhasil diunggah.');
    }
}
