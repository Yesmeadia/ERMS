<?php

namespace App\Http\Controllers;

use App\Models\CategoryMaster;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = CategoryMaster::withCount('students')->latest()->get();
        return view('super-admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('super-admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:categories,code'],
        ]);

        $category = CategoryMaster::create($validated);

        activity()
            ->performedOn($category)
            ->log("Created category: {$category->name} ({$category->code})");

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoryMaster $category)
    {
        return view('super-admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CategoryMaster $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', "unique:categories,code,{$category->id}"],
        ]);

        $category->update($validated);

        activity()
            ->performedOn($category)
            ->log("Updated category: {$category->name}");

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoryMaster $category)
    {
        if ($category->students()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category. Students are registered under this category.');
        }

        activity()
            ->performedOn($category)
            ->log("Deleted category: {$category->name}");

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle Category status (Activate / Deactivate).
     */
    public function toggleStatus(CategoryMaster $category)
    {
        $category->status = !$category->status;
        $category->save();

        $statusStr = $category->status ? 'Activated' : 'Deactivated';

        activity()
            ->performedOn($category)
            ->log("{$statusStr} category: {$category->name}");

        return back()->with('success', "Category has been {$statusStr}.");
    }
}
