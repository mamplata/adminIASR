<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'A name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'An email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',

            'password.required' => 'A password is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least :min characters long.',
            'password.regex' => 'The password must contain at least one letter and one number.',

        ];
    }
}
