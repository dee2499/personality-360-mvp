<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user profile and password management screen.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load('company');

        return view('profile.show', compact('user'));
    }

    /**
     * Update the user's personal details (first name and last name).
     * Email cannot be changed.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        $fullName = trim($validated['first_name'].' '.($validated['last_name'] ?? ''));

        $request->user()->update([
            'name' => $fullName,
        ]);

        return back()->with('success', 'Your name has been updated successfully.');
    }

    /**
     * Update the user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been updated successfully.');
    }
}
