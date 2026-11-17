<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-resume');
    }

    public function rules(): array
    {
        return [
            'skills' => 'present|array|max:40',
            'skills.*' => 'string|max:60',
            'years_experience' => 'required|numeric|min:0|max:60',
            'roles' => 'present|array|max:10',
            'roles.*' => 'string|max:120',
            'summary' => 'nullable|string|max:1000',
        ];
    }
}
