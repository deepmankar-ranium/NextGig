<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'Employer';
    }

    public function rules(): array
    {
        return [
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
