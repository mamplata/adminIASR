<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Device;

class CheckDeviceFingerprint
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */

    public function handle($request, Closure $next)
    {
        $device = null;

        // Loop through each cookie in the request
        foreach ($request->cookies as $cookieValue) {
            $device = Device::where('deviceFingerprint', $cookieValue)
                ->where('status', 'active')
                ->first();

            if ($device) {
                break;
            }
        }

        if (!$device) {
            return response()->json(['error' => 'Unauthorized device'], 401);
        }

        // If this is the device status endpoint, return the device name immediately.
        if ($request->is('api/device/status')) {
            return response()->json([
                'device_name' => $device->name,
                'device_fingerprint' => $device->deviceFingerprint,
            ], 200);
        }

        // For other endpoints, attach the device name to the request.
        $request->merge(['device_name' => $device->name]);

        return $next($request);
    }
}
