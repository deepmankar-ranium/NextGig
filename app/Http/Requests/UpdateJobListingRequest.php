<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // We'll authorize in the controller
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'employer_id' => 'required|exists:employers,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
