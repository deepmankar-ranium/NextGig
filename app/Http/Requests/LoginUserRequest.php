<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow all
        // users to log in, no specific authorization needed
        return true;
    }       
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }

}
