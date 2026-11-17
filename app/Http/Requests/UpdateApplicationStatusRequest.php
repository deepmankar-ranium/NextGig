<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'application_status' => ['required', Rule::in(array_diff(Application::getApplicationStatuses(), [Application::STATUS_APPLIED]))],
            'reopen' => 'sometimes|boolean',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Application $application */
                $application = $this->route('application');
                $to = $this->input('application_status');

                if (! $application->canTransitionTo($to, $this->boolean('reopen'))) {
                    $validator->errors()->add('application_status', $application->application_status === $to
                        ? "The application is already {$to}."
                        : "Moving from {$application->application_status} to {$to} goes backwards. Confirm \"reopen\" to do it.");
                }
            },
        ];
    }
}
