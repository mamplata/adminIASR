<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class SearchUsersRequest extends FormRequest
{
    public function authorize()
    {
        // Return true or add any logic to restrict who can use this request
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
        ];
    }
}
