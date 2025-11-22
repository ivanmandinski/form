<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    /**
     * Display the settings page.
     */
    public function index()
    {
        // Get default config
        $defaultSettings = config('form-monitor');
        
        // Get cached settings
        $cachedSettings = Cache::get('form-monitor-settings', []);
        
        // Merge settings (cached takes precedence)
        // We need to map the config structure to the flat structure used by the view
        $settings = [
            'email_notifications' => $cachedSettings['email_notifications'] ?? ($defaultSettings['notifications']['enabled'] ?? false),
            'notification_email' => $cachedSettings['notification_email'] ?? ($defaultSettings['notifications']['email'] ?? 'admin@example.com'),
            'default_timeout' => $cachedSettings['default_timeout'] ?? ($defaultSettings['timeouts']['http'] ?? 30),
            'max_retries' => $cachedSettings['max_retries'] ?? ($defaultSettings['scheduling']['max_retries'] ?? 2),
            'log_level' => $cachedSettings['log_level'] ?? ($defaultSettings['logging']['level'] ?? 'info'),
            'cleanup_days' => $cachedSettings['cleanup_days'] ?? ($defaultSettings['artifacts']['retention_days'] ?? 30),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'nullable|boolean', // Checkbox sends '1' or nothing
            'notification_email' => 'required_if:email_notifications,1|email|nullable',
            'default_timeout' => 'required|integer|min:5|max:300',
            'max_retries' => 'required|integer|min:0|max:5',
            'log_level' => 'required|in:debug,info,warning,error',
            'cleanup_days' => 'required|integer|min:1|max:365',
        ]);

        // Handle checkbox boolean conversion
        $validated['email_notifications'] = $request->has('email_notifications');

        // Store settings in cache
        Cache::put('form-monitor-settings', $validated, now()->addDays(30));

        // In a real app, you might also want to update the runtime config here
        // config(['form-monitor.timeouts.http' => $validated['default_timeout']]);
        // ... etc

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
