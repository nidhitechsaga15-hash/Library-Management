<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // If user wants welcome page, use welcome route
        // For /login, show unified login page
        if (request()->is('login')) {
            return view('auth.login');
        }
        return view('auth.welcome');
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function showStudentLogin()
    {
        return view('auth.student-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');
        $expectedRole = $request->input('role');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ]);
            }

            // Check if user is trying to login with correct role (if role is specified)
            if ($expectedRole && $user->role !== $expectedRole) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Invalid login credentials for this portal.',
                ]);
            }

            // Auto-detect role and redirect accordingly
            return redirect()->intended($this->redirectTo($user));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out successfully.');
    }

    protected function redirectTo($user)
    {
        if ($user->isAdmin()) {
            return '/admin/dashboard';
        } elseif ($user->isStaff()) {
            return '/staff/dashboard';
        } else {
            return '/student/dashboard';
        }
    }
}
