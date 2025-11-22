@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Add New Form Target</h1>
                <a href="{{ route('admin.forms.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Forms
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.forms.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Target Selection -->
            <div>
                <label for="target_id" class="block text-sm font-medium text-gray-700 mb-2">Target Website</label>
                <select name="target_id" id="target_id" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a target website</option>
                    @foreach(\App\Models\Target::all() as $target)
                        <option value="{{ $target->id }}" {{ old('target_id') == $target->id ? 'selected' : '' }}>
                            {{ $target->url }} {{ $target->notes ? "({$target->notes})" : '' }}
                        </option>
                    @endforeach
                </select>
                @error('target_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">Choose the website where the form is located</p>
            </div>
            
            <!-- Form Selector -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="selector_type" class="block text-sm font-medium text-gray-700 mb-2">Selector Type</label>
                    <select name="selector_type" id="selector_type" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="css" {{ old('selector_type') == 'css' ? 'selected' : '' }}>CSS Selector</option>
                        <option value="xpath" {{ old('selector_type') == 'xpath' ? 'selected' : '' }}>XPath</option>
                        <option value="id" {{ old('selector_type') == 'id' ? 'selected' : '' }}>Element ID</option>
                        <option value="name" {{ old('selector_type') == 'name' ? 'selected' : '' }}>Element Name</option>
                    </select>
                    @error('selector_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="selector_value" class="block text-sm font-medium text-gray-700 mb-2">Selector Value</label>
                    <input type="text" name="selector_value" id="selector_value" required 
                           value="{{ old('selector_value') }}"
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., #contact-form, //form[@id='contact'], form[name='contact']">
                    @error('selector_value')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Driver Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Form Driver Selection</label>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="radio" name="driver_type" id="driver_http" value="http" 
                               {{ old('driver_type', 'http') == 'http' ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <label for="driver_http" class="ml-3 flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">HTTP</span>
                            <span class="text-sm text-gray-700">Standard HTTP form testing (fast, reliable)</span>
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="radio" name="driver_type" id="driver_dusk" value="dusk" 
                               {{ old('driver_type') == 'dusk' ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                        <label for="driver_dusk" class="ml-3 flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2">Dusk</span>
                            <span class="text-sm text-gray-700">ChromeDriver automation for JavaScript forms</span>
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="radio" name="driver_type" id="driver_puppeteer" value="puppeteer" 
                               {{ old('driver_type') == 'puppeteer' ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                        <label for="driver_puppeteer" class="ml-3 flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">Puppeteer</span>
                            <span class="text-sm text-gray-700">Real browser with CAPTCHA handling</span>
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">Choose the driver based on your form's requirements. Puppeteer is recommended for forms with CAPTCHAs.</p>
            </div>

            <!-- CAPTCHA Configuration (only for Puppeteer) -->
            <div id="captcha-config" class="border border-green-200 bg-green-50 rounded-lg p-4" style="display: none;">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Puppeteer Configuration</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>This form will use Puppeteer with real browser automation for optimal CAPTCHA handling.</p>
                            <div class="mt-3 space-y-2">
                                <label for="recaptcha_expected" class="flex items-center">
                                    <input type="checkbox" name="recaptcha_expected" id="recaptcha_expected" value="1" 
                                           {{ old('recaptcha_expected') ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <span class="ml-2">Form contains CAPTCHA</span>
                                </label>
                                <label for="uses_js" class="flex items-center">
                                    <input type="checkbox" name="uses_js" id="uses_js" value="1" 
                                           {{ old('uses_js') ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <span class="ml-2">Form requires JavaScript</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Success/Error Selectors -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="success_selector" class="block text-sm font-medium text-gray-700 mb-2">Success Selector</label>
                    <input type="text" name="success_selector" id="success_selector" 
                           value="{{ old('success_selector') }}"
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., .success, #message-success">
                    <p class="mt-1 text-xs text-gray-500">CSS selector to identify successful form submission</p>
                </div>
                
                <div>
                    <label for="error_selector" class="block text-sm font-medium text-gray-700 mb-2">Error Selector</label>
                    <input type="text" name="error_selector" id="error_selector" 
                           value="{{ old('error_selector') }}"
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., .error, #message-error">
                    <p class="mt-1 text-xs text-gray-500">CSS selector to identify form submission errors</p>
                </div>
            </div>
            
            <!-- Schedule Configuration -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Configuration</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="schedule_enabled" class="flex items-center">
                            <input type="checkbox" name="schedule_enabled" id="schedule_enabled" value="1" 
                                   {{ old('schedule_enabled') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Enable scheduled monitoring</span>
                        </label>
                    </div>
                    
                    <div id="schedule-options" class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="display: none;">
                        <div>
                            <label for="schedule_frequency" class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                            <select name="schedule_frequency" id="schedule_frequency" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="hourly" {{ old('schedule_frequency') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                <option value="daily" {{ old('schedule_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('schedule_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="cron" {{ old('schedule_frequency') == 'cron' ? 'selected' : '' }}>Custom Cron</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="schedule_timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                            <select name="schedule_timezone" id="schedule_timezone" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="UTC" {{ old('schedule_timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ old('schedule_timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                <option value="America/Chicago" {{ old('schedule_timezone') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                <option value="America/Denver" {{ old('schedule_timezone') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                <option value="America/Los_Angeles" {{ old('schedule_timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                <option value="Europe/London" {{ old('schedule_timezone') == 'Europe/London' ? 'selected' : '' }}>London</option>
                                <option value="Europe/Paris" {{ old('schedule_timezone') == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                <option value="Asia/Tokyo" {{ old('schedule_timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                            </select>
                        </div>
                        
                        <div id="cron-input" style="display: none;">
                            <label for="schedule_cron" class="block text-sm font-medium text-gray-700 mb-2">Cron Expression</label>
                            <input type="text" name="schedule_cron" id="schedule_cron" 
                                   value="{{ old('schedule_cron') }}"
                                   class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0 */6 * * * (every 6 hours)">
                            <p class="mt-1 text-xs text-gray-500">Format: minute hour day month weekday</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.forms.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Form Target
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleEnabled = document.getElementById('schedule_enabled');
    const scheduleOptions = document.getElementById('schedule-options');
    const frequencySelect = document.getElementById('schedule_frequency');
    const cronInput = document.getElementById('cron-input');
    
    // Driver selection elements
    const driverRadios = document.querySelectorAll('input[name="driver_type"]');
    const captchaConfig = document.getElementById('captcha-config');
    
    function toggleScheduleOptions() {
        if (scheduleEnabled.checked) {
            scheduleOptions.style.display = 'grid';
        } else {
            scheduleOptions.style.display = 'none';
        }
    }
    
    function toggleCronInput() {
        if (frequencySelect.value === 'cron') {
            cronInput.style.display = 'block';
        } else {
            cronInput.style.display = 'none';
        }
    }
    
    function toggleCaptchaConfig() {
        const selectedDriver = document.querySelector('input[name="driver_type"]:checked');
        if (selectedDriver && selectedDriver.value === 'puppeteer') {
            captchaConfig.style.display = 'block';
        } else {
            captchaConfig.style.display = 'none';
        }
    }
    
    // Event listeners
    scheduleEnabled.addEventListener('change', toggleScheduleOptions);
    frequencySelect.addEventListener('change', toggleCronInput);
    
    // Driver selection change
    driverRadios.forEach(radio => {
        radio.addEventListener('change', toggleCaptchaConfig);
    });
    
    // Initialize state
    toggleScheduleOptions();
    toggleCronInput();
    toggleCaptchaConfig();
});
</script>
@endsection
