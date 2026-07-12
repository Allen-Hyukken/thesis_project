<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    /**
     * Handle the "forgot password" form submission.
     * Sends a reset link only if the email belongs to a registered account.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'We couldn\'t find an account with that email address.'])
                ->onlyInput('email');
        }

        // Throttle: don't allow a new link within 60 seconds of the last one.
        $recent = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if ($recent && isset($recent->created_at) && now()->diffInSeconds($recent->created_at) < 60) {
            return back()
                ->with('status', 'A reset link was already sent recently. Please check your inbox, or wait a minute before requesting another.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $user->full_name));

        return back()->with('status', 'We\'ve emailed you a link to reset your password. Please check your inbox.');
    }

    /**
     * Show the "set a new password" form (reached via the emailed link).
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the new-password submission from the reset form.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()
                ->withErrors(['email' => 'This password reset link is invalid.'])
                ->onlyInput('email');
        }

        // Link expires after 60 minutes.
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()
                ->withErrors(['email' => 'This password reset link has expired. Please request a new one.'])
                ->onlyInput('email');
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'We couldn\'t find an account with that email address.']);
        }

        $user->forceFill([
            'password_hash' => Hash::make($request->password),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
    }
}
