<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'identity.required' => 'NIP, email, atau akun SSO wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');
        $identity = $credentials['identity'];
        $attempt = filter_var($identity, FILTER_VALIDATE_EMAIL)
            ? ['email' => $identity, 'password' => $credentials['password'], 'is_active' => true]
            : ['nip_nik' => $identity, 'password' => $credentials['password'], 'is_active' => true];

        if (! Auth::attempt($attempt, $remember)) {
            return back()->withInput($request->only('identity'))->withErrors([
                'identity' => 'Kredensial tidak sesuai atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
