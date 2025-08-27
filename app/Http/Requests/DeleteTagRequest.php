<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->isEmployer();
    }

    public function rules(): array
    {
        return [
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id', // Ensure each tag ID exists in the tags table
        ];
    }
}
