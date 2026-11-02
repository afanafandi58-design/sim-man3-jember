<?php

namespace App\Http\Requests\Lms;

use App\Support\LmsDriveUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLmsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'drive_folder_url' => ['nullable', 'string', 'max:2048'],
            'share_instructions' => ['nullable', 'string', 'max:5000'],
            'manual_links_only' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $url = $this->input('drive_folder_url');
            if ($url && ! LmsDriveUrl::isValid($url)) {
                $v->errors()->add('drive_folder_url', 'URL folder Drive harus HTTPS Google Drive/Docs yang valid.');
            }
        });
    }
}
