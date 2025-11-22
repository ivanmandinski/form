<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\FormTargetController;
use App\Http\Controllers\Admin\FieldMappingController;
use App\Http\Controllers\Admin\CheckRunController;
use App\Http\Controllers\Admin\SettingsController;

// Redirect root to login if not authenticated, or to admin dashboard if authenticated
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    // All authenticated users go to admin dashboard
    return redirect()->route('admin.dashboard');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Debug route to check role (remove after debugging)
Route::get('/debug-role', function () {
    if (!auth()->check()) {
        return response()->json(['error' => 'Not logged in'], 401);
    }
    
    $user = auth()->user();
    return response()->json([
        'email' => $user->email,
        'id' => $user->id,
        'has_admin_role' => $user->hasRole('admin'),
        'roles' => $user->getRoleNames()->toArray(),
        'all_roles' => $user->roles->pluck('name')->toArray(),
        'guard_name_check' => $user->roles->pluck('guard_name')->toArray(),
    ]);
})->middleware('auth');

// Admin routes - all authenticated users have access
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Targets
    Route::resource('targets', TargetController::class);
    
    // Forms
    Route::resource('forms', FormTargetController::class);
    Route::post('forms/{form}/run', [FormTargetController::class, 'run'])->name('forms.run');
    
    // Field Mappings
    Route::resource('field-mappings', FieldMappingController::class);
    
    // Check Runs
    Route::resource('runs', CheckRunController::class)->only(['index', 'show', 'destroy']);
    
    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
