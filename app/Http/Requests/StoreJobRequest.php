<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

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
            'tags.*' => 'exists:tags,id', // Ensures each tag exists in the `tags` table
            
        ];
    }
}
