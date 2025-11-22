@extends('admin.layout')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Admin Dashboard') }}
    </h2>
@endsection

@section('content')
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl mb-6 sm:mb-8">
        <div class="px-4 sm:px-6 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Welcome back, Admin!</h1>
                    <p class="text-blue-100 text-base sm:text-lg">Here's what's happening with your form monitoring system</p>
                </div>
                <div class="hidden sm:block">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 sm:mb-8">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.targets.create') }}" class="group relative bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 sm:p-6 hover:from-green-600 hover:to-green-700 transition-all duration-200 transform hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h4 class="text-base sm:text-lg font-semibold text-white">Create Target</h4>
                            <p class="text-green-100 text-sm">Add a new website to monitor</p>
                        </div>
                    </div>
                    <div class="absolute top-3 right-3 sm:top-4 sm:right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <a href="{{ route('admin.forms.create') }}" class="group relative bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 sm:p-6 hover:from-blue-600 hover:to-blue-700 transition-all duration-200 transform hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h4 class="text-base sm:text-lg font-semibold text-white">Add Form</h4>
                            <p class="text-blue-100 text-sm">Set up form monitoring</p>
                        </div>
                    </div>
                    <div class="absolute top-3 right-3 sm:top-4 sm:right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <a href="{{ route('admin.runs.index') }}" class="group relative bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 sm:p-6 hover:from-purple-600 hover:to-purple-700 transition-all duration-200 transform hover:-translate-y-1 hover:shadow-lg sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h4 class="text-base sm:text-lg font-semibold text-white">View Runs</h4>
                            <p class="text-purple-100 text-sm">Check monitoring results</p>
                        </div>
                    </div>
                    <div class="absolute top-3 right-3 sm:top-4 sm:right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Stats provided by DashboardController -->

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg Response Time</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ number_format($avgDuration, 2) }}s</p>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4">
                    <p class="text-xs text-gray-500">Average duration of checks in last 24h</p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Forms</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $totalForms }}</p>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4">
                    <a href="{{ route('admin.forms.index') }}" class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center">
                        View all forms
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Runs</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $totalRuns }}</p>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4">
                    <a href="{{ route('admin.runs.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium flex items-center">
                        View all runs
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-sm font-medium text-gray-600">Success Rate</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $successRate }}%</p>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ $successRate }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $successfulRuns }} of {{ $totalRuns }} successful</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & System Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Recent Check Runs -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Check Runs</h3>
                    <a href="{{ route('admin.runs.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        View all
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <!-- Recent runs provided by controller -->
                
                @if($recentRuns->count() > 0)
                    <div class="space-y-3 sm:space-y-4">
                        @foreach($recentRuns as $run)
                            <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                                    <div class="flex-shrink-0">
                                        @if($run->status === 'success')
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @elseif($run->status === 'failure')
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                        @elseif($run->status === 'blocked')
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate text-sm sm:text-base">
                                            {{ $run->formTarget->target->url ?? 'Unknown Target' }}
                                        </p>
                                        <p class="text-xs sm:text-sm text-gray-500 truncate">
                                            {{ $run->formTarget->selector_value }} - {{ $run->driver }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-1 ml-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($run->status === 'success') bg-green-100 text-green-800
                                        @elseif($run->status === 'failure') bg-yellow-100 text-yellow-800
                                        @elseif($run->status === 'blocked') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($run->status) }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $run->started_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No check runs yet</h3>
                        <p class="text-sm text-gray-500">Start monitoring your forms to see activity here</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">System Status</h3>
            </div>
            <div class="p-4 sm:p-6">
                <div class="space-y-4 sm:space-y-6">
                    <!-- Targets Status -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Targets</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalTargets }}</span>
                            <span class="text-sm text-gray-500">active</span>
                        </div>
                    </div>

                    <!-- Forms Status -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Forms</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalForms }}</span>
                            <span class="text-sm text-gray-500">monitored</span>
                        </div>
                    </div>

                    <!-- Monitoring Status -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Monitoring</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalRuns }}</span>
                            <span class="text-sm text-gray-500">runs</span>
                        </div>
                    </div>

                    <!-- Success Rate -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Success Rate</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ $successRate }}%</span>
                            <span class="text-sm text-gray-500">overall</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Quick Stats</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl font-bold text-blue-600">{{ \App\Models\CheckRun::where('status', 'pending')->count() }}</div>
                            <div class="text-xs text-gray-500">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl font-bold text-red-600">{{ \App\Models\CheckRun::where('status', 'blocked')->count() }}</div>
                            <div class="text-xs text-gray-500">Blocked</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
