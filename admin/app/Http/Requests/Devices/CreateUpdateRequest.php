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
            'machineId'         => 'required|string|unique:devices,machineId',
            'hardwareUID'       => 'required|string|unique:devices,hardwareUID',
            'MACAdress'         => 'required|string|unique:devices,MACAdress',
            'deviceFingerprint' => 'required|string|unique:devices,deviceFingerprint',
        ];
    }

    public function messages()
    {
        return [
            'name.required'              => 'Please provide the device name.',
            'machineId.required'         => 'The machine ID is required.',
            'machineId.unique'           => 'This machine ID is already in use.',
            'hardwareUID.required'       => 'Hardware UID cannot be empty.',
            'hardwareUID.unique'         => 'This hardware UID is already registered.',
            'MACAdress.required'         => 'A valid MAC address is required.',
            'MACAdress.unique'           => 'This MAC address is already in use.',
            'deviceFingerprint.required' => 'The device fingerprint must be provided.',
            'deviceFingerprint.unique'   => 'This device fingerprint is already taken.',
        ];
    }
}
