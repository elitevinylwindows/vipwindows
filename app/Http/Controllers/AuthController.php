<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->redirectByRole(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectByRole(Auth::user()));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect($this->redirectByRole(Auth::user()));
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:vip_users,email',
            'phone'    => 'nullable|string|max:30',
            'address'  => 'nullable|string|max:255',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:50',
            'zip'      => 'nullable|string|max:20',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = VipUser::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => 'customer',
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Redirect user based on role after login.
     */
    protected function redirectByRole($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }
        return route('customer.dashboard');
    }
}
