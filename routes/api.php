<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormTestController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication endpoints (no auth required)
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

// API Authentication middleware group
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication management endpoints
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('token', [AuthController::class, 'createToken'])->name('token');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::delete('token', [AuthController::class, 'revokeToken'])->name('revoke-token');
        Route::delete('tokens', [AuthController::class, 'revokeAllTokens'])->name('revoke-all-tokens');
    });
    
    // Form Testing Endpoints
    Route::prefix('forms')->name('forms.')->group(function () {
        // Test a form with full configuration
        Route::post('test', [FormTestController::class, 'testForm'])->name('test');
        
        // Test a form by referencing an existing FormTarget ID
        Route::post('test/{formTargetId}', [FormTestController::class, 'testFormById'])->name('test.by-id');
        
        // Get all form targets
        Route::get('/', [FormTestController::class, 'getFormTargets'])->name('index');
        
        // Get a specific form target
        Route::get('{formTargetId}', [FormTestController::class, 'getFormTarget'])->name('show');
        
        // Get check run history for a form target
        Route::get('{formTargetId}/runs', [FormTestController::class, 'getCheckRunHistory'])->name('runs');
    });
    
    // Check Run Endpoints
    Route::prefix('runs')->name('runs.')->group(function () {
        // Get a specific check run
        Route::get('{checkRunId}', [FormTestController::class, 'getCheckRun'])->name('show');
    });
    
    // Artifact Download Endpoint
    Route::get('artifacts/{artifact}/download', function ($artifactId) {
        $artifact = \App\Models\CheckArtifact::findOrFail($artifactId);
        
        if (!\Storage::disk('public')->exists($artifact->path)) {
            return response()->json(['error' => 'Artifact not found'], 404);
        }
        
        return \Storage::disk('public')->download($artifact->path);
    })->name('artifacts.download');
});

// Public API endpoints (no authentication required)
Route::prefix('public')->group(function () {
    // Health check endpoint
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    })->name('health');
    
    // API documentation endpoint
    Route::get('docs', function () {
        return response()->json([
            'title' => 'Form Monitor API',
            'version' => '1.0.0',
            'description' => 'API for testing web forms and monitoring their functionality',
            'endpoints' => [
                'POST /api/forms/test' => 'Test a form with full configuration',
                'POST /api/forms/test/{id}' => 'Test a form by FormTarget ID',
                'GET /api/forms' => 'Get all form targets',
                'GET /api/forms/{id}' => 'Get a specific form target',
                'GET /api/forms/{id}/runs' => 'Get check run history for a form target',
                'GET /api/runs/{id}' => 'Get a specific check run',
                'GET /api/artifacts/{id}/download' => 'Download an artifact',
            ],
            'authentication' => 'Bearer token via Sanctum',
        ]);
    })->name('docs');
});

// Fallback for undefined API routes
Route::fallback(function () {
    return response()->json([
        'error' => 'API endpoint not found',
        'message' => 'The requested API endpoint does not exist.',
        'available_endpoints' => [
            'GET /api/public/health' => 'Health check',
            'GET /api/public/docs' => 'API documentation',
            'POST /api/forms/test' => 'Test a form',
            'GET /api/forms' => 'List form targets',
        ],
    ], 404);
});
