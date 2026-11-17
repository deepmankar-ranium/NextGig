<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class ApplyForJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role?->name === 'Job Seeker' && $this->user()->jobseeker !== null;
    }

    /**
     * A resume PDF is needed only if the seeker has none on file. The cover letter is optional.
     */
    public function rules(): array
    {
        return [
            'resume' => 'nullable|file|mimes:pdf|max:5120',
            'cover_letter' => 'nullable|string|max:5000',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $onFile = $this->user()->jobseeker->resume;

                if (! $this->hasFile('resume') && (blank($onFile) || ! Storage::disk('uploads')->exists($onFile))) {
                    $validator->errors()->add('resume', 'Add your resume (PDF) to apply.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'resume.mimes' => 'Your resume must be a PDF.',
            'resume.max' => 'Your resume must be 5 MB or smaller.',
        ];
    }
}
