<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * Verify a new email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyNewEmail(Request $request, User $user, $hash)
    {
        // Validate the signed URL and that the hash matches the pending email.
        if (! $request->hasValidSignature() || sha1($user->pending_email) !== $hash) {
            abort(403, 'Invalid or expired verification link.');
        }

        // Update the user's email and mark it as verified.
        $user->email = $user->pending_email;
        $user->pending_email = null;
        $user->email_verified_at = now();
        $user->save();

        return redirect('/profile')->with('status', 'Your email address has been updated and verified.');
    }
}
