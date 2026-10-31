<?php

namespace App\Http\Requests\Lms;

use App\Support\LmsDriveUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLmsSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'lms_assignment_id' => ['required', 'integer', 'exists:lms_assignments,id'],
            'siswa_id' => ['required', 'integer', 'exists:siswas,id'],
            'drive_url' => ['required', 'string', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'score' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $url = $this->input('drive_url');
            if ($url && ! LmsDriveUrl::isValid($url)) {
                $v->errors()->add('drive_url', 'URL harus HTTPS Google Drive/Docs yang valid.');
            }
        });
    }
}
