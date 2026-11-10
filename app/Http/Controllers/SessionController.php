<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SessionController
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);
        $credentials['email'] = Str::lower($credentials['email']);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('workspace_id');

        return redirect()->route('workspaces');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
