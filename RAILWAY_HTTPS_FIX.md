# Fix Mixed Content (HTTPS) Error

The error "Mixed Content" means your page is loaded over HTTPS, but assets (CSS/JS) are being requested over HTTP.

## Quick Fix

### Step 1: Update APP_URL in Railway

1. Go to Railway Dashboard → Your Web Service → Variables tab
2. Find `APP_URL` variable
3. **Make sure it starts with `https://`** (not `http://`)
4. Update it to: `https://web-production-85cc5.up.railway.app`
5. Save

### Step 2: Clear Laravel Cache

After updating `APP_URL`, clear the config cache:

```bash
railway run php artisan config:clear
railway run php artisan config:cache
```

Or Railway will auto-redeploy and clear cache automatically.

## Why This Happens

- Railway serves your app over HTTPS
- But if `APP_URL` is set to `http://`, Laravel generates asset URLs with HTTP
- Browsers block mixed content (HTTPS page loading HTTP resources)

## Verify It's Fixed

1. Check your Railway variables - `APP_URL` should be `https://...`
2. Clear browser cache or do a hard refresh (Ctrl+Shift+R / Cmd+Shift+R)
3. Check browser console - mixed content errors should be gone

## Additional Fix: Force HTTPS in Laravel

If you want to force HTTPS for all URLs, add this to your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

But updating `APP_URL` to HTTPS should be enough.

