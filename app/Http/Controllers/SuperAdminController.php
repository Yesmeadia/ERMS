<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuperAdminCreatedMail;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::role('super-admin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->latest()->paginate(10);
        $totalAdmins = User::role('super-admin')->count();
        $canCreateAdmin = $totalAdmins < 2;

        return view('super-admin.admins.index', compact('admins', 'canCreateAdmin', 'totalAdmins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (User::role('super-admin')->count() >= 2) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'The maximum limit of 2 Super Admin accounts has been reached.');
        }

        return view('super-admin.admins.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (User::role('super-admin')->count() >= 2) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'The maximum limit of 2 Super Admin accounts has been reached.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ];

        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($userData);
        $user->assignRole('super-admin');

        // Send email with credentials
        try {
            Mail::to($user->email)->send(new SuperAdminCreatedMail($user, $validated['password']));
        } catch (\Exception $e) {
            report($e);
        }

        activity()
            ->performedOn($user)
            ->log("Created Super Admin user: {$user->email}");

        return redirect()->route('admin.admins.index')
            ->with('success', 'Super Admin account created successfully. The login credentials have been emailed to them.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $admin = User::findOrFail($id);

        if (!$admin->hasRole('super-admin')) {
            abort(404);
        }

        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Please edit your own account details via the My Profile page.');
        }

        return view('super-admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        if (!$admin->hasRole('super-admin')) {
            abort(404);
        }

        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Please edit your own account details via the My Profile page.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$admin->id}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048', new \App\Rules\VirusFree],
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if ($request->hasFile('profile_image')) {
            if ($admin->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->profile_image);
            }
            $admin->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        if ($request->filled('password')) {
            $admin->password = bcrypt($validated['password']);
        }

        $admin->save();

        activity()
            ->performedOn($admin)
            ->log("Updated Super Admin user account: {$admin->email}");

        return redirect()->route('admin.admins.index')
            ->with('success', 'Super Admin account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $admin = User::findOrFail($id);

        if (!$admin->hasRole('super-admin')) {
            abort(404);
        }

        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own active Super Admin account.');
        }

        activity()
            ->performedOn($admin)
            ->log("Deleted Super Admin user account: {$admin->email}");

        // Delete profile photograph if exists
        if ($admin->profile_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->profile_image);
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Super Admin account deleted successfully.');
    }
}
