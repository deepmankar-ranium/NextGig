<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'exists:users,id', 'not_in:'.$this->user()->id],
            'message' => 'required|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.not_in' => 'You cannot send a message to yourself.',
        ];
    }
}
