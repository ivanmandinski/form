@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                <p class="mt-2 text-gray-600">Configure your form monitoring application</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Application Settings</h3>
            <p class="text-sm text-gray-500">Manage global configuration options</p>
        </div>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Notification Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 flex items-center">
                        <svg class="mr-2 h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Notifications
                    </h4>
                    
                    <div>
                        <label for="email_notifications" class="flex items-center">
                            <input type="checkbox" name="email_notifications" id="email_notifications" value="1" 
                                   {{ $settings['email_notifications'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Enable email notifications</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Receive email alerts for form check failures</p>
                    </div>
                    
                    <div>
                        <label for="notification_email" class="block text-sm font-medium text-gray-700">Notification Email</label>
                        <input type="email" name="notification_email" id="notification_email" 
                               value="{{ $settings['notification_email'] }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Email address for receiving notifications</p>
                    </div>
                </div>
                
                <!-- Monitoring Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 flex items-center">
                        <svg class="mr-2 h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Monitoring
                    </h4>
                    
                    <div>
                        <label for="default_timeout" class="block text-sm font-medium text-gray-700">Default Timeout (seconds)</label>
                        <input type="number" name="default_timeout" id="default_timeout" min="10" max="300"
                               value="{{ $settings['default_timeout'] }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Maximum time to wait for form submission</p>
                    </div>
                    
                    <div>
                        <label for="max_retries" class="block text-sm font-medium text-gray-700">Maximum Retries</label>
                        <input type="number" name="max_retries" id="max_retries" min="0" max="5"
                               value="{{ $settings['max_retries'] }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Number of retry attempts for failed checks</p>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Settings -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-md font-medium text-gray-900 flex items-center mb-4">
                    <svg class="mr-2 h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Advanced Settings
                </h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="log_level" class="block text-sm font-medium text-gray-700">Log Level</label>
                        <select name="log_level" id="log_level" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="debug" {{ $settings['log_level'] == 'debug' ? 'selected' : '' }}>Debug</option>
                            <option value="info" {{ $settings['log_level'] == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ $settings['log_level'] == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ $settings['log_level'] == 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Level of detail for application logging</p>
                    </div>
                    
                    <div>
                        <label for="cleanup_days" class="block text-sm font-medium text-gray-700">Cleanup After (days)</label>
                        <input type="number" name="cleanup_days" id="cleanup_days" min="1" max="365"
                               value="{{ $settings['cleanup_days'] }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Automatically clean up old check runs</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="resetToDefaults()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset to Defaults
                </button>
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to their default values?')) {
        document.getElementById('email_notifications').checked = false;
        document.getElementById('notification_email').value = 'admin@example.com';
        document.getElementById('default_timeout').value = '30';
        document.getElementById('max_retries').value = '2';
        document.getElementById('log_level').value = 'info';
        document.getElementById('cleanup_days').value = '30';
    }
}
</script>
@endsection
