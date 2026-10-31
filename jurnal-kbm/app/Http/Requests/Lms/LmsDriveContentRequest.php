<?php

namespace App\Http\Requests\Lms;

use App\Support\LmsDriveUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class LmsDriveContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    protected function baseRules(bool $driveRequired = true): array
    {
        $driveRule = $driveRequired ? 'required' : 'nullable';

        return [
            'jadwal_id' => ['nullable', 'integer', 'exists:jadwals,id'],
            'kelas' => ['required', 'string', 'max:50'],
            'mapel' => ['required', 'string', 'max:100'],
            'tahun_pelajaran' => ['nullable', 'string', 'max:20'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'drive_url' => [$driveRule, 'string', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $url = $this->input('drive_url');
            if ($url && ! LmsDriveUrl::isValid($url)) {
                $v->errors()->add('drive_url', 'URL harus HTTPS Google Drive/Docs/Forms yang valid.');
            }
        });
    }
}
