<?php

namespace App\Http\Requests\Devices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // Adjust as needed; returning true allows all users to use this request.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'sometimes|nullable|integer', // Allow an id if provided
            'name' => 'required|string',
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Custom error messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'              => 'Please provide the device name.',
            'status.required'            => 'The device status is required.',
            'status.in'                  => 'Invalid status value. Must be active or inactive.',
        ];
    }
}
