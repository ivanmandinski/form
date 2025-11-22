@extends('admin.layout')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Check Run Details') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Run #{{ $run->id }}</h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.runs.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        ← Back to Runs
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Run Information</h4>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($run->status === 'success') bg-green-100 text-green-800
                                    @elseif($run->status === 'failure') bg-yellow-100 text-yellow-800
                                    @elseif($run->status === 'blocked') bg-red-100 text-red-800
                                    @elseif($run->status === 'error') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </dd>
                        </div>
                        
                                                       <div>
                                   <dt class="text-sm font-medium text-gray-600">Driver</dt>
                                   <dd class="mt-1 text-sm text-gray-900">
                                       <div class="flex items-center space-x-2">
                                           <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                               @if($run->driver === 'puppeteer') bg-green-100 text-green-800
                                               @elseif($run->driver === 'dusk') bg-purple-100 text-purple-800
                                               @else bg-blue-100 text-blue-800
                                               @endif">
                                               {{ ucfirst($run->driver) }}
                                           </span>
                                           @if($run->driver === 'puppeteer')
                                               <span class="text-xs text-gray-500" title="Real browser with CAPTCHA handling">🌐</span>
                                           @elseif($run->driver === 'dusk')
                                               <span class="text-xs text-gray-500" title="ChromeDriver automation">⚠️</span>
                                           @else
                                               <span class="text-xs text-gray-500" title="HTTP-based testing">📡</span>
                                           @endif
                                       </div>
                                   </dd>
                               </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Started At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $run->started_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        
                        @if($run->finished_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Finished At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $run->finished_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Duration</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $run->started_at->diffForHumans($run->finished_at, true) }}</dd>
                        </div>
                        @endif
                        
                        @if($run->http_status)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">HTTP Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $run->http_status }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Form Target</h4>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Target URL</dt>
                            <dd class="mt-1">
                                <a href="{{ $run->formTarget->target->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm break-all">
                                    {{ $run->formTarget->target->url }}
                                </a>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Selector</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                    {{ $run->formTarget->selector_type }}: {{ $run->formTarget->selector_value }}
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Method</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $run->formTarget->method_override ?: 'Default' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            
                               <!-- Puppeteer Information -->
                   @if($run->driver === 'puppeteer')
                   <div class="mb-8">
                       <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Puppeteer Details</h4>
                       <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                           <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                               <div>
                                   <dt class="text-sm font-medium text-green-800">Browser Used</dt>
                                   <dd class="mt-1 text-sm text-green-700">Real Chrome/Chromium Browser</dd>
                               </div>
                               <div>
                                   <dt class="text-sm font-medium text-green-800">CAPTCHA Handling</dt>
                                   <dd class="mt-1 text-sm text-green-700">Automatic detection and solving</dd>
                               </div>
                               <div>
                                   <dt class="text-sm font-medium text-green-800">Stealth Mode</dt>
                                   <dd class="mt-1 text-sm text-green-700">Bot detection avoidance enabled</dd>
                               </div>
                               <div>
                                   <dt class="text-sm font-medium text-green-800">Screenshots</dt>
                                   <dd class="mt-1 text-sm text-green-700">Before/after submission captured</dd>
                               </div>
                           </div>
                       </div>
                   </div>
                   @endif

                   <!-- Error Details -->
                   @if($run->error_detail)
                   <div class="mb-8">
                       <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Error Details</h4>
                       <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                           <pre class="text-sm text-red-800 whitespace-pre-wrap">{{ json_encode($run->error_detail, JSON_PRETTY_PRINT) }}</pre>
                       </div>
                   </div>
                   @endif

                   <!-- Debug Info -->
                   @php
                       $debugInfoArtifact = $run->artifacts->firstWhere('type', 'debug_info');
                       $debugInfoContent = null;
                       if ($debugInfoArtifact && $debugInfoArtifact->content) {
                           $decoded = json_decode($debugInfoArtifact->content, true);
                           $debugInfoContent = $decoded !== null ? json_encode($decoded, JSON_PRETTY_PRINT) : $debugInfoArtifact->content;
                       }
                   @endphp
                   @if($debugInfoContent)
                   <div class="mb-8">
                       <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Debug Info</h4>
                       <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                           <pre class="text-sm text-red-800 whitespace-pre-wrap">{{ $debugInfoContent }}</pre>
                       </div>
                   </div>
                   @endif
            
            <!-- Message Excerpt -->
            @if($run->message_excerpt)
            <div class="mb-8">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Response Message</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-800">{{ $run->message_excerpt }}</p>
                </div>
            </div>
            @endif
            
            <!-- Final URL -->
            @if($run->final_url && $run->final_url !== $run->formTarget->target->url)
            <div class="mb-8">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Final URL</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <a href="{{ $run->final_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm break-all">
                        {{ $run->final_url }}
                    </a>
                </div>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <form action="{{ route('admin.runs.destroy', $run) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this check run? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Delete Run
                    </button>
                </form>
                @if($run->formTarget)
                <a href="{{ route('admin.forms.edit', $run->formTarget) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Form
                </a>
                @endif
                <a href="{{ route('admin.runs.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Back to Runs
                </a>
            </div>
        </div>
    </div>
@endsection
