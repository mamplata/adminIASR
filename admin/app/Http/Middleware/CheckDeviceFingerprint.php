<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class CheckDeviceFingerprint
{
    public function handle($request, Closure $next)
    {
        // Retrieve from the cookie instead of the header
        $fingerprint = $request->cookie('deviceFingerprint');

        $deviceIsValid = Device::where('deviceFingerprint', $fingerprint)
            ->where('status', 'active')
            ->exists();

        if (!$fingerprint || !$deviceIsValid) {
            return response()->json(['error' => 'Unauthorized device'], 401);
        }

        return $next($request);
    }
}
