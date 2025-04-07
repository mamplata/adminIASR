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

    /**
     * Constructor.
     *
     * @param DeviceService $deviceService
     */
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Renders the device index page.
     *
     * @param Request $request
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $devices = $this->deviceService->getDevices($request);

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'search'  => $request->input('search'),
            'status'  => $request->input('status'),
        ]);
    }


    /**
     * Store or update a device based on the provided request data.
     *
     * This method validates the incoming request data, checks if an existing
     * device should be updated or a new one should be created, and performs
     * the update or create operation. It redirects to the devices index page
     * with a success message indicating whether a device was created or updated.
     *
     * @param CreateUpdateRequest $request The request containing validated device data.
     * @return \Illuminate\Http\RedirectResponse A redirect response with a success message.
     */

    public function store(CreateUpdateRequest $request)
    {

        $validated = $request->validated();

        // If an 'id' is provided, fetch the existing device; otherwise, it's a new device.
        $device = isset($validated['id']) ? Device::find($validated['id']) : null;

        $device = $this->deviceService->updateOrCreateDevice($validated, $device);

        $message = $device->wasRecentlyCreated ? 'Device created!' : 'Device updated!';

        return redirect()->route('devices.index')->with('success', $message);
    }

    /**
     * Destroys a device, deleting it from the database.
     *
     * @param \App\Models\Device $device The device to be deleted.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Device $device)
    {
        $this->deviceService->deleteDevice($device);

        return redirect()->route('devices.index')->with('success', 'Device deleted!');
    }


    /**
     * Activates a device, marking it as active and setting a cookie with a random name and the device's fingerprint as its value.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * If this route is reached, it means the CheckDeviceFingerprint middleware has validated the
     * device fingerprint in a cookie. This endpoint simply returns a 200 response with the
     * device status as "active".
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        // If this route is reached, it means CheckDeviceFingerprint passed
        return response()->json(['status' => 'active'], 200);
    }
}
