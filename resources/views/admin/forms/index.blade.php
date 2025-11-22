@extends('admin.layout')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Form Targets') }}
    </h2>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- CAPTCHA & Puppeteer Notice -->
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">CAPTCHA & Browser Support</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p><strong>CAPTCHA Handling:</strong> Forms with CAPTCHAs automatically use Puppeteer real browser for better success rates.</p>
                    <p class="mt-1"><strong>JavaScript Forms:</strong> Forms marked with "Dusk" driver use HTTP testing by default for reliability.</p>
                    <p class="mt-1">Configure CAPTCHA solver in your <code class="bg-blue-100 px-2 py-1 rounded text-xs">.env</code> file with <code class="bg-blue-100 px-2 py-1 rounded text-xs">CAPTCHA_SOLVER_API_KEY</code></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">Form Targets</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.targets.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Create Target
                    </a>
                    <a href="{{ route('admin.forms.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Add Form
                    </a>
                </div>
            </div>
            
            @php
                $forms = \App\Models\FormTarget::with(['target', 'checkRuns' => function($query) {
                    $query->latest()->limit(1);
                }])->latest()->get();
            @endphp
            
            @if($forms->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200" style="table-layout: fixed;">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Selector</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Driver</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Last Run</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6 min-w-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($forms as $form)
                                @php
                                    $lastRun = $form->checkRuns->first();
                                    $status = $lastRun ? $lastRun->status : 'Never Run';
                                    $statusColor = match($status) {
                                        'success' => 'bg-green-100 text-green-800',
                                        'failure' => 'bg-yellow-100 text-yellow-800',
                                        'blocked' => 'bg-red-100 text-red-800',
                                        'error' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ $form->target->url }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                {{ Str::limit($form->target->url, 40) }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                {{ $form->selector_type }}: {{ $form->selector_value }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if($form->recaptcha_expected)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Puppeteer
                                                </span>
                                                <span class="text-xs text-gray-500" title="Uses real browser for CAPTCHA handling">🌐</span>
                                            @elseif($form->uses_js)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Dusk
                                                </span>
                                                <span class="text-xs text-gray-500" title="Requires ChromeDriver for full functionality">⚠️</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    HTTP
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($form->schedule_enabled)
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium">{{ ucfirst($form->schedule_frequency) }}</span>
                                                @if($form->schedule_frequency === 'cron')
                                                    <br><span class="text-xs text-gray-500">{{ $form->schedule_cron }}</span>
                                                @endif
                                                @if($form->schedule_next_run_at)
                                                    <br><span class="text-xs text-gray-500">Next: {{ $form->schedule_next_run_at->format('M j, H:i') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Manual</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($lastRun)
                                            {{ $lastRun->started_at->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium w-1/6 min-w-0">
                                        <div class="flex flex-col space-y-2">
                                            <form action="{{ route('admin.forms.run', $form) }}" method="POST" class="w-full">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-1 border border-green-300 text-green-700 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md text-sm font-medium transition-colors duration-200" onclick="return confirm('Are you sure you want to run this form now?')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Run Now
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.forms.edit', $form) }}" class="w-full inline-flex items-center justify-center px-3 py-1 border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 hover:text-blue-800 rounded-md text-sm font-medium transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" class="w-full">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-1 border border-red-300 text-red-700 bg-red-50 hover:bg-red-100 hover:text-red-800 rounded-md text-sm font-medium transition-colors duration-200" onclick="return confirm('Are you sure you want to delete this form target? This action cannot be undone and will also delete all associated check runs.')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H9a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No forms found.</p>
                    <a href="{{ route('admin.forms.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Add your first form
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
