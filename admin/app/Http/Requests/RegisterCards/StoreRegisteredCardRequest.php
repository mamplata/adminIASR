<?php

namespace App\Http\Requests\RegisterCards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegisteredCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => 'required|exists:student_infos,studentId',
            'uid'       => [
                'required',
                'string',
                Rule::unique('registered_cards', 'uid')->where(function ($query) {
                    return $query->where('studentId', '<>', $this->studentId);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'studentId.required' => 'Student ID is required.',
            'studentId.exists'   => 'Student ID does not exist in student records.',
            'uid.required'       => 'UID is required.',
            'uid.unique'         => 'This UID is already assigned to another student.',
        ];
    }
}
