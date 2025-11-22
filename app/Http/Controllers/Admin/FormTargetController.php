<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTarget;
use App\Models\Target;
use App\Services\FormCheckService;
use Illuminate\Http\Request;

class FormTargetController extends Controller
{
    protected FormCheckService $formCheckService;

    public function __construct(FormCheckService $formCheckService)
    {
        $this->formCheckService = $formCheckService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $forms = FormTarget::with(['target', 'checkRuns' => function($query) {
            $query->latest()->limit(1);
        }])->latest()->get();
        
        return view('admin.forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $targets = Target::all();
        return view('admin.forms.create', compact('targets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:targets,id',
            'selector_type' => 'required|in:id,class,css',
            'selector_value' => 'required|string|max:255',
            'method_override' => 'nullable|string|max:10',
            'action_override' => 'nullable|url',
            'driver_type' => 'required|in:http,dusk,puppeteer',
            'uses_js' => 'boolean',
            'recaptcha_expected' => 'boolean',
            'success_selector' => 'nullable|string|max:255',
            'error_selector' => 'nullable|string|max:255',
            'schedule_enabled' => 'boolean',
            'schedule_frequency' => 'required_if:schedule_enabled,1|in:manual,hourly,daily,weekly,cron',
            'schedule_cron' => 'required_if:schedule_frequency,cron|nullable|string|max:255',
            'schedule_timezone' => 'required_if:schedule_frequency,1|string|max:50',
        ]);

        // Set driver-specific flags based on driver_type
        $validated['uses_js'] = $validated['driver_type'] === 'dusk' || $validated['driver_type'] === 'puppeteer';
        
        // For Puppeteer, ensure recaptcha_expected is set if driver is puppeteer
        if ($validated['driver_type'] === 'puppeteer') {
            $validated['recaptcha_expected'] = $request->boolean('recaptcha_expected');
        }

        FormTarget::create($validated);

        return redirect()->route('admin.forms.index')
            ->with('success', 'Form target created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $form = FormTarget::with(['target', 'fieldMappings', 'checkRuns'])->findOrFail($id);
        return view('admin.forms.show', compact('form'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $form = FormTarget::findOrFail($id);
        $targets = Target::all();
        return view('admin.forms.edit', compact('form', 'targets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $form = FormTarget::findOrFail($id);
        
        $validated = $request->validate([
            'target_id' => 'required|exists:targets,id',
            'selector_type' => 'required|in:id,class,css',
            'selector_value' => 'required|string|max:255',
            'method_override' => 'nullable|string|max:10',
            'action_override' => 'nullable|url',
            'driver_type' => 'required|in:http,dusk,puppeteer',
            'uses_js' => 'boolean',
            'recaptcha_expected' => 'boolean',
            'success_selector' => 'nullable|string|max:255',
            'error_selector' => 'nullable|string|max:255',
            'schedule_enabled' => 'boolean',
            'schedule_frequency' => 'required_if:schedule_enabled,1|in:manual,hourly,daily,weekly,cron',
            'schedule_cron' => 'required_if:schedule_frequency,cron|nullable|string|max:255',
            'schedule_timezone' => 'required_if:schedule_frequency,1|string|max:50',
        ]);

        // Set driver-specific flags based on driver_type
        $validated['uses_js'] = $validated['driver_type'] === 'dusk' || $validated['driver_type'] === 'puppeteer';
        
        // For Puppeteer, ensure recaptcha_expected is set if driver is puppeteer
        if ($validated['driver_type'] === 'puppeteer') {
            $validated['recaptcha_expected'] = $request->boolean('recaptcha_expected');
        }

        $form->update($validated);

        return redirect()->route('admin.forms.index')
            ->with('success', 'Form target updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $form = FormTarget::findOrFail($id);
        $form->delete();

        return redirect()->route('admin.forms.index')
            ->with('success', 'Form target deleted successfully.');
    }

    /**
     * Manually run a form check.
     */
    public function run(FormTarget $form)
    {
        try {
            $checkRun = $this->formCheckService->checkForm($form);
            
            return redirect()->route('admin.forms.index')
                ->with('success', "Form check started successfully. Run ID: {$checkRun->id}");
        } catch (\Exception $e) {
            return redirect()->route('admin.forms.index')
                ->with('error', 'Failed to start form check: ' . $e->getMessage());
        }
    }
}
