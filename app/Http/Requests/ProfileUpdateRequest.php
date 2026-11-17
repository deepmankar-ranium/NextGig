<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            // Normally a small JPEG the browser has already cropped. If the browser couldn't read
            // the file (e.g. HEIC in Chrome), the original arrives and the server converts it.
            'photo' => ['nullable', 'file', 'max:25600', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif,image/heic,image/heif,image/bmp,image/x-ms-bmp'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.mimetypes' => "That file isn't a photo we can use. Try a different one.",
            'photo.max' => 'That photo is too large. Try a different one.',
        ];
    }
}
