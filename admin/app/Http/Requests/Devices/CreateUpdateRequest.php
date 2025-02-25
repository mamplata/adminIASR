<?php

namespace App\Http\Requests\Devices;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // Adjust as needed; returning true allows all users to use this request.
        return true;
    }

    public function rules()
    {
        return [
            'name'              => 'required|string',
            'machineId'         => 'required|string',
            'hardwareUID'       => 'required|string',
            'MACAdress'         => 'required|string',
            'deviceFingerprint' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required'              => 'Please provide the device name.',
            'machineId.required'         => 'The machine ID is required.',
            'hardwareUID.required'       => 'Hardware UID cannot be empty.',
            'MACAdress.required'         => 'A valid MAC address is required.',
            'deviceFingerprint.required' => 'The device fingerprint must be provided.',
        ];
    }
}
