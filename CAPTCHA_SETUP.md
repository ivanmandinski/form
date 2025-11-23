# CAPTCHA Bypass Setup Guide

This guide explains how to configure CAPTCHA solving for automated form testing.

## Overview

The Form Monitor application supports three methods for handling CAPTCHA:

1. **CAPTCHA Solving Services** (2captcha, AntiCaptcha) - For production/testing
2. **Google reCAPTCHA Test Keys** - For your own development sites
3. **Conditional Bypass** - For testing environments

## Method 1: Using CAPTCHA Solving Services

### 2captcha Setup

1. **Sign up for 2captcha**: Visit https://2captcha.com and create an account
2. **Get your API key**: Copy your API key from the dashboard
3. **Configure environment variables**:

```env
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key_here
CAPTCHA_SOLVER_PROVIDER=2captcha
CAPTCHA_SOLVER_TIMEOUT=120
```

### AntiCaptcha Setup

1. **Sign up for AntiCaptcha**: Visit https://anti-captcha.com and create an account
2. **Get your API key**: Copy your API key from the dashboard
3. **Configure environment variables**:

```env
CAPTCHA_SOLVER_API_KEY=your_anticaptcha_api_key_here
CAPTCHA_SOLVER_PROVIDER=anticaptcha
CAPTCHA_SOLVER_TIMEOUT=120
```

### Pricing

- **2captcha**: ~$2.99 per 1000 reCAPTCHA v2 solves
- **AntiCaptcha**: ~$1.00 per 1000 reCAPTCHA v2 solves

Both services offer free trial credits for testing.

## Method 2: Using Google reCAPTCHA Test Keys

For your own development/testing sites, you can use Google's test keys that always pass validation.

### Test Keys

- **Site Key**: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
- **Secret Key**: `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`

### How It Works

When the CAPTCHA solver detects these test keys, it automatically injects a test token without calling any external service. This is **free** and works instantly.

### Usage

Simply configure your form to use the test site key. The solver will automatically detect it and bypass it.

**Important**: These keys only work for testing. They will NOT work on production sites with real reCAPTCHA keys.

## Method 3: Conditional Bypass for Testing

If you control the form's backend, you can add conditional logic to bypass CAPTCHA during testing:

### PHP Example (WordPress/WPForms)

```php
add_filter('wpforms_process_bypass_captcha', function($bypass, $entry, $form_data) {
    // Bypass CAPTCHA if testing environment
    if (defined('TESTING_ENVIRONMENT') && TESTING_ENVIRONMENT === true) {
        return true;
    }
    
    // Bypass for specific user agents
    if (isset($_SERVER['HTTP_USER_AGENT']) && 
        strpos($_SERVER['HTTP_USER_AGENT'], 'Form Monitor Bot') !== false) {
        return true;
    }
    
    return $bypass;
}, 10, 3);
```

### Laravel Example

```php
// In your form validation
if (config('app.env') === 'testing' || 
    request()->header('User-Agent') === 'Form Monitor Bot/1.0') {
    // Skip CAPTCHA validation
    return true;
}
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# CAPTCHA Solver Configuration
CAPTCHA_SOLVER_API_KEY=your_api_key_here
CAPTCHA_SOLVER_PROVIDER=2captcha  # or 'anticaptcha'
CAPTCHA_SOLVER_TIMEOUT=120  # seconds

# Optional: Disable CAPTCHA solving entirely
# CAPTCHA_SOLVER_ENABLED=false
```

### Config File

The configuration is also available in `config/form-monitor.php`:

```php
'captcha' => [
    'api_key' => env('CAPTCHA_SOLVER_API_KEY', ''),
    'provider' => env('CAPTCHA_SOLVER_PROVIDER', '2captcha'),
    'timeout' => env('CAPTCHA_SOLVER_TIMEOUT', 120),
    'retry_attempts' => env('CAPTCHA_SOLVER_RETRY_ATTEMPTS', 3),
    'solve_score_based' => env('CAPTCHA_SOLVE_SCORE_BASED', true),
    'solve_inactive_challenges' => env('CAPTCHA_SOLVE_INACTIVE_CHALLENGES', true),
    'solve_recaptcha_v3' => env('CAPTCHA_SOLVE_RECAPTCHA_V3', true),
    'solve_recaptcha_v2' => env('CAPTCHA_SOLVE_RECAPTCHA_V2', true),
    'solve_hcaptcha' => env('CAPTCHA_SOLVE_HCAPTCHA', true),
    'visual_feedback' => env('CAPTCHA_VISUAL_FEEDBACK', true),
],
```

## How It Works

1. **Detection**: The Puppeteer script automatically detects CAPTCHA presence on the page
2. **Type Identification**: Identifies the CAPTCHA type (reCAPTCHA v2/v3, hCaptcha)
3. **Site Key Extraction**: Extracts the site key from the page
4. **Test Key Check**: If it's a test key, injects test token immediately
5. **Service Solving**: If not a test key and API key is configured, submits to solving service
6. **Token Injection**: Injects the solved token into the form
7. **Form Submission**: Proceeds with form submission

## Supported CAPTCHA Types

- ✅ Google reCAPTCHA v2 (checkbox)
- ✅ Google reCAPTCHA v3 (invisible/score-based)
- ✅ hCaptcha
- ⚠️ reCAPTCHA v1 (deprecated, not supported)

## Troubleshooting

### CAPTCHA Not Being Solved

1. **Check API key**: Ensure `CAPTCHA_SOLVER_API_KEY` is set correctly
2. **Check provider**: Verify `CAPTCHA_SOLVER_PROVIDER` is '2captcha' or 'anticaptcha'
3. **Check balance**: Ensure your solving service account has credits
4. **Check logs**: Look for CAPTCHA-related errors in the Puppeteer output

### Test Keys Not Working

- Test keys only work if the form's backend is configured to accept them
- Make sure you're using the exact test site key: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
- The backend must use the matching test secret key for validation

### Timeout Issues

- Increase `CAPTCHA_SOLVER_TIMEOUT` if solving takes longer
- Check your solving service's average solve time
- Some CAPTCHAs may take 30-60 seconds to solve

## Security & Legal Considerations

⚠️ **Important**: 

- Only use CAPTCHA solving for forms you own or have explicit permission to test
- Never use this to bypass CAPTCHA on sites you don't own (this violates terms of service)
- CAPTCHA solving services cost money - monitor your usage
- Test keys should only be used in development/testing environments
- Always respect website terms of service and robots.txt

## Cost Estimation

For regular form monitoring:
- **Hourly checks**: ~720 CAPTCHA solves/month = ~$2-3/month (2captcha) or ~$0.72/month (AntiCaptcha)
- **Daily checks**: ~30 CAPTCHA solves/month = ~$0.10/month

## Example Usage

### Via Admin Interface

1. Create a new Form Target
2. Set `Driver Type` to `puppeteer`
3. Check `reCAPTCHA Expected`
4. Configure your CAPTCHA solver API key in `.env`
5. Save and run the form check

### Via API

```bash
curl -X POST https://your-domain.com/api/forms/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/contact",
    "selector_type": "css",
    "selector_value": "form#contact-form",
    "driver_type": "puppeteer",
    "recaptcha_expected": true,
    "field_mappings": [
      {"name": "name", "value": "Test User"},
      {"name": "email", "value": "test@example.com"}
    ]
  }'
```

## Support

For issues or questions:
- Check the application logs: `storage/logs/laravel.log`
- Check Puppeteer debug output: Set `PUPPETEER_DEBUG=true` in `.env`
- Review the CAPTCHA solver service documentation


