<?php

/**
 * Simple API Test Script for Form Monitor
 * 
 * This script demonstrates how to use the Form Monitor API
 * to test web forms programmatically.
 */

class FormMonitorAPITest
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
     * Test a form with full configuration
     */
    public function testForm(array $config): array
    {
        $data = json_encode($config);
        return $this->makeRequest('POST', '/api/forms/test', $data);
    }

    /**
     * Test a form by FormTarget ID
     */
    public function testFormById(int $formTargetId, array $overrides = []): array
    {
        $data = json_encode($overrides);
        return $this->makeRequest('POST', "/api/forms/test/{$formTargetId}", $data);
    }

    /**
     * Get all form targets
     */
    public function getFormTargets(array $filters = []): array
    {
        $query = http_build_query($filters);
        $url = '/api/forms' . ($query ? '?' . $query : '');
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get check run history for a form target
     */
    public function getCheckRunHistory(int $formTargetId, array $filters = []): array
    {
        $query = http_build_query($filters);
        $url = "/api/forms/{$formTargetId}/runs" . ($query ? '?' . $query : '');
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get user information
     */
    public function getUserInfo(): array
    {
        return $this->makeRequest('GET', '/api/auth/me');
    }

    /**
     * Make HTTP request
     */
    private function makeRequest(string $method, string $endpoint, string $data = null): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false, // For testing only
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
    $api = new FormMonitorAPITest(
        'http://localhost:8000', // Your API base URL
        'admin@example.com',     // Your email
        'password'               // Your password
    );

    echo "=== Form Monitor API Test ===\n\n";

    // Get user info
    echo "1. Getting user information...\n";
    $userInfo = $api->getUserInfo();
    echo "User: {$userInfo['data']['user']['name']} ({$userInfo['data']['user']['email']})\n";
    echo "Roles: " . implode(', ', $userInfo['data']['user']['roles']) . "\n\n";

    // Test a simple contact form
    echo "2. Testing a contact form...\n";
    $formTest = $api->testForm([
        'url' => 'https://httpbin.org/forms/post',
        'selector_type' => 'css',
        'selector_value' => 'form',
        'driver_type' => 'http',
        'success_selector' => 'h1',
        'field_mappings' => [
            [
                'name' => 'custname',
                'value' => 'John Doe'
            ],
            [
                'name' => 'custtel',
                'value' => '555-1234'
            ],
            [
                'name' => 'custemail',
                'value' => 'john@example.com'
            ],
            [
                'name' => 'size',
                'value' => 'large'
            ],
            [
                'name' => 'topping',
                'value' => 'bacon'
            ],
            [
                'name' => 'delivery',
                'value' => '20:00'
            ],
            [
                'name' => 'comments',
                'value' => 'This is a test from the API'
            ]
        ]
    ]);

    if ($formTest['success']) {
        $run = $formTest['data'];
        echo "✅ Form test completed successfully!\n";
        echo "Status: {$run['status']}\n";
        echo "HTTP Status: {$run['http_status']}\n";
        echo "Final URL: {$run['final_url']}\n";
        echo "Duration: {$run['duration_seconds']} seconds\n";
        echo "Message: {$run['message_excerpt']}\n";
        
        if (!empty($run['artifacts'])) {
            echo "Artifacts: " . count($run['artifacts']) . " files created\n";
        }
    } else {
        echo "❌ Form test failed: {$formTest['message']}\n";
    }

    echo "\n";

    // Get all form targets
    echo "3. Getting all form targets...\n";
    $formTargets = $api->getFormTargets(['per_page' => 5]);
    
    if ($formTargets['success']) {
        echo "Found {$formTargets['pagination']['total']} form targets\n";
        foreach ($formTargets['data'] as $target) {
            echo "- {$target['target']['name']} ({$target['target']['url']})\n";
        }
    } else {
        echo "❌ Failed to get form targets: {$formTargets['message']}\n";
    }

    echo "\n=== Test completed ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
