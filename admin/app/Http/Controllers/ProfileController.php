<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyNewEmailNotification;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Update the user's name immediately
        $user->name = $data['name'];

        // Check if the email has changed
        if ($data['email'] !== $user->email) {
            // Instead of updating the email directly,
            // store the new email in a temporary field (e.g., pending_email)
            $user->pending_email = $data['email'];

            // Optionally, you might want to nullify the verified timestamp now:
            // $user->email_verified_at = null;

            // Generate a signed verification URL valid for 60 minutes.
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify-new',
                now()->addMinutes(60),
                [
                    'user' => $user->id,
                    'hash' => sha1($user->pending_email),
                ]
            );

            // Send a custom notification to the pending email address
            Notification::route('mail', $user->pending_email)
                ->notify(new VerifyNewEmailNotification($verificationUrl));

            // Let the user know that a verification email was sent.
            session()->flash('status', 'A verification link has been sent to your new email address. Please check your inbox to verify the change.');
        }

        $user->save();

        return redirect()->route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (User::count() === 1) {
            return back()->withErrors(['error' => 'You cannot delete the last remaining user.']);
        }

        // Attempt to delete the user
        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
