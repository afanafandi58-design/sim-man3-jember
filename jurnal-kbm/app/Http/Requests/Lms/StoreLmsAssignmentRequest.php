<?php

namespace App\Http\Requests\Lms;

class StoreLmsAssignmentRequest extends LmsDriveContentRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->baseRules(false), [
            'due_at' => ['nullable', 'date'],
            'max_score' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);
    }
}
