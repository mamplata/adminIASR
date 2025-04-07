<?php

namespace App\Http\Requests\Announcements;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The rules are dependent on the 'type' field value. If the type is 'text',
     * the request must contain a 'title' and 'body' field in the 'content' field.
     * If the type is 'image', the request must contain a file upload in the
     * 'content' field. If a new file is provided for an image announcement,
     * additional validations are applied.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'departments'        => 'required|string',
            'publisher'         => 'required|string',
            'type'              => 'required|in:text,image',
            'publication_date'  => 'required|date',
            'end_date'          => 'required|date|after_or_equal:publication_date',
            'content'           => 'required', // For text announcements, JSON is expected.
        ];

        // If the announcement type is image and a new file is provided, add file-specific validations.
        if ($this->type === 'image' && $this->hasFile('content')) {
            $rules['content'] = 'required|file|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * These messages correspond to validation rules defined in the rules method,
     * providing user-friendly messages for validation failures. They cover required
     * fields, valid data types, and constraints specific to images, such as file type
     * and size limitations.
     *
     * @return array<string, string>
     */

    public function messages()
    {
        return [
            'departments.required'       => 'Department field is required.',
            'publisher.required'        => 'Publisher field is required.',
            'type.required'             => 'Type field is required.',
            'type.in'                   => 'Type must be either text or image.',
            'publication_date.required' => 'Publication date is required.',
            'publication_date.date'     => 'Publication date must be a valid date.',
            'content.required'          => 'Content field is required.',
            'content.file'              => 'Content must be a valid file.',
            'content.mimes'             => 'Content must be an image of type jpeg, png, or jpg.',
            'content.max'               => 'Content must not be greater than 2MB.',
        ];
    }
}
