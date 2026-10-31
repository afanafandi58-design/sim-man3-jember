<?php

namespace App\Http\Requests\Lms;

use App\Support\LmsDriveUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLmsAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'scope' => ['required', 'in:all,kelas'],
            'kelas' => ['nullable', 'required_if:scope,kelas', 'string', 'max:50'],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:10000'],
            'drive_url' => ['nullable', 'string', 'max:2048'],
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
