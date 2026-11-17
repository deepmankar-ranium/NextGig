<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class DestroyTagRequest extends FormRequest
{
    /**
     * Every requested tag must pass TagPolicy::delete; one forbidden tag blocks the batch.
     * Unknown ids are left to validation.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->role?->name !== 'Employer') {
            return false;
        }

        $ids = array_filter((array) $this->input('tags', []), 'is_numeric');

        return Tag::whereIn('id', $ids)->get()->every(fn (Tag $tag) => $user->can('delete', $tag));
    }

    public function rules(): array
    {
        return [
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
