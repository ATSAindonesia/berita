<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the password-only login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle the password login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        $users = User::all();
        $matchedUser = null;

        foreach ($users as $user) {
            if ($user->password === $request->password || Hash::check($request->password, $user->password)) {
                $matchedUser = $user;
                break;
            }
        }

        if ($matchedUser) {
            Auth::login($matchedUser);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'password' => 'Password tidak cocok.',
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
