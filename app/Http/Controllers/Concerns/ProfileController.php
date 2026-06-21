<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => auth()->user(), 'isOwnProfile' => true]);
    }

    /**
     * View someone else's profile — read-only, no Edit button.
     * Reuses the same view as show().
     */
    public function showUser(User $user)
    {
        if ((int) $user->user_id === (int) auth()->id()) {
            return redirect()->route('profile.show');
        }

        return view('profile.show', ['user' => $user, 'isOwnProfile' => false]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'full_name' => 'required|string|max:255',
            'bio'       => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];

        if ($user->role === 'student') {
            $rules['program']    = 'nullable|string|max:255';
            $rules['year_level'] = 'nullable|string|max:50';
        } elseif ($user->role === 'teacher') {
            $rules['department'] = 'nullable|string|max:255';
            $rules['position']   = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
