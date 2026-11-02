<?php

namespace App\Http\Requests\Lms;

use App\Support\LmsDriveUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PublicSubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:30'],
            'drive_url' => ['required', 'string', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:500'],
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
