<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return view('super-admin.profile', compact('user'));
        }

        if ($user->hasRole('invigilator')) {
            return view('invigilator.profile', compact('user'));
        }

        $school = $user->school;
        return view('school-admin.profile', compact('user', 'school'));
    }

    /**
     * Update the profile details.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
                'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
            ]);

            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $validated['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
            }

            $user->update($validated);

            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->log('Updated their profile details');

            return back()->with('success', 'Profile details updated successfully.');
        }

        if ($user->hasRole('invigilator')) {
            abort(403, 'Profile updates are disabled for invigilator accounts.');
        }

        // School Admin flow
        $school = $user->school;
        if (!$school) {
            return back()->with('error', 'No school profile found for this account.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'school_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'zone' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($userData);

        // Update School details
        $school->update([
            'name' => $validated['school_name'],
            'address' => $validated['address'],
            'zone' => $validated['zone'],
            'state' => $validated['state'],
            'contact_person' => $validated['contact_person'],
            'mobile_number' => $validated['mobile_number'],
            'email' => $validated['email'], // synced email
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($school)
            ->log("Updated school profile details: {$school->name} and admin profile");

        return back()->with('success', 'Profile and School details updated successfully.');
    }
}
