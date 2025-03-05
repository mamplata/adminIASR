<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceService
{
    public function getDevices(Request $request)
    {
        $query = Device::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('short_code', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->paginate(5)
            ->appends($request->only(['search', 'status']))
            ->through(fn ($device) => [
                'id'                => $device->id,
                'name'              => $device->name,
                'short_code'        => $device->short_code,
                'status'            => $device->status,
                'deviceFingerprint' => $device->deviceFingerprint,
            ]);
    }

    public function updateOrCreateDevice(array $data, Device $device = null)
    {
        if (!$device) { // Creating a new device
            // Ensure unique short_code
            do {
                $data['short_code'] = strtoupper(\Illuminate\Support\Str::random(6));
            } while (Device::where('short_code', $data['short_code'])->exists());

            // Generate unique deviceFingerprint (UUID)
            $data['deviceFingerprint'] = (string) \Illuminate\Support\Str::uuid();
        } else { // Updating an existing device
            unset($data['short_code'], $data['deviceFingerprint']); // Prevent changes
        }

        return Device::updateOrCreate(
            ['id' => $device?->id],
            $data
        );
    }

    public function deleteDevice(Device $device)
    {
        return $device->delete();
    }
}
