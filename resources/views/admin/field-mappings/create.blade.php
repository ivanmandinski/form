@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Add Field Mapping</h1>
                <a href="{{ route('admin.field-mappings.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Field Mappings
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.field-mappings.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Form Target Selection -->
            <div>
                <label for="form_target_id" class="block text-sm font-medium text-gray-700 mb-2">Form Target</label>
                <select name="form_target_id" id="form_target_id" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a form target</option>
                    @foreach($formTargets as $formTarget)
                        <option value="{{ $formTarget->id }}" {{ old('form_target_id') == $formTarget->id ? 'selected' : '' }}>
                            {{ $formTarget->target->url }} - {{ $formTarget->selector_value }}
                        </option>
                    @endforeach
                </select>
                @error('form_target_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">Choose the form target to map fields for</p>
            </div>
            
            <!-- Field Configuration -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="field_name" class="block text-sm font-medium text-gray-700 mb-2">Field Name</label>
                    <input type="text" name="field_name" id="field_name" required 
                           value="{{ old('field_name') }}"
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., email, name, message">
                    @error('field_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="field_type" class="block text-sm font-medium text-gray-700 mb-2">Field Type</label>
                    <select name="field_type" id="field_type" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="text" {{ old('field_type') == 'text' ? 'selected' : '' }}>Text Input</option>
                        <option value="email" {{ old('field_type') == 'email' ? 'selected' : '' }}>Email Input</option>
                        <option value="textarea" {{ old('field_type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                        <option value="select" {{ old('field_type') == 'select' ? 'selected' : '' }}>Select Dropdown</option>
                        <option value="checkbox" {{ old('field_type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                        <option value="radio" {{ old('field_type') == 'radio' ? 'selected' : '' }}>Radio Button</option>
                    </select>
                    @error('field_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Selector Configuration -->
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
                           placeholder="e.g., #email, input[name='email'], //input[@name='email']">
                    @error('selector_value')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Field Value -->
            <div>
                <label for="field_value" class="block text-sm font-medium text-gray-700 mb-2">Field Value</label>
                <input type="text" name="field_value" id="field_value" 
                       value="{{ old('field_value') }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Value to fill in the field (leave empty for dynamic values)">
                @error('field_value')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">Leave empty to use dynamic values or enter a static value</p>
            </div>
            
            <!-- Required Field -->
            <div>
                <label for="is_required" class="flex items-center">
                    <input type="checkbox" name="is_required" id="is_required" value="1" 
                           {{ old('is_required') ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">This field is required</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">Check if this field must be filled before form submission</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.field-mappings.index') }}" 
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
                    Create Field Mapping
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
