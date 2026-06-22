<?php

namespace App\Http\Controllers;

use App\Models\ClassMaster;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = ClassMaster::withCount('students')->latest()->get();
        return view('super-admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.classes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classes,code'],
            'description' => ['nullable', 'string'],
        ]);

        $class = ClassMaster::create($validated);

        activity()
            ->performedOn($class)
            ->log("Created class: {$class->name} ({$class->code})");

        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassMaster $class)
    {
        return view('super-admin.classes.edit', ['classMaster' => $class]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassMaster $class)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', "unique:classes,code,{$class->id}"],
            'description' => ['nullable', 'string'],
        ]);

        $class->update($validated);

        activity()
            ->performedOn($class)
            ->log("Updated class: {$class->name}");

        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassMaster $class)
    {
        if ($class->students()->exists()) {
            return redirect()->route('admin.classes.index')->with('error', 'Cannot delete class. Students are already registered under this class.');
        }

        activity()
            ->performedOn($class)
            ->log("Deleted class: {$class->name}");

        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }

    /**
     * Toggle Class status (Activate / Deactivate).
     */
    public function toggleStatus(ClassMaster $class)
    {
        $class->status = !$class->status;
        $class->save();

        $statusStr = $class->status ? 'Activated' : 'Deactivated';

        activity()
            ->performedOn($class)
            ->log("{$statusStr} class: {$class->name}");

        return back()->with('success', "Class has been {$statusStr}.");
    }
}
