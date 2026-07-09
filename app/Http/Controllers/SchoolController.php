<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SchoolCreatedMail;
use Spatie\Permission\Models\Role;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = School::with('admins')->withCount('admins', 'students');

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $schools = $query->latest()->paginate(10);

        return view('super-admin.schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.schools.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:schools,code'],
            'address' => ['required', 'string'],
            'zone' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:schools,email', 'unique:users,email'],
            'is_centre' => ['nullable', 'boolean'],
        ]);

        $validated['is_centre'] = $request->boolean('is_centre');

        $school = School::create($validated);

        // Generate password and create first school-admin User
        $password = Str::random(16);
        $user = User::create([
            'name' => $school->contact_person,
            'email' => $school->email,
            'password' => Hash::make($password),
            'school_id' => $school->id,
        ]);

        // Assign school-admin role
        $role = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Generate password set token
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);

        // Send email with credentials
        try {
            Mail::to($school->email)->send(new SchoolCreatedMail($school, $user, $token));
        } catch (\Exception $e) {
            // Log the error but don't crash the request
            report($e);
        }

        activity()
            ->performedOn($school)
            ->log("Created school: {$school->name} ({$school->code}) and automatically created admin user: {$user->email}");

        return redirect()->route('admin.schools.index')->with('success', 'School created successfully. School Admin account was automatically created and the password has been sent to their email.');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        $school->load('admins', 'students.class', 'students.examination');
        return view('super-admin.schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        return view('super-admin.schools.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', "unique:schools,code,{$school->id}"],
            'address' => ['required', 'string'],
            'zone' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', "unique:schools,email,{$school->id}", "unique:users,email,{$school->id},school_id"],
            'is_centre' => ['nullable', 'boolean'],
        ]);

        $validated['is_centre'] = $request->boolean('is_centre');

        $oldEmail = $school->email;
        $school->update($validated);

        // Optional: Keep the main school admin user email in sync if the school email changed
        if ($oldEmail !== $school->email) {
            $mainAdmin = User::where('school_id', $school->id)->where('email', $oldEmail)->first();
            if ($mainAdmin) {
                $mainAdmin->update(['email' => $school->email]);
            }
        }

        activity()
            ->performedOn($school)
            ->log("Updated school details: {$school->name}");

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        // Activity log
        activity()
            ->performedOn($school)
            ->log("Deleted school: {$school->name}");

        $school->delete();

        return redirect()->route('admin.schools.index')->with('success', 'School deleted successfully.');
    }

    /**
     * Toggle School status (Activate / Deactivate).
     */
    public function toggleStatus(School $school)
    {
        $school->status = !$school->status;
        $school->save();

        $statusStr = $school->status ? 'Activated' : 'Deactivated';

        activity()
            ->performedOn($school)
            ->log("{$statusStr} school: {$school->name}");

        return back()->with('success', "School is now {$statusStr}.");
    }

    /**
     * Assign School Admin login account.
     */
    public function assignAdmin(Request $request, School $school)
    {
        $validated = $request->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'school_id' => $school->id,
        ]);

        // Assign school-admin role
        $role = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        activity()
            ->performedOn($school)
            ->causedBy(auth()->user())
            ->log("Assigned School Admin Account ({$user->email}) to school: {$school->name}");

        return back()->with('success', "School Admin account assigned successfully.");
    }

    /**
     * Reset School Admin Password.
     */
    public function resetPassword(Request $request, School $school)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'new_password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
        ]);

        $user = User::where('school_id', $school->id)->findOrFail($validated['user_id']);
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        activity()
            ->performedOn($school)
            ->causedBy(auth()->user())
            ->log("Reset password for School Admin ({$user->email}) of school: {$school->name}");

        return back()->with('success', "School Admin password reset successfully.");
    }
}
