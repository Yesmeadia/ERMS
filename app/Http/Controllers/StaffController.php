<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\Examination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Mail\InvigilatorCreatedMail;
use Barryvdh\DomPDF\Facade\Pdf;

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

        if ($request->filled('login_status') && Schema::hasColumn('users', 'last_login_at')) {
            if ($request->login_status === 'logged_in') {
                $query->whereNotNull('last_login_at');
            } elseif ($request->login_status === 'not_logged_in') {
                $query->whereNull('last_login_at');
            }
        }

        $staffMembers = $query->latest()->paginate(10);

        return view('super-admin.staff.index', compact('staffMembers'));
    }

    /**
     * Export the filtered/all invigilators list as PDF.
     */
    public function exportPdf(Request $request)
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

        if ($request->filled('login_status') && Schema::hasColumn('users', 'last_login_at')) {
            if ($request->login_status === 'logged_in') {
                $query->whereNotNull('last_login_at');
            } elseif ($request->login_status === 'not_logged_in') {
                $query->whereNull('last_login_at');
            }
        }

        $invigilators = $query->orderBy('name', 'asc')->get();

        $summary = [
            'total' => $invigilators->count(),
            'logged_in' => $invigilators->whereNotNull('last_login_at')->count(),
            'not_logged_in' => $invigilators->whereNull('last_login_at')->count(),
            'active' => $invigilators->filter(fn($u) => $u->is_active ?? true)->count(),
            'banned' => $invigilators->filter(fn($u) => !($u->is_active ?? true))->count(),
        ];

        $examination = $request->filled('examination_id')
            ? Examination::find($request->examination_id)
            : Examination::getActiveExam();

        $pdf = Pdf::loadView('pdf.staff-invigilators', [
            'invigilators' => $invigilators,
            'examination' => $examination,
            'summary' => $summary,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'login_status' => $request->login_status,
            ],
            'generatedAt' => now()->format('d M Y, h:i A'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Invigilators_List_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
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
