<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|string|max:15',
            'username' => 'required|string|max:255',
            'profile_photo' => 'required|image|max:2048',
            'certificate' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'password' => 'required|string|confirmed|min:8',
        ];
    }
}
