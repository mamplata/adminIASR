<?php

namespace App\Http\Requests\RegisterCards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegisteredCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     *
     * The validation rules ensure that:
     * - 'studentId' is required and must exist in the 'student_infos' table.
     * - 'uid' is required, must be a string, and must be unique in the 'registered_cards' table,
     *   except for records with the same 'studentId' as this request.
     */

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

    /**
     * Return validation error messages.
     *
     * This method returns custom validation error messages for the fields
     * 'studentId' and 'uid'.
     *
     * @return array<string, string>
     */
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
