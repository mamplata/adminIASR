<?php

namespace App\Http\Requests\Announcements;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementStoreRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * If the announcement type is 'text', this function attempts to decode the 'content'
     * field from JSON or object format into an array. It then merges the 'title' and 'body'
     * fields from the content into the request data to facilitate validation.
     */

    protected function prepareForValidation()
    {
        if ($this->input('type') === 'text') {
            $content = $this->input('content');

            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content = $decoded;
                }
            } elseif (is_object($content)) {
                $content = (array) $content;
            }

            if (is_array($content)) {
                $this->merge([
                    'content' => [
                        'title' => $content['title'] ?? null,
                        'body'  => $content['body'] ?? null,
                    ],
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The rules are dependent on the 'type' field value. If the type is 'text',
     * the request must contain a 'title' and 'body' field in the 'content' field.
     * If the type is 'image', the request must contain a file upload in the
     * 'content' field.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'departments'       => 'required|string',
            'publisher'         => 'required|string',
            'type'              => 'required|in:text,image',
            'publication_date'  => 'required|date',
            'end_date'          => 'required|date|after_or_equal:publication_date',
        ];

        if ($this->input('type') === 'text') {
            // Validate the merged fields for text type.
            $rules['content.title'] = 'required';
            $rules['content.body']  = 'required';
        } else {
            // For image announcements, require a file upload.
            $rules['content'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom message for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'departments.required'      => 'Department field is required.',
            'publisher.required'        => 'Publisher field is required.',
            'type.required'             => 'Type field is required.',
            'type.in'                   => 'Type must be either text or image.',
            'publication_date.required' => 'Publication date is required.',
            'publication_date.date'     => 'Publication date must be a valid date.',
            'content.required'          => 'Content field is required.',
            'content.title.required'    => 'Title is required for text announcements.',
            'content.body.required'     => 'Body is required for text announcements.',
        ];
    }
}
