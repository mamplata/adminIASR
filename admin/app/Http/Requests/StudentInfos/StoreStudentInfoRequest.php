<?php

namespace App\Http\Requests\StudentInfos;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the request is authorized, otherwise false.
     */

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'studentId'  => 'required|integer',
            'fName'      => 'required|string|max:255',
            'lName'      => 'required|string|max:255',
            'program'    => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'yearLevel'  => 'required|string|max:50',
            'image'      => 'required|string',
            'last_enrolled_at' => 'required|string|max:50',
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'studentId.required'  => 'Student ID is required.',
            'studentId.integer'   => 'Student ID must be an integer.',
            'fName.required'      => 'First name is required.',
            'lName.required'      => 'Last name is required.',
            'program.required'    => 'Program is required.',
            'department.required' => 'Department is required.',
            'yearLevel.required'  => 'Year level is required.',
            'image.required'      => 'Student image is required.',
            'last_enrolled_at.required' => 'Enrollment status is required.',
        ];
    }
}
