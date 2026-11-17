<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationStatusRequest extends FormRequest
{
    /**
     * Only the employer who owns the application's job (see JobApplicationPolicy::update).
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('application'));
    }

    public function rules(): array
    {
        return [
            'application_status' => 'required|in:pending,approved,rejected',
        ];
    }
}
