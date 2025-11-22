<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckRun;
use App\Models\FormTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats for the last 24 hours
        $last24h = now()->subHours(24);
        
        $totalChecks = CheckRun::where('created_at', '>=', $last24h)->count();
        
        $successfulChecks = CheckRun::where('created_at', '>=', $last24h)
            ->where('status', 'success')
            ->count();
            
        $failedChecks = CheckRun::where('created_at', '>=', $last24h)
            ->where('status', 'failure')
            ->count();

        // Overall stats for the view
        $totalTargets = \App\Models\Target::count();
        $totalForms = \App\Models\FormTarget::count();
        $totalRuns = CheckRun::count();
        $successfulRuns = CheckRun::where('status', 'success')->count();
            
        $successRate = $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 1) : 0;
        
        // Average response time (duration between started_at and finished_at)
        // Note: This assumes finished_at is populated. If using SQLite/MySQL, we can use DB raw for diff
        // For simplicity in PHP:
        $avgDuration = CheckRun::where('created_at', '>=', $last24h)
            ->whereNotNull('finished_at')
            ->whereNotNull('started_at')
            ->get()
            ->avg(function ($run) {
                return $run->finished_at->diffInSeconds($run->started_at);
            }) ?? 0;
            
        $activeForms = FormTarget::where('schedule_enabled', true)->count();
        
        // Recent runs
        $recentRuns = CheckRun::with('formTarget.target')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalChecks',
            'successfulChecks',
            'failedChecks',
            'successRate',
            'avgDuration',
            'activeForms',
            'activeForms',
            'recentRuns',
            'totalTargets',
            'totalForms',
            'totalRuns',
            'successfulRuns'
        ));
    }
}
