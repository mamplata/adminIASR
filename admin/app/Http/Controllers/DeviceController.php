<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::orderBy('created_at', 'desc');

        // Optional search filtering across key columns
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $devices = $query->paginate(5)
            ->appends(['search' => $request->input('search')])
            ->through(fn($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'machineId' => $device->machineId,
                'hardwareUID' => $device->hardwareUID,
                'MACAdress' => $device->MACAdress,
                'deviceFingerprint' => $device->deviceFingerprint,
            ]);

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'search' => $request->input('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'machineId' => 'required|string',
            'hardwareUID' => 'required|string',
            'MACAdress' => 'required|string',
            'deviceFingerprint' => 'required|string',
        ]);

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device created!');
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'machineId' => 'required|string',
            'hardwareUID' => 'required|string',
            'MACAdress' => 'required|string',
            'deviceFingerprint' => 'required|string',
        ]);

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Device updated!');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted!');
    }
}
