<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyForJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'Job Seeker';
    }

    public function rules(): array
    {
        return [
            'resume_text' => 'required|string',
            'cover_letter' => 'required|string',
        ];
    }
}
