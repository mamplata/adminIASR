<?php

namespace App\Http\Requests\Announcements;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'department'        => 'required|string',
            'publisher'         => 'required|string',
            'type'              => 'required|in:text,image',
            'publication_date'  => 'required|date',
            'content'           => 'required', // For text announcements, JSON is expected.
        ];

        // If the announcement type is image and a new file is provided, add file-specific validations.
        if ($this->type === 'image' && $this->hasFile('content')) {
            $rules['content'] = 'required|file|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'department.required'       => 'Department field is required.',
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
