<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'Employer';
    }

    public function rules(): array
    {
        return [
            'application_status' => 'required|in:pending,approved,rejected',
        ];
    }
}
