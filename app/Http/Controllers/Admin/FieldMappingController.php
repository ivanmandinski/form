<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FieldMapping;
use App\Models\FormTarget;
use Illuminate\Http\Request;

class FieldMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formTargets = FormTarget::with(['target', 'fieldMappings'])->get();
        return view('admin.field-mappings.index', compact('formTargets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formTargets = FormTarget::with('target')->get();
        return view('admin.field-mappings.create', compact('formTargets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_target_id' => 'required|exists:form_targets,id',
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:1000',
        ]);

        FieldMapping::create($validated);

        return redirect()->route('admin.field-mappings.index')
            ->with('success', 'Field mapping created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fieldMapping = FieldMapping::with('formTarget.target')->findOrFail($id);
        return view('admin.field-mappings.show', compact('fieldMapping'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $fieldMapping = FieldMapping::findOrFail($id);
        $formTargets = FormTarget::with('target')->get();
        return view('admin.field-mappings.edit', compact('fieldMapping', 'formTargets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fieldMapping = FieldMapping::findOrFail($id);
        
        $validated = $request->validate([
            'form_target_id' => 'required|exists:form_targets,id',
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:1000',
        ]);

        $fieldMapping->update($validated);

        return redirect()->route('admin.field-mappings.index')
            ->with('success', 'Field mapping updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fieldMapping = FieldMapping::findOrFail($id);
        $fieldMapping->delete();

        return redirect()->route('admin.field-mappings.index')
            ->with('success', 'Field mapping deleted successfully.');
    }
}
