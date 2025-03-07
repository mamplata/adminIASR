<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Device;

class CheckDeviceFingerprint
{
    public function handle($request, Closure $next)
    {
        $authorized = false;

        // Loop through each cookie in the request
        foreach ($request->cookies as $cookieValue) {
            if (Device::where('deviceFingerprint', $cookieValue)
                ->where('status', 'active')
                ->exists()
            ) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            return response()->json(['error' => 'Unauthorized device'], 401);
        }

        return $next($request);
    }
}
