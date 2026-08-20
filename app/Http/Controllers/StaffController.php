<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\InvigilatorCreatedMail;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('invigilator')->with('school');

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where(function($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                });
            } elseif ($request->status === 'banned') {
                $query->where('is_active', false);
            }
        }

        $staffMembers = $query->latest()->paginate(10);

        return view('super-admin.staff.index', compact('staffMembers'));
    }

    public function create()
    {
        $examinationCentres = School::where('is_centre', true)->where('status', true)->orderBy('name')->get();
        return view('super-admin.staff.create', compact('examinationCentres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt(Str::random(32)),
            'school_id' => $validated['school_id'] ?? null,
        ];

        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($userData);

        $user->assignRole('invigilator');

        // Generate password set token
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);

        // Send email with credentials
        try {
            Mail::to($user->email)->send(new InvigilatorCreatedMail($user, $token));
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

        $examinationCentres = School::where('is_centre', true)->where('status', true)->orderBy('name')->get();
        return view('super-admin.staff.edit', compact('staff', 'examinationCentres'));
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
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
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

    /**
     * Toggle the active/banned status of a staff member.
     */
    public function toggleStatus($id)
    {
        $staff = User::findOrFail($id);

        if (!$staff->hasRole('invigilator')) {
            abort(404);
        }

        $staff->is_active = !($staff->is_active ?? true);
        $staff->save();

        $statusStr = $staff->is_active ? 'Activated' : 'Banned / Deactivated';

        activity()
            ->performedOn($staff)
            ->log("{$statusStr} staff account: {$staff->email}");

        return back()->with('success', "Staff account is now {$statusStr}.");
    }

    /**
     * Send password reset invitation link to the staff member.
     */
    public function sendResetLink($id)
    {
        $staff = User::findOrFail($id);

        if (!$staff->hasRole('invigilator')) {
            abort(404);
        }

        $token = \Illuminate\Support\Facades\Password::broker()->createToken($staff);

        try {
            Mail::to($staff->email)->send(new InvigilatorCreatedMail($staff, $token));
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to send password reset email: ' . $e->getMessage());
        }

        activity()
            ->performedOn($staff)
            ->log("Sent password reset link to staff user: {$staff->email}");

        return back()->with('success', "Password reset link has been emailed to {$staff->email}.");
    }
}
