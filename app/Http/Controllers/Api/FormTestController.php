<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TestFormRequest;
use App\Http\Requests\Api\TestFormByIdRequest;
use App\Http\Resources\Api\CheckRunResource;
use App\Http\Resources\Api\FormTargetResource;
use App\Models\FormTarget;
use App\Models\CheckRun;
use App\Services\FormCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FormTestController extends Controller
{
    private FormCheckService $formCheckService;

    public function __construct(FormCheckService $formCheckService)
    {
        $this->formCheckService = $formCheckService;
    }

    /**
     * Test a form by providing all form details in the request
     */
    public function testForm(TestFormRequest $request): JsonResponse
    {
        try {
            Log::info('API form test request received', [
                'url' => $request->url,
                'selector_type' => $request->selector_type,
                'selector_value' => $request->selector_value,
                'driver_type' => $request->driver_type ?? 'auto',
            ]);

            // Create a temporary FormTarget for this test
            $formTarget = $this->createTemporaryFormTarget($request);

            // Run the form check
            $checkRun = $this->formCheckService->checkForm($formTarget);

            Log::info('API form test completed', [
                'check_run_id' => $checkRun->id,
                'status' => $checkRun->status,
                'final_url' => $checkRun->final_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form test completed successfully',
                'data' => new CheckRunResource($checkRun),
            ], 200);

        } catch (\Exception $e) {
            Log::error('API form test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Form test failed: ' . $e->getMessage(),
                'error' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Test a form by referencing an existing FormTarget ID
     */
    public function testFormById(TestFormByIdRequest $request, int $formTargetId): JsonResponse
    {
        try {
            $formTarget = FormTarget::with(['target', 'fieldMappings'])->findOrFail($formTargetId);

            Log::info('API form test by ID request received', [
                'form_target_id' => $formTargetId,
                'url' => $formTarget->target->url,
            ]);

            // Override any settings if provided in request
            if ($request->has('driver_type')) {
                $formTarget->driver_type = $request->driver_type;
            }

            // Run the form check
            $checkRun = $this->formCheckService->checkForm($formTarget);

            Log::info('API form test by ID completed', [
                'form_target_id' => $formTargetId,
                'check_run_id' => $checkRun->id,
                'status' => $checkRun->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form test completed successfully',
                'data' => new CheckRunResource($checkRun),
            ], 200);

        } catch (\Exception $e) {
            Log::error('API form test by ID failed', [
                'form_target_id' => $formTargetId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Form test failed: ' . $e->getMessage(),
                'error' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get all available form targets
     */
    public function getFormTargets(Request $request): JsonResponse
    {
        try {
            $query = FormTarget::with(['target', 'fieldMappings', 'checkRuns' => function ($query) {
                $query->latest()->limit(1);
            }]);

            // Apply filters if provided
            if ($request->has('active_only') && $request->boolean('active_only')) {
                $query->where('schedule_enabled', true);
            }

            if ($request->has('driver_type')) {
                $query->where('driver_type', $request->driver_type);
            }

            $formTargets = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => FormTargetResource::collection($formTargets),
                'pagination' => [
                    'current_page' => $formTargets->currentPage(),
                    'last_page' => $formTargets->lastPage(),
                    'per_page' => $formTargets->perPage(),
                    'total' => $formTargets->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('API get form targets failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form targets: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific form target
     */
    public function getFormTarget(int $formTargetId): JsonResponse
    {
        try {
            $formTarget = FormTarget::with(['target', 'fieldMappings', 'checkRuns' => function ($query) {
                $query->latest()->limit(10);
            }])->findOrFail($formTargetId);

            return response()->json([
                'success' => true,
                'data' => new FormTargetResource($formTarget),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form target not found',
            ], 404);
        }
    }

    /**
     * Get check run history for a form target
     */
    public function getCheckRunHistory(Request $request, int $formTargetId): JsonResponse
    {
        try {
            $formTarget = FormTarget::findOrFail($formTargetId);

            $query = $formTarget->checkRuns()->with('artifacts');

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('driver')) {
                $query->where('driver', $request->driver);
            }

            if ($request->has('limit')) {
                $query->limit($request->get('limit', 50));
            }

            $checkRuns = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => CheckRunResource::collection($checkRuns),
                'pagination' => [
                    'current_page' => $checkRuns->currentPage(),
                    'last_page' => $checkRuns->lastPage(),
                    'per_page' => $checkRuns->perPage(),
                    'total' => $checkRuns->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve check run history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific check run
     */
    public function getCheckRun(int $checkRunId): JsonResponse
    {
        try {
            $checkRun = CheckRun::with(['formTarget.target', 'artifacts'])->findOrFail($checkRunId);

            return response()->json([
                'success' => true,
                'data' => new CheckRunResource($checkRun),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check run not found',
            ], 404);
        }
    }

    /**
     * Create a temporary FormTarget for API testing
     */
    private function createTemporaryFormTarget(TestFormRequest $request): FormTarget
    {
        // Create a temporary target
        $target = \App\Models\Target::create([
            'name' => 'API Test Target - ' . now()->format('Y-m-d H:i:s'),
            'url' => $request->url,
        ]);

        // Create a temporary form target with advanced features
        $formTarget = FormTarget::create([
            'target_id' => $target->id,
            'selector_type' => $request->selector_type,
            'selector_value' => $request->selector_value,
            'method_override' => $request->method_override,
            'action_override' => $request->action_override,
            'driver_type' => $request->driver_type ?? 'auto',
            'uses_js' => $request->boolean('uses_js'),
            'recaptcha_expected' => $request->boolean('recaptcha_expected'),
            'success_selector' => $request->success_selector,
            'error_selector' => $request->error_selector,
            'schedule_enabled' => false, // Always false for API tests
            'execute_javascript' => $request->execute_javascript,
            'wait_for_elements' => $request->wait_for_elements ? json_encode($request->wait_for_elements) : null,
            'custom_actions' => $request->custom_actions ? json_encode($request->custom_actions) : null,
        ]);

        // Create field mappings if provided
        if ($request->has('field_mappings') && is_array($request->field_mappings)) {
            foreach ($request->field_mappings as $mapping) {
                $formTarget->fieldMappings()->create([
                    'name' => $mapping['name'],
                    'selector' => $mapping['selector'] ?? $mapping['name'],
                    'value' => $mapping['value'],
                    'type' => $mapping['type'] ?? 'text',
                    'clear_first' => $mapping['clear_first'] ?? true,
                    'delay' => $mapping['delay'] ?? 100,
                ]);
            }
        }

        return $formTarget->load(['target', 'fieldMappings']);
    }
}
