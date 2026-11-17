<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;

class UploadResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-resume');
    }

    public function rules(): array
    {
        return [
            'resume' => 'required|file|mimes:pdf|max:5120',
        ];
    }
}
