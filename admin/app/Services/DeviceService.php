<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceService
{
    public function getDevices(Request $request)
    {
        $query = Device::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        return $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($device) => [
                'id'                => $device->id,
                'name'              => $device->name,
                'machineId'         => $device->machineId,
                'hardwareUID'       => $device->hardwareUID,
                'MACAdress'         => $device->MACAdress,
                'deviceFingerprint' => $device->deviceFingerprint,
            ]);
    }

    public function updateOrCreateDevice(array $data, Device $device = null)
    {
        if ($device) {
            return Device::updateOrCreate(
                ['id' => $device->id],
                $data
            );
        }

        return Device::updateOrCreate(
            [
                'machineId'         => $data['machineId'],
                'hardwareUID'       => $data['hardwareUID'],
            ],
            $data
        );
    }


    public function deleteDevice(Device $device)
    {
        return $device->delete();
    }
}
