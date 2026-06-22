<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvigilatorCreatedMail;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('invigilator')->with('school');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staffMembers = $query->latest()->paginate(10);

        return view('super-admin.staff.index', compact('staffMembers'));
    }

    public function create()
    {
        $schools = School::where('status', true)->get();
        return view('super-admin.staff.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'school_id' => $validated['school_id'],
        ];

        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($userData);

        $user->assignRole('invigilator');

        // Send email with credentials
        try {
            Mail::to($user->email)->send(new InvigilatorCreatedMail($user, $validated['password']));
        } catch (\Exception $e) {
            report($e);
        }

        activity()
            ->performedOn($user)
            ->log("Created examination staff user: {$user->email}");

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully. The login credentials have been emailed to them.');
    }

    public function edit($id)
    {
        $staff = User::findOrFail($id);

        if (!$staff->hasRole('invigilator')) {
            abort(404);
        }

        $schools = School::where('status', true)->get();
        return view('super-admin.staff.edit', compact('staff', 'schools'));
    }

    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        if (!$staff->hasRole('invigilator')) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$staff->id}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->school_id = $validated['school_id'];

        if ($request->hasFile('profile_image')) {
            if ($staff->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($staff->profile_image);
            }
            $staff->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        if ($request->filled('password')) {
            $staff->password = bcrypt($validated['password']);
        }

        $staff->save();

        activity()
            ->performedOn($staff)
            ->log("Updated staff user account: {$staff->email}");

        return redirect()->route('admin.staff.index')->with('success', 'Staff account updated successfully.');
    }

    public function destroy($id)
    {
        $staff = User::findOrFail($id);

        if (!$staff->hasRole('invigilator')) {
            abort(404);
        }

        activity()
            ->performedOn($staff)
            ->log("Deleted staff user account: {$staff->email}");

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff account deleted successfully.');
    }
}
