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
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._%+\-]+@gmail\.com$/i',
                'unique:users'
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required'  => 'A name is required.',
            'name.string'    => 'The name must be a valid string.',
            'name.max'       => 'The name may not be greater than :max characters.',

            'email.required' => 'An email address is required.',
            'email.email'    => 'Please enter a valid email address.',
            'email.regex'    => 'The email must be a valid Gmail address (must end with @gmail.com).',
            'email.unique'   => 'This email address is already taken.',
        ];
    }
}
