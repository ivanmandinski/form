<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll handle authentication via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:2048'],
            'selector_type' => ['required', Rule::in(['id', 'class', 'css'])],
            'selector_value' => ['required', 'string', 'max:255'],
            'method_override' => ['nullable', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'action_override' => ['nullable', 'url', 'max:2048'],
            'driver_type' => ['nullable', Rule::in(['auto', 'http', 'dusk', 'puppeteer'])],
            'uses_js' => ['nullable', 'boolean'],
            'recaptcha_expected' => ['nullable', 'boolean'],
            'success_selector' => ['nullable', 'string', 'max:255'],
            'error_selector' => ['nullable', 'string', 'max:255'],
            'field_mappings' => ['nullable', 'array'],
            'field_mappings.*.name' => ['required_with:field_mappings', 'string', 'max:255'],
            'field_mappings.*.selector' => ['nullable', 'string', 'max:255'],
            'field_mappings.*.value' => ['required_with:field_mappings', 'string', 'max:1000'],
            'field_mappings.*.type' => ['nullable', Rule::in(['text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local', 'checkbox', 'radio', 'file', 'select', 'textarea'])],
            'field_mappings.*.clear_first' => ['nullable', 'boolean'],
            'field_mappings.*.delay' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'wait_for_javascript' => ['nullable', 'boolean'],
            'execute_javascript' => ['nullable', 'string', 'max:5000'],
            'wait_for_elements' => ['nullable', 'array'],
            'wait_for_elements.*' => ['string', 'max:255'],
            'custom_actions' => ['nullable', 'array'],
            'custom_actions.*.type' => ['required_with:custom_actions', Rule::in(['click', 'type', 'select', 'wait', 'waitForSelector', 'evaluate'])],
            'custom_actions.*.selector' => ['required_with:custom_actions', 'string', 'max:255'],
            'custom_actions.*.value' => ['nullable', 'string', 'max:1000'],
            'custom_actions.*.wait_time' => ['nullable', 'integer', 'min:0', 'max:30000'],
            'timeout' => ['nullable', 'integer', 'min:5000', 'max:300000'],
            'headless' => ['nullable', 'boolean'],
            'debug' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'url.required' => 'The URL is required.',
            'url.url' => 'The URL must be a valid URL.',
            'selector_type.required' => 'The selector type is required.',
            'selector_type.in' => 'The selector type must be one of: id, class, css.',
            'selector_value.required' => 'The selector value is required.',
            'driver_type.in' => 'The driver type must be one of: auto, http, dusk, puppeteer.',
            'method_override.in' => 'The method override must be one of: GET, POST, PUT, PATCH, DELETE.',
            'action_override.url' => 'The action override must be a valid URL.',
            'field_mappings.array' => 'Field mappings must be an array.',
            'field_mappings.*.name.required_with' => 'Field mapping name is required when field mappings are provided.',
            'field_mappings.*.value.required_with' => 'Field mapping value is required when field mappings are provided.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'url' => 'URL',
            'selector_type' => 'selector type',
            'selector_value' => 'selector value',
            'method_override' => 'method override',
            'action_override' => 'action override',
            'driver_type' => 'driver type',
            'uses_js' => 'uses JavaScript',
            'recaptcha_expected' => 'reCAPTCHA expected',
            'success_selector' => 'success selector',
            'error_selector' => 'error selector',
            'field_mappings' => 'field mappings',
        ];
    }
}
