<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobListingRequest extends FormRequest
{
    /**
     * Only the owner may update, and the listing cannot be moved to another employer.
     */
    public function authorize(): bool
    {
        $jobListing = $this->route('jobListing');

        return $this->user()->can('update', $jobListing)
            && (int) $this->input('employer_id') === $jobListing->employer_id;
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
