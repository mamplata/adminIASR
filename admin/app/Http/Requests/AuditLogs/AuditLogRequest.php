<?php

namespace App\Http\Requests\AuditLogs;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogRequest extends FormRequest
{
    public function authorize()
    {
        return true; // If you need any specific authorization, adjust it here.
    }

    public function rules()
    {
        return [
            'action' => 'nullable|string',
            'model' => 'nullable|string',
            'admin_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'End date must be equal or after the start date.',
        ];
    }
}
