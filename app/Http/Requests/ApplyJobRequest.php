<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApplyJobrequest extends FormRequest{
    public function authorize(){
        return Auth::check(); // Ensure the user is authenticated
        }

    public function rules(){
        return ['resume_text' => 'required|string',
            'cover_letter' => 'required|string',];

    }
}
