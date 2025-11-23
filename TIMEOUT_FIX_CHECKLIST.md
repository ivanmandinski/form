# Timeout Fix Checklist

If you're still getting timeout errors, follow these steps:

## 1. Verify Code Changes Are Deployed

The timeout was changed from 300s to 360s default. Check if changes are deployed:

```bash
# Check current timeout value in code
grep -r "timeout.*360" app/Services/PuppeteerFormCheckService.php
grep -r "puppeteer.*360" config/form-monitor.php
```

## 2. Clear Config Cache

**CRITICAL**: Laravel caches config. Clear it:

```bash
php artisan config:clear
php artisan config:cache
```

Or if on production:
```bash
php artisan config:clear
# Don't cache in production if you're testing
```

## 3. Set Environment Variable Explicitly

Add to your `.env` file:

```env
FORM_MONITOR_PUPPETEER_TIMEOUT=420
```

This sets it to 7 minutes (420 seconds) to give plenty of buffer.

## 4. Check Current Timeout Values

Run this to see what timeout is actually being used:

```bash
php artisan tinker
```

Then:
```php
config('form-monitor.timeouts.puppeteer')
```

Should show `360` or your custom value.

## 5. Verify Process Timeout

The process timeout should be `timeout + 60` seconds. With default 360s:
- Script timeout: 300s (360 - 60)
- Process timeout: 420s (360 + 60)
- PHP timeout: 480s (360 + 120)

## 6. Check for Other Timeout Limits

### Server/Platform Limits

**Railway/Heroku/etc:**
- Check platform timeout limits
- May need to increase in platform settings

**PHP ini settings:**
```bash
php -i | grep max_execution_time
```

**Nginx/Apache:**
- Check `fastcgi_read_timeout` or `proxy_read_timeout`
- Should be at least 480 seconds

## 7. Test with Increased Timeout

Temporarily set a very high timeout to test:

```env
FORM_MONITOR_PUPPETEER_TIMEOUT=600
```

This gives 10 minutes. If it still times out, the issue is elsewhere.

## 8. Check Script Output

Look at the actual Puppeteer script output to see where it's hanging:

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log | grep -i "puppeteer\|captcha\|timeout"

# Check for stderr output (Puppeteer logs go to stderr)
# This should show where the script is spending time
```

## 9. Common Hanging Points

The script might hang at:

1. **Page navigation** - Network issues
2. **CAPTCHA solving** - API delays or infinite polling
3. **Form submission** - Slow server response
4. **Network idle wait** - Page keeps loading

Check logs for which step it's stuck on.

## 10. Quick Fix: Disable CAPTCHA Solving Temporarily

To test if CAPTCHA solving is the issue:

```env
CAPTCHA_SOLVER_ENABLED=false
```

Or remove the API key temporarily.

## 11. Verify Deployment

If using Git, ensure changes are committed and pushed:

```bash
git status
git log --oneline -5
```

## 12. Restart Services

After changing config:

```bash
# Restart queue workers
php artisan queue:restart

# Restart Horizon if using
php artisan horizon:terminate

# Restart web server/PHP-FPM
sudo systemctl restart php8.2-fpm  # or your PHP version
```

## Debug Mode

Enable debug logging:

```env
PUPPETEER_DEBUG=true
LOG_LEVEL=debug
```

This will show detailed timing information.

## Expected Behavior

With timeout = 360s:
- Script gets 300s (5 minutes) to run
- Process won't be killed until 420s (7 minutes)
- PHP won't timeout until 480s (8 minutes)

If script takes longer than 300s, it should exit gracefully with a timeout error, not be killed by PHP.

## Still Timing Out?

If still timing out after all checks:

1. **Check actual execution time** - Maybe the form really takes 5+ minutes
2. **Check for infinite loops** - Look for while loops without proper timeouts
3. **Check network conditions** - Slow connections cause delays
4. **Check CAPTCHA API** - 2captcha might be slow or down
5. **Increase timeout further** - Set to 600s (10 minutes) as test

## Contact Points

- Check Laravel logs: `storage/logs/laravel.log`
- Check Puppeteer stderr output (in process output)
- Check 2captcha account for API issues
- Check server resource limits (CPU, memory)

