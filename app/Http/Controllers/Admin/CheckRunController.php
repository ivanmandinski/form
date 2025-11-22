<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckRun;
use Illuminate\Http\Request;

class CheckRunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $runs = CheckRun::with(['formTarget.target'])
            ->latest()
            ->paginate(20);
            
        return view('admin.runs.index', compact('runs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $run = CheckRun::with(['formTarget.target', 'artifacts'])->findOrFail($id);
        return view('admin.runs.show', compact('run'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $run = CheckRun::with('artifacts')->findOrFail($id);
        
        // Delete associated artifacts and their files
        foreach ($run->artifacts as $artifact) {
            // Delete the physical file
            if (\Storage::disk('public')->exists($artifact->path)) {
                \Storage::disk('public')->delete($artifact->path);
            }
            // Delete the database record
            $artifact->delete();
        }
        
        // Delete the check run
        $run->delete();
        
        return redirect()->route('admin.runs.index')
            ->with('success', 'Check run and all associated artifacts have been deleted successfully.');
    }
}
