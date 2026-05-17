<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            return redirect($this->redirectByRole(Auth::user()));
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
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:vip_users,email',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:255',
            'city'      => 'nullable|string|max:100',
            'state'     => 'nullable|string|max:50',
            'zip'       => 'nullable|string|max:20',
            'password'  => 'required|confirmed|min:8',
            'user_type' => 'required|in:customer,installer',
        ]);

        $userData = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'] ?? null,
            'city'     => $validated['city'] ?? null,
            'state'    => $validated['state'] ?? null,
            'zip'      => $validated['zip'] ?? null,
            'role'     => $validated['user_type'],
            'password' => Hash::make($validated['password']),
        ];

        // Auto-generate booking slug for installers
        if ($validated['user_type'] === 'installer') {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (VipUser::where('booking_slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $userData['booking_slug'] = $slug;
        }

        $user = VipUser::create($userData);

        Auth::login($user);
        return redirect($this->redirectByRole($user));
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
        if ($user->isInstaller()) {
            return route('installer.dashboard');
        }
        if ($user->isAdmin() || $user->isStaff()) {
            return route('admin.dashboard');
        }
        return route('customer.dashboard');
    }
}
