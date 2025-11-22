<?php

/**
 * Advanced Puppeteer API Test Script
 * 
 * This script demonstrates the enhanced Puppeteer functionality including:
 * - JavaScript execution
 * - reCAPTCHA solving
 * - Advanced form interactions
 * - Custom actions
 * - Screenshot capture
 */

class AdvancedPuppeteerAPITest
{
    private string $baseUrl;
    private string $token;
    private array $headers;

    public function __construct(string $baseUrl, string $email, string $password)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $this->login($email, $password);
        $this->headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    /**
     * Login and get API token
     */
    private function login(string $email, string $password): string
    {
        $data = json_encode([
            'email' => $email,
            'password' => $password,
            'revoke_existing' => false
        ]);

        $response = $this->makeRequest('POST', '/api/auth/login', $data);
        
        if (!$response['success']) {
            throw new Exception('Login failed: ' . $response['message']);
        }

        return $response['data']['token'];
    }

    /**
     * Test a form with advanced Puppeteer features
     */
    public function testAdvancedForm(array $config): array
    {
        $data = json_encode($config);
        return $this->makeRequest('POST', '/api/forms/test', $data);
    }

    /**
     * Make HTTP request
     */
    private function makeRequest(string $method, string $endpoint, ?string $data = null): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = isset($this->headers) ? $this->headers : ['Content-Type: application/json', 'Accept: application/json'];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120, // Increased timeout for Puppeteer
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }

        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }

        return $decodedResponse;
    }
}

// Example usage
try {
    // Initialize API client
    $api = new AdvancedPuppeteerAPITest(
        'http://localhost:8000',
        'admin@formmonitor.com',
        'password'
    );

    echo "=== Advanced Puppeteer API Test ===\n\n";

    // Test 1: Simple form with JavaScript support
    echo "1. Testing form with JavaScript support...\n";
    $jsFormTest = $api->testAdvancedForm([
        'url' => 'https://httpbin.org/forms/post',
        'selector_type' => 'css',
        'selector_value' => 'form',
        'driver_type' => 'puppeteer',
        'uses_js' => true,
        'wait_for_javascript' => true,
        'field_mappings' => [
            [
                'name' => 'custname',
                'selector' => 'input[name="custname"]',
                'value' => 'John Doe',
                'type' => 'text',
                'clear_first' => true,
                'delay' => 100
            ],
            [
                'name' => 'custemail',
                'selector' => 'input[name="custemail"]',
                'value' => 'john@example.com',
                'type' => 'email',
                'clear_first' => true,
                'delay' => 150
            ],
            [
                'name' => 'comments',
                'selector' => 'textarea[name="comments"]',
                'value' => 'This is a test with advanced Puppeteer features!',
                'type' => 'textarea',
                'clear_first' => true,
                'delay' => 200
            ]
        ],
        'success_selector' => 'h1',
        'timeout' => 60000,
        'debug' => true
    ]);

    if ($jsFormTest['success']) {
        $run = $jsFormTest['data'];
        echo "✅ JavaScript form test completed successfully!\n";
        echo "Status: {$run['status']}\n";
        echo "Final URL: {$run['final_url']}\n";
        echo "Duration: {$run['duration_seconds']} seconds\n";
        echo "Message: {$run['message_excerpt']}\n";
        
        if (!empty($run['artifacts'])) {
            echo "Artifacts: " . count($run['artifacts']) . " files created\n";
            foreach ($run['artifacts'] as $artifact) {
                echo "  - {$artifact['type']}: {$artifact['url']}\n";
            }
        }
    } else {
        echo "❌ JavaScript form test failed: {$jsFormTest['message']}\n";
        if (isset($jsFormTest['error'])) {
            echo "Error details: " . json_encode($jsFormTest['error'], JSON_PRETTY_PRINT) . "\n";
        }
    }

    echo "\n";

    // Test 2: Form with custom JavaScript execution
    echo "2. Testing form with custom JavaScript execution...\n";
    $customJsTest = $api->testAdvancedForm([
        'url' => 'https://httpbin.org/forms/post',
        'selector_type' => 'css',
        'selector_value' => 'form',
        'driver_type' => 'puppeteer',
        'uses_js' => true,
        'execute_javascript' => '
            // Custom JavaScript to execute before form filling
            console.log("Executing custom JavaScript...");
            
            // Add a custom class to the form
            const form = document.querySelector("form");
            if (form) {
                form.classList.add("api-tested");
                console.log("Form marked as API tested");
            }
            
            // Simulate some dynamic behavior
            setTimeout(() => {
                console.log("Custom JavaScript completed");
            }, 1000);
        ',
        'wait_for_elements' => [
            'form',
            'input[name="custname"]',
            'textarea[name="comments"]'
        ],
        'field_mappings' => [
            [
                'name' => 'custname',
                'value' => 'Custom JS Test User',
                'type' => 'text',
                'delay' => 200
            ],
            [
                'name' => 'comments',
                'value' => 'This form was tested with custom JavaScript execution!',
                'type' => 'textarea',
                'delay' => 300
            ]
        ],
        'timeout' => 60000
    ]);

    if ($customJsTest['success']) {
        $run = $customJsTest['data'];
        echo "✅ Custom JavaScript test completed successfully!\n";
        echo "Status: {$run['status']}\n";
        echo "Duration: {$run['duration_seconds']} seconds\n";
    } else {
        echo "❌ Custom JavaScript test failed: {$customJsTest['message']}\n";
    }

    echo "\n";

    // Test 3: Form with custom actions
    echo "3. Testing form with custom actions...\n";
    $customActionsTest = $api->testAdvancedForm([
        'url' => 'https://httpbin.org/forms/post',
        'selector_type' => 'css',
        'selector_value' => 'form',
        'driver_type' => 'puppeteer',
        'uses_js' => true,
        'custom_actions' => [
            [
                'type' => 'wait',
                'wait_time' => 1000
            ],
            [
                'type' => 'click',
                'selector' => 'input[name="custname"]',
                'wait_time' => 500
            ],
            [
                'type' => 'evaluate',
                'value' => 'console.log("Custom action: Focused on name field");'
            ]
        ],
        'field_mappings' => [
            [
                'name' => 'custname',
                'value' => 'Custom Actions Test',
                'type' => 'text',
                'delay' => 100
            ],
            [
                'name' => 'custemail',
                'value' => 'actions@example.com',
                'type' => 'email',
                'delay' => 100
            ]
        ],
        'timeout' => 60000
    ]);

    if ($customActionsTest['success']) {
        $run = $customActionsTest['data'];
        echo "✅ Custom actions test completed successfully!\n";
        echo "Status: {$run['status']}\n";
        echo "Duration: {$run['duration_seconds']} seconds\n";
    } else {
        echo "❌ Custom actions test failed: {$customActionsTest['message']}\n";
    }

    echo "\n";

    // Test 4: Form with reCAPTCHA (if available)
    echo "4. Testing form with reCAPTCHA detection...\n";
    $captchaTest = $api->testAdvancedForm([
        'url' => 'https://www.google.com/recaptcha/api2/demo',
        'selector_type' => 'css',
        'selector_value' => 'form',
        'driver_type' => 'puppeteer',
        'uses_js' => true,
        'recaptcha_expected' => true,
        'wait_for_javascript' => true,
        'field_mappings' => [
            [
                'name' => 'name',
                'value' => 'CAPTCHA Test User',
                'type' => 'text'
            ],
            [
                'name' => 'email',
                'value' => 'captcha@example.com',
                'type' => 'email'
            ]
        ],
        'timeout' => 120000, // 2 minutes for CAPTCHA solving
        'debug' => true
    ]);

    if ($captchaTest['success']) {
        $run = $captchaTest['data'];
        echo "✅ CAPTCHA test completed successfully!\n";
        echo "Status: {$run['status']}\n";
        echo "Duration: {$run['duration_seconds']} seconds\n";
        echo "CAPTCHA detected: " . ($run['captcha_detected'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ CAPTCHA test failed: {$captchaTest['message']}\n";
        echo "Note: This is expected if no CAPTCHA solver API key is configured\n";
    }

    echo "\n=== All tests completed ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
