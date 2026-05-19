<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPasswordResetController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Customer/ForgotPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::broker('customers')->sendResetLink(
            $request->only('email'),
        );

        return back()->with('status', 'Si tu email está registrado, recibirás un enlace para restablecer tu contraseña.');
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('Customer/ResetPassword', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password) {
                $customer->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($customer));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('customers.login')
                ->with('status', 'Tu contraseña fue actualizada. Ya puedes ingresar.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
