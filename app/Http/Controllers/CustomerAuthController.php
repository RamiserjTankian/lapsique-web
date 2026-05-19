<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customers.portal');
        }

        return Inertia::render('Customer/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $customer = Auth::guard('customer')->user();
            $customer?->updateLastInteraction();

            return redirect()->route('customers.portal');
        }

        return back()->withErrors([
            'email' => 'Credenciales invalidas. Revisa tu email y contraseña.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customers.login');
    }
}
