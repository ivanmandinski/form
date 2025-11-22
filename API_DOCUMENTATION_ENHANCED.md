# Form Monitor API Documentation - Enhanced Edition

## Overview

The Form Monitor API provides comprehensive endpoints for testing web forms and monitoring their functionality. It supports multiple testing methods including HTTP requests, Puppeteer (for JavaScript-heavy forms with advanced features), and Dusk (for complex interactions).

### Key Features

- **Multiple Testing Drivers**: HTTP, Puppeteer, Dusk
- **Advanced Puppeteer Support**: JavaScript execution, reCAPTCHA solving, custom actions
- **Flexible Form Configuration**: CSS selectors, field mappings, success/error detection
- **Comprehensive Response Data**: Status, timing, artifacts, error details, screenshots
- **Secure Authentication**: Laravel Sanctum token-based authentication
- **Rate Limiting**: Built-in protection against abuse
- **Artifact Management**: Download HTML, screenshots, debug information
- **Pagination**: Efficient data retrieval for large datasets
- **Real-time Monitoring**: Live form testing capabilities

## Base URL

```
http://your-domain.com/api
```

## Authentication

The API uses Laravel Sanctum for authentication. You need to obtain a Bearer token to access protected endpoints.

### Getting an API Token

#### Login and Get Token

```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "your-email@example.com",
    "password": "your-password",
    "revoke_existing": false
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "roles": ["admin"]
        },
        "token": "1|abcdef123456789...",
        "token_type": "Bearer",
        "expires_at": "2024-12-31T23:59:59.000000Z"
    }
}
```

#### Create Additional Token

```http
POST /api/auth/token
Authorization: Bearer your-token-here
Content-Type: application/json

{
    "token_name": "My API Token",
    "abilities": ["form-test"],
    "revoke_existing": false
}
```

### Using the Token

Include the token in the Authorization header:

```http
Authorization: Bearer your-token-here
```

## Endpoints

### 1. Test Form with Full Configuration

Test a form by providing all necessary configuration details.

```http
POST /api/forms/test
Authorization: Bearer your-token-here
Content-Type: application/json

{
    "url": "https://example.com/contact",
    "selector_type": "id",
    "selector_value": "contact-form",
    "method_override": "POST",
    "action_override": "https://example.com/contact/submit",
    "driver_type": "auto",
    "uses_js": false,
    "recaptcha_expected": false,
    "success_selector": ".success-message",
    "error_selector": ".error-message",
    "field_mappings": [
        {
            "name": "name",
            "selector": "input[name='name']",
            "value": "John Doe"
        },
        {
            "name": "email",
            "selector": "input[name='email']",
            "value": "john@example.com"
        },
        {
            "name": "message",
            "selector": "textarea[name='message']",
            "value": "Hello, this is a test message."
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Form test completed successfully",
    "data": {
        "id": 123,
        "form_target_id": 456,
        "driver": "http",
        "status": "success",
        "http_status": 200,
        "final_url": "https://example.com/contact/thank-you",
        "message_excerpt": "Thank you for your message!",
        "error_detail": null,
        "started_at": "2024-01-15T10:30:00.000000Z",
        "finished_at": "2024-01-15T10:30:05.000000Z",
        "duration_seconds": 5,
        "is_successful": true,
        "is_blocked": false,
        "is_error": false,
        "artifacts": [
            {
                "id": 789,
                "check_run_id": 123,
                "type": "html",
                "path": "artifacts/abc123_123.html",
                "url": "http://your-domain.com/api/artifacts/789/download",
                "created_at": "2024-01-15T10:30:05.000000Z"
            }
        ]
    }
}
```

### 2. Test Form by ID

Test a form using an existing FormTarget configuration.

```http
POST /api/forms/test/{formTargetId}
Authorization: Bearer your-token-here
Content-Type: application/json

{
    "driver_type": "puppeteer"
}
```

### 3. Get All Form Targets

Retrieve all available form targets with optional filtering.

```http
GET /api/forms?active_only=true&driver_type=puppeteer&per_page=20
Authorization: Bearer your-token-here
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "target_id": 1,
            "selector_type": "id",
            "selector_value": "contact-form",
            "driver_type": "auto",
            "uses_js": false,
            "recaptcha_expected": false,
            "schedule_enabled": true,
            "target": {
                "id": 1,
                "name": "Contact Form",
                "url": "https://example.com/contact"
            },
            "field_mappings": [
                {
                    "id": 1,
                    "name": "name",
                    "selector": "input[name='name']",
                    "value": "Test User"
                }
            ],
            "latest_check_run": {
                "id": 123,
                "status": "success",
                "started_at": "2024-01-15T10:30:00.000000Z"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### 4. Get Specific Form Target

```http
GET /api/forms/{formTargetId}
Authorization: Bearer your-token-here
```

### 5. Get Check Run History

Retrieve the check run history for a specific form target.

```http
GET /api/forms/{formTargetId}/runs?status=success&driver=http&limit=10&per_page=20
Authorization: Bearer your-token-here
```

### 6. Get Specific Check Run

```http
GET /api/runs/{checkRunId}
Authorization: Bearer your-token-here
```

### 7. Download Artifact

Download HTML content, screenshots, or debug information from a check run.

```http
GET /api/artifacts/{artifactId}/download
Authorization: Bearer your-token-here
```

### 8. User Information

Get current user information and token details.

```http
GET /api/auth/me
Authorization: Bearer your-token-here
```

### 9. Logout

Revoke the current token.

```http
POST /api/auth/logout
Authorization: Bearer your-token-here
```

## Request Parameters

### Form Test Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `url` | string | Yes | The URL of the page containing the form |
| `selector_type` | string | Yes | Type of selector: `id`, `class`, or `css` |
| `selector_value` | string | Yes | The selector value to find the form |
| `method_override` | string | No | HTTP method override (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`) |
| `action_override` | string | No | Form action URL override |
| `driver_type` | string | No | Testing driver: `auto`, `http`, `dusk`, `puppeteer` |
| `uses_js` | boolean | No | Whether the form uses JavaScript |
| `recaptcha_expected` | boolean | No | Whether the form has reCAPTCHA |
| `success_selector` | string | No | CSS selector for success message |
| `error_selector` | string | No | CSS selector for error message |
| `field_mappings` | array | No | Array of field mappings |
| `wait_for_javascript` | boolean | No | Wait for JavaScript to load (default: true for Puppeteer) |
| `execute_javascript` | string | No | Custom JavaScript code to execute before form filling |
| `wait_for_elements` | array | No | Array of CSS selectors to wait for before proceeding |
| `custom_actions` | array | No | Array of custom actions to perform |
| `timeout` | integer | No | Timeout in milliseconds (5000-300000, default: 30000) |
| `headless` | boolean | No | Run browser in headless mode (default: true) |
| `debug` | boolean | No | Enable debug logging (default: false) |

### Field Mapping Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Form field name |
| `selector` | string | No | CSS selector for the field (defaults to name) |
| `value` | string | Yes | Value to fill in the field |
| `type` | string | No | Field type: `text`, `email`, `password`, `number`, `tel`, `url`, `search`, `date`, `time`, `datetime-local`, `checkbox`, `radio`, `file`, `select`, `textarea` |
| `clear_first` | boolean | No | Clear existing value before filling (default: true) |
| `delay` | integer | No | Delay between keystrokes in milliseconds (0-5000, default: 100) |

### Custom Action Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | Yes | Action type: `click`, `type`, `select`, `wait`, `waitForSelector`, `evaluate` |
| `selector` | string | Required for most types | CSS selector for the element |
| `value` | string | Required for `type`, `select`, `evaluate` | Value to type, select, or JavaScript code to execute |
| `wait_time` | integer | No | Wait time in milliseconds after action (0-30000, default: 1000) |

## Response Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

## Status Values

### Check Run Status

- `success`: Form submission was successful
- `failure`: Form submission failed
- `blocked`: Request was blocked (e.g., CAPTCHA, rate limiting)
- `error`: An error occurred during testing

### Driver Types

- `auto`: Automatically choose the best driver based on form requirements
- `http`: Use HTTP requests (fastest, no JavaScript support, good for simple forms)
- `dusk`: Use Laravel Dusk (supports JavaScript, requires Chrome, good for complex interactions)
- `puppeteer`: Use Puppeteer (supports JavaScript, reCAPTCHA solving, custom actions, screenshots)

### Puppeteer Advanced Features

When using `driver_type: "puppeteer"`, you get access to advanced features:

- **JavaScript Execution**: Run custom JavaScript code before form filling
- **reCAPTCHA Solving**: Automatic detection and solving of CAPTCHAs
- **Custom Actions**: Perform custom interactions (click, type, wait, evaluate)
- **Element Waiting**: Wait for specific elements to load
- **Screenshot Capture**: Automatic screenshots for debugging
- **Advanced Field Handling**: Support for all HTML input types
- **Realistic Behavior**: Human-like typing, scrolling, clicking
- **Debug Mode**: Enhanced logging and error reporting

## Error Responses

### Validation Error

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "url": ["The url field is required."],
        "selector_type": ["The selected selector type is invalid."]
    }
}
```

### Authentication Error

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### Server Error

```json
{
    "success": false,
    "message": "Form test failed: Connection timeout",
    "error": {
        "type": "GuzzleHttp\\Exception\\ConnectException",
        "message": "Connection timeout"
    }
}
```

## Rate Limiting

API requests are rate-limited to prevent abuse. The default limits are:

- 60 requests per minute per user
- 1000 requests per hour per user

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Configuration

### Environment Variables

Configure the API behavior using environment variables:

```bash
# API Settings
FORM_MONITOR_USER_AGENT="Form Monitor Bot/1.0"
FORM_MONITOR_HTTP_TIMEOUT=30
FORM_MONITOR_PUPPETEER_TIMEOUT=120

# Puppeteer Settings
PUPPETEER_HEADLESS=true
PUPPETEER_DEBUG=false
PUPPETEER_USER_DATA_DIR=/path/to/user/data

# CAPTCHA Solver Settings (2captcha.com)
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key
CAPTCHA_SOLVER_PROVIDER=2captcha
CAPTCHA_SOLVER_TIMEOUT=120
CAPTCHA_SOLVER_RETRY_ATTEMPTS=3
CAPTCHA_SOLVE_RECAPTCHA_V3=true
CAPTCHA_SOLVE_RECAPTCHA_V2=true
CAPTCHA_SOLVE_HCAPTCHA=true
CAPTCHA_VISUAL_FEEDBACK=true

# Rate Limiting
FORM_MONITOR_MAX_CONCURRENT_PER_HOST=2
FORM_MONITOR_GLOBAL_MAX_CONCURRENT=10

# Artifacts
FORM_MONITOR_ARTIFACT_RETENTION_DAYS=30
FORM_MONITOR_MAX_HTML_SIZE=1048576
FORM_MONITOR_SCREENSHOT_QUALITY=80
```

### CAPTCHA Solver Setup

To enable reCAPTCHA solving, you need to:

1. **Sign up for 2captcha.com** (or another supported service)
2. **Get your API key** from the service dashboard
3. **Add the API key** to your `.env` file:
   ```bash
   CAPTCHA_SOLVER_API_KEY=your_actual_api_key_here
   ```
4. **Test the integration** using a form with reCAPTCHA

### Puppeteer Browser Configuration

The API uses advanced Puppeteer configuration for optimal performance:

- **Stealth Mode**: Avoids bot detection
- **Ad Blocking**: Improves performance and blocks tracking
- **Realistic Headers**: Human-like HTTP headers
- **Optimized Viewport**: Common desktop resolution (1366x768)
- **Advanced Chrome Flags**: 30+ flags for optimal performance

## Examples

### Simple Contact Form Test

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/contact",
    "selector_type": "id",
    "selector_value": "contact-form",
    "field_mappings": [
      {
        "name": "name",
        "value": "John Doe"
      },
      {
        "name": "email",
        "value": "john@example.com"
      },
      {
        "name": "message",
        "value": "Hello from API test!"
      }
    ]
  }'
```

### JavaScript Form with Puppeteer

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/signup",
    "selector_type": "class",
    "selector_value": "signup-form",
    "driver_type": "puppeteer",
    "uses_js": true,
    "recaptcha_expected": true,
    "success_selector": ".success-message",
    "field_mappings": [
      {
        "name": "username",
        "value": "testuser123"
      },
      {
        "name": "email",
        "value": "test@example.com"
      },
      {
        "name": "password",
        "value": "securepassword123"
      }
    ]
  }'
```

## Advanced Puppeteer Examples

### Form with Custom JavaScript Execution

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/contact",
    "selector_type": "id",
    "selector_value": "contact-form",
    "driver_type": "puppeteer",
    "uses_js": true,
    "wait_for_javascript": true,
    "execute_javascript": "console.log(\"Custom JS executed\"); document.querySelector(\"#contact-form\").classList.add(\"api-tested\");",
    "wait_for_elements": [".form-loaded", "#contact-form", "input[name=\"name\"]"],
    "field_mappings": [
      {
        "name": "name",
        "selector": "input[name=\"name\"]",
        "value": "John Doe",
        "type": "text",
        "clear_first": true,
        "delay": 100
      },
      {
        "name": "email",
        "selector": "input[name=\"email\"]",
        "value": "john@example.com",
        "type": "email",
        "clear_first": true,
        "delay": 150
      }
    ],
    "timeout": 60000,
    "debug": true
  }'
```

### Form with Custom Actions

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/multi-step-form",
    "selector_type": "css",
    "selector_value": "form",
    "driver_type": "puppeteer",
    "uses_js": true,
    "custom_actions": [
      {
        "type": "wait",
        "wait_time": 1000
      },
      {
        "type": "click",
        "selector": "button[data-step=\"1\"]",
        "wait_time": 500
      },
      {
        "type": "waitForSelector",
        "selector": ".step-2-loaded",
        "wait_time": 2000
      },
      {
        "type": "evaluate",
        "value": "console.log(\"Custom action: Step 2 loaded\");"
      }
    ],
    "field_mappings": [
      {
        "name": "first_name",
        "value": "John",
        "type": "text",
        "delay": 100
      },
      {
        "name": "last_name",
        "value": "Doe",
        "type": "text",
        "delay": 100
      }
    ],
    "timeout": 60000
  }'
```

### Form with reCAPTCHA Solving

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/protected-form",
    "selector_type": "css",
    "selector_value": "form",
    "driver_type": "puppeteer",
    "uses_js": true,
    "recaptcha_expected": true,
    "wait_for_javascript": true,
    "field_mappings": [
      {
        "name": "username",
        "value": "testuser",
        "type": "text",
        "delay": 100
      },
      {
        "name": "password",
        "value": "testpass",
        "type": "password",
        "delay": 100
      }
    ],
    "timeout": 120000,
    "debug": true
  }'
```

### Complex Form with Multiple Field Types

```bash
curl -X POST "http://your-domain.com/api/forms/test" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/complex-form",
    "selector_type": "id",
    "selector_value": "registration-form",
    "driver_type": "puppeteer",
    "uses_js": true,
    "field_mappings": [
      {
        "name": "email",
        "selector": "input[type=\"email\"]",
        "value": "user@example.com",
        "type": "email",
        "clear_first": true,
        "delay": 100
      },
      {
        "name": "password",
        "selector": "input[type=\"password\"]",
        "value": "securepassword123",
        "type": "password",
        "clear_first": true,
        "delay": 100
      },
      {
        "name": "birth_date",
        "selector": "input[type=\"date\"]",
        "value": "1990-01-01",
        "type": "date",
        "clear_first": true,
        "delay": 100
      },
      {
        "name": "country",
        "selector": "select[name=\"country\"]",
        "value": "United States",
        "type": "select",
        "clear_first": false,
        "delay": 0
      },
      {
        "name": "newsletter",
        "selector": "input[type=\"checkbox\"][name=\"newsletter\"]",
        "value": "true",
        "type": "checkbox",
        "clear_first": false,
        "delay": 0
      },
      {
        "name": "bio",
        "selector": "textarea[name=\"bio\"]",
        "value": "This is my bio text with multiple lines.\nIt includes special characters and formatting.",
        "type": "textarea",
        "clear_first": true,
        "delay": 50
      }
    ],
    "success_selector": ".registration-success",
    "error_selector": ".registration-error",
    "timeout": 60000,
    "debug": true
  }'
```

## Health Check

Check API status without authentication:

```http
GET /api/public/health
```

Response:
```json
{
    "status": "ok",
    "timestamp": "2025-09-05T11:10:43.595732Z",
    "version": "1.0.0"
}
```

## API Documentation Endpoint

Get API documentation without authentication:

```http
GET /api/public/docs
```

## Troubleshooting

### Common Issues

#### 1. Form Not Found
**Error**: `Form not found with selector`
**Solution**: 
- Verify the selector is correct
- Try different selector types (`id`, `class`, `css`)
- Use browser dev tools to inspect the form
- Enable `debug: true` for detailed logging

#### 2. Field Mapping Failures
**Error**: `Could not find field: fieldname`
**Solution**:
- Check field names match exactly
- Try different selector strategies
- Use `clear_first: false` if field has default values
- Increase `delay` for slow-loading forms

#### 3. CAPTCHA Solving Issues
**Error**: `CAPTCHA solving failed`
**Solution**:
- Verify `CAPTCHA_SOLVER_API_KEY` is set correctly
- Check 2captcha account balance
- Increase `timeout` for CAPTCHA solving
- Ensure `recaptcha_expected: true` is set

#### 4. JavaScript Execution Errors
**Error**: `Custom JavaScript execution failed`
**Solution**:
- Validate JavaScript syntax
- Use `console.log()` for debugging
- Check browser console for errors
- Test JavaScript in browser dev tools first

#### 5. Timeout Issues
**Error**: `Request timeout`
**Solution**:
- Increase `timeout` value (up to 300000ms)
- Use `wait_for_elements` for slow-loading content
- Enable `wait_for_javascript: true`
- Check network connectivity

### Debug Mode

Enable debug mode for detailed logging:

```json
{
  "debug": true,
  "driver_type": "puppeteer"
}
```

Debug mode provides:
- Detailed browser console logs
- Screenshot capture on errors
- Step-by-step execution logging
- Enhanced error context

## Best Practices

### 1. Form Testing Strategy
1. **Start Simple**: Test with HTTP driver first
2. **Add Complexity**: Use Puppeteer for JavaScript features
3. **Handle CAPTCHAs**: Enable CAPTCHA solving for protected forms
4. **Monitor Results**: Check success/error selectors

### 2. Field Mapping Best Practices
- Use specific CSS selectors when possible
- Include field types for better handling
- Set appropriate delays for realistic behavior
- Clear existing values unless needed

### 3. Error Handling
- Always check response `success` field
- Use `debug: true` for troubleshooting
- Implement retry logic for transient failures
- Monitor API rate limits

### 4. Security Considerations
- Use HTTPS endpoints when possible
- Rotate API tokens regularly
- Monitor for suspicious activity
- Keep CAPTCHA solver API keys secure

### 5. Performance Optimization
- Choose the right driver for your use case
- Use appropriate timeouts
- Optimize field mappings
- Implement efficient element waiting

## Client Libraries

### PHP Client Example

```php
<?php

class FormMonitorAPI {
    private $baseUrl;
    private $token;
    
    public function __construct($baseUrl, $email, $password) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $this->login($email, $password);
    }
    
    public function testForm($config) {
        $data = json_encode($config);
        return $this->makeRequest('POST', '/api/forms/test', $data);
    }
    
    private function login($email, $password) {
        $data = json_encode([
            'email' => $email,
            'password' => $password
        ]);
        
        $response = $this->makeRequest('POST', '/api/auth/login', $data);
        return $response['data']['token'];
    }
    
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Usage
$api = new FormMonitorAPI('http://localhost:8000', 'admin@formmonitor.com', 'password');

$result = $api->testForm([
    'url' => 'https://example.com/contact',
    'selector_type' => 'id',
    'selector_value' => 'contact-form',
    'driver_type' => 'puppeteer',
    'field_mappings' => [
        [
            'name' => 'name',
            'value' => 'John Doe',
            'type' => 'text'
        ]
    ]
]);

if ($result['success']) {
    echo "Form test successful!\n";
} else {
    echo "Form test failed: " . $result['message'] . "\n";
}
```

### JavaScript/Node.js Client Example

```javascript
class FormMonitorAPI {
    constructor(baseUrl, email, password) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.token = null;
        this.login(email, password);
    }
    
    async login(email, password) {
        const response = await fetch(`${this.baseUrl}/api/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        this.token = data.data.token;
    }
    
    async testForm(config) {
        const response = await fetch(`${this.baseUrl}/api/forms/test`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(config)
        });
        
        return await response.json();
    }
}

// Usage
const api = new FormMonitorAPI('http://localhost:8000', 'admin@formmonitor.com', 'password');

const result = await api.testForm({
    url: 'https://example.com/contact',
    selector_type: 'id',
    selector_value: 'contact-form',
    driver_type: 'puppeteer',
    field_mappings: [
        {
            name: 'name',
            value: 'John Doe',
            type: 'text'
        }
    ]
});

if (result.success) {
    console.log('Form test successful!');
} else {
    console.log('Form test failed:', result.message);
}
```

## Support

For additional support and examples, please refer to:

- **Test Scripts**: `test_api.php` and `test_advanced_puppeteer_api.php`
- **Configuration**: `config/form-monitor.php`
- **Environment Variables**: `.env` file
- **Logs**: `storage/logs/laravel.log`

---

**Version**: 1.0.0  
**Last Updated**: September 5, 2025  
**API Base URL**: `http://your-domain.com/api`
