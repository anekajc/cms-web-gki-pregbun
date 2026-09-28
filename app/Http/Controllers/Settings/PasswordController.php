<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\Username;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/password', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $wasForced = $user->must_change_password;
        $needsUsername = $user->username === null;

        // An existing account that only lacks a username gets a username-only
        // form; everyone else (including new users) changes their password here.
        $usernameOnly = $needsUsername && ! $wasForced;

        $rules = [];

        if ($needsUsername) {
            $request->merge(['username' => Username::normalize($request->input('username'))]);
            $rules['username'] = Username::rules($user->id);
        }

        if (! $usernameOnly) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', Password::defaults(), 'confirmed'];
        }

        $validated = $request->validate($rules, [
            ...Username::messages(),
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            ...($needsUsername ? ['username' => $validated['username']] : []),
            ...($usernameOnly ? [] : [
                'password' => Hash::make($validated['password']),
                'must_change_password' => false,
            ]),
        ]);

        // A held user was locked to this page; send them into the app once done.
        return $wasForced || $needsUsername ? redirect()->route('dashboard') : back();
    }
}
