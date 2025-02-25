<?php

namespace App\Http\Controllers;

use App\Http\Requests\Devices\CreateUpdateRequest;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index(Request $request)
    {
        $devices = $this->deviceService->getDevices($request);

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'search'  => $request->input('search')
        ]);
    }

    public function store(CreateUpdateRequest $request)
    {
        $validated = $request->validated();
        $this->deviceService->createDevice($validated);

        return redirect()->route('devices.index')->with('success', 'Device created!');
    }

    public function update(CreateUpdateRequest $request, Device $device)
    {
        $validated = $request->validated();
        $this->deviceService->updateDevice($device, $validated);

        return redirect()->route('devices.index')->with('success', 'Device updated!');
    }

    public function destroy(Device $device)
    {
        $this->deviceService->deleteDevice($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted!');
    }
}
