<?php

namespace App\Http\Requests;

use App\Models\JobListing;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobListingRequest extends FormRequest
{
    /**
     * Employers only, and only for their own employer profile.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->can('create', JobListing::class)
            && (int) $this->input('employer_id') === $user->employer->id;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'min_years_experience' => 'nullable|integer|min:0|max:40',
            'employer_id' => 'required|exists:employers,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
