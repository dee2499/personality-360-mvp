<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
     * Check if a username is available.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $username = trim((string) $request->query('username', ''));

        if ($username === '') {
            return response()->json([
                'available' => true,
                'message' => '',
            ]);
        }

        $taken = User::whereRaw('LOWER(username) = ?', [strtolower($username)])
            ->where('id', '!=', $request->user()->id)
            ->exists();

        return response()->json([
            'available' => ! $taken,
            'message' => $taken ? 'This username is already taken. Please choose another.' : 'Username is available!',
        ]);
    }

    /**
     * Update the user's personal details (first name, last name, username, and optional email).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($request->user()->id),
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $taken = User::whereRaw('LOWER(username) = ?', [strtolower(trim($value))])
                            ->where('id', '!=', $request->user()->id)
                            ->exists();
                        if ($taken) {
                            $fail('This username is already taken. Please choose another one.');
                        }
                    }
                },
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($request->user()->id),
            ],
            'designation' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'integer', 'min:16', 'max:100'],
        ]);

        $fullName = trim($validated['first_name'].' '.($validated['last_name'] ?? ''));

        $request->user()->update([
            'name' => $fullName,
            'username' => ! empty($validated['username']) ? trim($validated['username']) : null,
            'email' => $validated['email'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'department' => $validated['department'] ?? null,
            'division' => $validated['division'] ?? null,
            'age' => $validated['age'] ?? null,
        ]);

        return back()->with('success', 'Your profile details have been updated successfully.');
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
