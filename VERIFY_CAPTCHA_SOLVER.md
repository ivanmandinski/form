# How to Verify 2captcha API is Being Used

This guide explains how to verify that the CAPTCHA solver is correctly configured and using the 2captcha API.

## Quick Verification Methods

### Method 1: Use the Verification Command

Run the built-in verification command:

```bash
php artisan captcha:verify
```

This will:
- ✅ Check if API key is configured
- ✅ Verify API key format
- ✅ Test API connectivity
- ✅ Show your account balance

**Example Output:**
```
🔐 CAPTCHA Solver Configuration Verification

Configuration:
  Provider: 2captcha
  API Key: 12345678...abcd
  Timeout: 120 seconds

API Key Validation:
  ✅ API key length looks correct (32+ characters)

Testing API Connectivity:
  Testing 2captcha API...
  ✅ API connection successful!
  Balance: $5.23

✅ Configuration verified successfully!
   CAPTCHA solving is ENABLED and ready to use.
```

### Method 2: Check Application Logs

When a form check runs, the logs will show CAPTCHA solver configuration:

**In Laravel logs** (`storage/logs/laravel.log`):
```json
{
  "message": "Running Puppeteer form check",
  "captcha_solver": {
    "provider": "2captcha",
    "api_key_set": true,
    "api_key_preview": "12345678...abcd",
    "enabled": true
  }
}
```

**In Puppeteer output** (stderr):
```
🔐 CAPTCHA Solver Configuration:
   Provider: 2captcha
   API Key: 12345678...abcd
   Enabled: true
   Timeout: 120000ms
```

### Method 3: Check During Form Execution

When a CAPTCHA is detected and solved, you'll see detailed logs:

```
🔄 Submitting CAPTCHA to 2captcha API...
   Site Key: 6LeIxAcTAAAAAJcZVRqyHh71UM...
   Page URL: https://example.com/contact
   API URL: https://2captcha.com/in.php (key hidden)
✅ CAPTCHA submitted to 2captcha successfully
   Task ID: 12345678
   Status: OK
   Polling for solution (attempt 1)...
   Polling for solution (attempt 2)...
✅ CAPTCHA solved by 2captcha successfully!
   Time taken: 15 seconds
   Token preview: 03AGdBq24Pj...
```

## Configuration Check

### 1. Verify Environment Variables

Check your `.env` file:

```env
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key_here
CAPTCHA_SOLVER_PROVIDER=2captcha
CAPTCHA_SOLVER_TIMEOUT=120
```

### 2. Verify Config File

Check `config/form-monitor.php`:

```php
'captcha' => [
    'api_key' => env('CAPTCHA_SOLVER_API_KEY', ''),
    'provider' => env('CAPTCHA_SOLVER_PROVIDER', '2captcha'),
    'timeout' => env('CAPTCHA_SOLVER_TIMEOUT', 120),
],
```

### 3. Clear Config Cache (if needed)

If you changed `.env` values, clear the config cache:

```bash
php artisan config:clear
php artisan config:cache
```

## What to Look For

### ✅ Signs 2captcha is Working:

1. **Initialization Logs:**
   ```
   ✅ CAPTCHA Solver initialized: 2captcha (API Key: 12345678...abcd)
   ```

2. **When CAPTCHA is Detected:**
   ```
   CAPTCHA detected - attempting to solve...
   🔄 Submitting CAPTCHA to 2captcha API...
   ```

3. **Successful Solving:**
   ```
   ✅ CAPTCHA solved by 2captcha successfully!
   CAPTCHA solved successfully
   ```

4. **In CheckRun Results:**
   - `captchaSolved: true` in debug info
   - `captchaDetected: true`
   - Status is `success` (not `blocked`)

### ❌ Signs 2captcha is NOT Working:

1. **No API Key:**
   ```
   ⚠️ CAPTCHA Solver disabled: No API key configured
   CAPTCHA detected but solving is disabled (no API key configured)
   ```

2. **API Errors:**
   ```
   ❌ 2captcha API error: ERROR_WRONG_USER_KEY
   CAPTCHA solving failed: 2captcha submission failed
   ```

3. **In CheckRun Results:**
   - `captchaSolved: false`
   - `captchaBlocking: true`
   - Status is `blocked`

## Testing with a Real Form

1. **Create a form target** with `recaptcha_expected: true`
2. **Run the form check** via admin panel or API
3. **Check the logs** for CAPTCHA solver activity
4. **Review the CheckRun** debug info for CAPTCHA details

## Troubleshooting

### API Key Not Being Loaded

1. Check `.env` file has `CAPTCHA_SOLVER_API_KEY` set
2. Run `php artisan config:clear`
3. Verify config: `php artisan tinker` → `config('form-monitor.captcha.api_key')`

### Wrong Provider

If you see `anticaptcha` but want `2captcha`:
1. Set `CAPTCHA_SOLVER_PROVIDER=2captcha` in `.env`
2. Clear config cache

### API Errors

Common errors and solutions:

- **`ERROR_WRONG_USER_KEY`**: Invalid API key
- **`ERROR_ZERO_BALANCE`**: No credits in account
- **`ERROR_NO_SLOT_AVAILABLE`**: Service overloaded, retry later
- **`CAPCHA_NOT_READY`**: Normal, keep polling (handled automatically)

### Check Account Balance

Use the verification command:
```bash
php artisan captcha:verify
```

Or check manually:
```bash
curl "https://2captcha.com/res.php?key=YOUR_API_KEY&action=getbalance&json=1"
```

## Debug Mode

Enable Puppeteer debug mode to see more details:

```env
PUPPETEER_DEBUG=true
```

This will show:
- All CAPTCHA detection steps
- API request/response details
- Polling status updates
- Token injection process

## Summary

To verify 2captcha is being used:

1. ✅ Run `php artisan captcha:verify`
2. ✅ Check logs for "2captcha" mentions
3. ✅ Look for "CAPTCHA solved by 2captcha" messages
4. ✅ Verify `captchaSolved: true` in CheckRun results
5. ✅ Check your 2captcha account balance decreases after solving

If all checks pass, 2captcha is working correctly! 🎉

