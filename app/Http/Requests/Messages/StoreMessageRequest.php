<?php

namespace App\Http\Requests\Messages;

use App\Messaging\MessagingRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'not_in:'.$this->user()->id],
            'message' => 'required|string|max:5000',
            'job_listing_id' => ['nullable', 'integer'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $rules = app(MessagingRules::class);
            $me = $this->user();
            $to = User::find($this->integer('receiver_id'));

            $block = $rules->blockState($me, $to);
            if ($block === 'you_blocked') {
                $validator->errors()->add('message', 'You blocked this person. Unblock them to send a message.');
            } elseif ($block === 'blocked_you') {
                $validator->errors()->add('message', "You can't reply to this conversation.");
            } elseif (! $rules->canContact($me, $to)) {
                $validator->errors()->add('message', 'You can message employers of posted jobs, and employers can message their applicants.');
            } elseif (! $rules->jobBelongsToEither($this->input('job_listing_id') ? $this->integer('job_listing_id') : null, $me, $to)) {
                $validator->errors()->add('job_listing_id', 'That job is not part of this conversation.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'receiver_id.not_in' => 'You cannot send a message to yourself.',
        ];
    }
}
