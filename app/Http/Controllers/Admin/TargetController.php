<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $targets = Target::withCount('formTargets')->latest()->get();
        return view('admin.targets.index', compact('targets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.targets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'notes' => 'nullable|string|max:1000',
        ]);

        Target::create($validated);

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $target = Target::with('formTargets')->findOrFail($id);
        return view('admin.targets.show', compact('target'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $target = Target::findOrFail($id);
        return view('admin.targets.edit', compact('target'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $target = Target::findOrFail($id);
        
        $validated = $request->validate([
            'url' => 'required|url',
            'notes' => 'nullable|string|max:1000',
        ]);

        $target->update($validated);

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $target = Target::findOrFail($id);
        $target->delete();

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target deleted successfully.');
    }
}
