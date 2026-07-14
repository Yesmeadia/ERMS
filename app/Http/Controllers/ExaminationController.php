<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $examinations = Examination::withCount('students')->latest()->get();
        return view('super-admin.examinations.index', compact('examinations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.examinations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'registration_start_date' => ['required', 'date'],
            'registration_end_date' => ['required', 'date', 'after_or_equal:registration_start_date'],
            'hall_ticket_release_date' => ['required', 'date', 'after_or_equal:registration_end_date'],
            'status' => ['required', 'in:Draft,Registration Started,Registartion closed,Examination Ongoing,result published'],
        ]);

        $examination = Examination::create($validated);

        activity()
            ->performedOn($examination)
            ->log("Created examination session: {$examination->name}");

        return redirect()->route('admin.examinations.index')->with('success', 'Examination session created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Examination $examination)
    {
        $examination->loadCount('students');
        return view('super-admin.examinations.show', compact('examination'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Examination $examination)
    {
        return view('super-admin.examinations.edit', compact('examination'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Examination $examination)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'registration_start_date' => ['required', 'date'],
            'registration_end_date' => ['required', 'date', 'after_or_equal:registration_start_date'],
            'hall_ticket_release_date' => ['required', 'date', 'after_or_equal:registration_end_date'],
            'status' => ['required', 'in:Draft,Registration Started,Registartion closed,Examination Ongoing,result published'],
        ]);

        $examination->update($validated);

        activity()
            ->performedOn($examination)
            ->log("Updated examination session: {$examination->name}");

        return redirect()->route('admin.examinations.index')->with('success', 'Examination session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Examination $examination)
    {
        if ($examination->students()->exists()) {
            return redirect()->route('admin.examinations.index')->with('error', 'Cannot delete examination session. Students are already registered for this session.');
        }

        activity()
            ->performedOn($examination)
            ->log("Deleted examination session: {$examination->name}");

        $examination->delete();

        return redirect()->route('admin.examinations.index')->with('success', 'Examination session deleted successfully.');
    }

    /**
     * Toggle status / open or close registration.
     */
    public function updateStatus(Request $request, Examination $examination)
    {
        $request->validate([
            'status' => ['required', 'in:Draft,Registration Started,Registartion closed,Examination Ongoing,result published'],
        ]);

        $oldStatus = $examination->status;
        $examination->status = $request->status;
        $examination->save();

        if ($request->status === 'result published' && $oldStatus !== 'result published') {
            activity()
                ->performedOn($examination)
                ->log("Result Publication: Results published for examination: {$examination->name}");
        } elseif ($request->status === 'Registartion closed' && $oldStatus !== 'Registartion closed') {
            activity()
                ->performedOn($examination)
                ->log("Registration Closed: Registration closed for examination: {$examination->name}");
        } else {
            activity()
                ->performedOn($examination)
                ->log("Changed registration status of {$examination->name} to: {$examination->status}");
        }

        return back()->with('success', "Examination registration status updated to {$examination->status}.");
    }
}
