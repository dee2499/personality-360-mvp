<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Show the account activation and password setup screen.
     */
    public function show(string $token): View|RedirectResponse
    {
        $user = User::where('invitation_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has already been used.');
        }

        if ($user->invitation_accepted_at !== null) {
            return redirect()->route('login')
                ->with('info', 'Your account is already activated. Please log in with your password.');
        }

        return view('auth.invitation', compact('user', 'token'));
    }

    /**
     * Set password and activate the user account.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $user = User::where('invitation_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'invitation_accepted_at' => now(),
            'invitation_token' => null,
        ]);

        Auth::login($user);

        $companyName = $user->company?->name ?? 'your organization';

        return redirect()->route('participant.assessments.index')
            ->with('success', "Welcome to {$companyName}! Your account is now active and your password is set.");
    }
}
