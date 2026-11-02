<?php

namespace App\Http\Requests\Lms;

class UpdateLmsQuizRequest extends LmsDriveContentRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->baseRules(true), [
            'due_at' => ['nullable', 'date'],
        ]);
    }
}
