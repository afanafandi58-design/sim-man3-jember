<?php

namespace App\Http\Requests\Lms;

class StoreLmsMaterialRequest extends LmsDriveContentRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->baseRules(true);
    }
}
