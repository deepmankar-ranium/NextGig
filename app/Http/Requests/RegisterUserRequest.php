<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class RegisterUserRequest extends FormRequest{

    public function authorize(): bool
    {
        // Allow all users to register, no specific authorization needed
        return true;

    }

    public function rules(): array
    {
        return [
           'full_name' => 'required|string|max:255',
             'email' => 'required|email|unique:users',
             'password' => 'required|min:8|confirmed',
             'name' => 'nullable|string|max:255', // Employer name
             'address' => 'nullable|string|max:255', // Employer address
             'phone' => 'nullable|string|max:20', // Employer phone
             'description' => 'nullable|string', // Employer description
        ];
    }

}
