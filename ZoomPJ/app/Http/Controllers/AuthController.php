<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $userId = DB::transaction(function () use ($data) {
            DB::table('users')->orderBy('user_id')->lockForUpdate()->get();

            $userId = ((int) DB::table('users')->max('user_id')) + 1;

            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => $data['name'],
                'surname' => $data['surname'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $userId;
        });

        if (! Auth::loginUsingId($userId)) {
            throw new \RuntimeException('The new account was created but could not be signed in.');
        }

        $request->session()->regenerate();

        return redirect()->route('bookings.index');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('ui.invalid_credentials')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $destination = Auth::user()?->role === 'admin'
            ? route('admin.dashboard')
            : route('bookings.index');

        return redirect($destination);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
