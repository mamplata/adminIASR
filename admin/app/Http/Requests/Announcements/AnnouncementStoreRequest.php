<?php

namespace App\Http\Requests\Announcements;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'departments'        => 'required|string',
            'publisher'         => 'required|string',
            'type'              => 'required|in:text,image',
            'publication_date'  => 'required|date',
            'content'           => 'required', // For text, JSON is expected; for image, a file is expected.
        ];
    }

    public function messages()
    {
        return [
            'departments.required'       => 'Department field is required.',
            'publisher.required'        => 'Publisher field is required.',
            'type.required'             => 'Type field is required.',
            'type.in'                 => 'Type must be either text or image.',
            'publication_date.required' => 'Publication date is required.',
            'publication_date.date'     => 'Publication date must be a valid date.',
            'content.required'          => 'Content field is required.',
        ];
    }
}
