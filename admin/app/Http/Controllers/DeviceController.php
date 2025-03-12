<?php

namespace App\Http\Controllers;

use App\Http\Requests\Devices\CreateUpdateRequest;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

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
            'search'  => $request->input('search'),
            'status'  => $request->input('status'),
        ]);
    }

    // Combined store function for both creation and update
    public function store(CreateUpdateRequest $request)
    {

        $validated = $request->validated();

        // If an 'id' is provided, fetch the existing device; otherwise, it's a new device.
        $device = isset($validated['id']) ? Device::find($validated['id']) : null;

        $device = $this->deviceService->updateOrCreateDevice($validated, $device);

        $message = $device->wasRecentlyCreated ? 'Device created!' : 'Device updated!';

        return redirect()->route('devices.index')->with('success', $message);
    }

    public function destroy(Device $device)
    {
        $this->deviceService->deleteDevice($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted!');
    }

    public function register(Request $request)
    {
        $request->validate([
            'short_code' => 'required|string'
        ]);

        // Look up the device by short_code
        $device = Device::where('short_code', $request->short_code)->first();

        if (!$device) {
            return response()->json([
                'message' => 'Invalid short code.'
            ], 403); // or 404, depending on your preference
        }

        if ($device->status === 'active') {
            return response()->json([
                'message' => 'Device is already active.'
            ], 409);
        }

        // Otherwise, activate the device
        $device->status = 'active';
        $device->save();

        // Generate a random cookie name
        $cookieName = Str::random(10);

        // Return a successful response with a set-cookie header
        return response()
            ->json([
                'success' => true,
                'device_name' => $device->name,
                'device_fingerprint' => $device->deviceFingerprint,
            ])
            ->cookie(
                $cookieName,
                $device->deviceFingerprint,
                525600 // 1 year in minutes
            );
    }

    public function status()
    {
        // If this route is reached, it means CheckDeviceFingerprint passed
        return response()->json(['status' => 'active'], 200);
    }
}
