<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:student,teacher',
        ]);

        $user = User::create([
            'full_name'     => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => $request->role,
        ]);

        Auth::login($user);

        return $request->role === 'teacher'
            ? redirect()->route('teacher.dashboard')
            : redirect()->route('student.dashboard');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $role = Auth::user()->role;

            return $role === 'teacher'
                ? redirect()->route('teacher.dashboard')
                : redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;

        // 1. Generate a secure random token
        $token = Str::random(64);

        // 2. Store or update the token in the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // 3. Build the actual link pointing to your reset form route
        // Assumes your named route is 'password.reset'
        $resetLink = route('password.reset', ['token' => $token, 'email' => $email]);

        // 4. Send the actual link in the email
        Mail::raw("Hello! You requested a password reset. Click the link below to change your password:\n\n" . $resetLink . "\n\nIf you did not request this, please ignore this email.", function ($message) use ($email) {
            $message->to($email)
                ->subject('Reset Your Password — Acadly');
        });

        return back()->with('status', 'If your email exists, we have sent a password reset link.');
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'This password reset link is invalid or expired.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password_hash' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in.');
    }
}
