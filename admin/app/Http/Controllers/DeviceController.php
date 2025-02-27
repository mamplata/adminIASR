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

    // Combined store function for both creation and update
    public function store(CreateUpdateRequest $request, Device $device = null)
    {
        $validated = $request->validated();
        $device    = $this->deviceService->updateOrCreateDevice($validated, $device);

        $message = $device->wasRecentlyCreated ? 'Device created!' : 'Device updated!';

        return redirect()->route('devices.index')->with('success', $message);
    }

    public function destroy(Device $device)
    {
        $this->deviceService->deleteDevice($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted!');
    }
}
