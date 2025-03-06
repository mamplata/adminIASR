<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Device;

class CheckDeviceFingerprint
{
    public function handle($request, $next)
    {
        $fingerprint = $request->header('X-Device-Fingerprint');

        if (!$fingerprint || !Device::where('deviceFingerprint', $fingerprint)->where('status', true)->exists()) {
            return response()->json(['error' => 'Unauthorized device'], 401);
        }

        return $next($request);
    }
}
