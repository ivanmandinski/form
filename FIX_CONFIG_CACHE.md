# Fix Config Cache Without artisan

If `php artisan config:clear` gives a parse error, here are alternative methods:

## Method 1: Delete Cache Files Manually

```bash
# Delete config cache file
rm -f bootstrap/cache/config.php

# Delete all cache files
rm -rf bootstrap/cache/*.php

# Make sure cache directory exists
mkdir -p bootstrap/cache
chmod 755 bootstrap/cache
```

## Method 2: Reinstall Dependencies (if vendor is corrupted)

```bash
# Remove vendor directory
rm -rf vendor/

# Reinstall dependencies
composer install --no-dev --optimize-autoloader

# Then try config:clear again
php artisan config:clear
```

## Method 3: Use PHP Directly

Instead of `php artisan config:clear`, you can manually clear config:

```bash
# Create a simple PHP script
cat > clear_config.php << 'EOF'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
Artisan::call('config:clear');
echo "Config cache cleared!\n";
EOF

php clear_config.php
rm clear_config.php
```

## Method 4: Set Environment Variable Directly

Instead of clearing cache, just set the timeout in `.env`:

```env
FORM_MONITOR_PUPPETEER_TIMEOUT=420
```

Then restart your PHP/web server. The new value will be picked up on next request.

## Method 5: Check PHP Version Compatibility

Your PHP version (8.4.1) should work, but verify:

```bash
php -v
# Should show PHP 8.2+ 

# Check if composer dependencies are compatible
composer check-platform-reqs
```

## Method 6: Fix Corrupted Vendor File

If the ParseErrorException.php file is corrupted:

```bash
# Remove just that package
rm -rf vendor/psy/

# Reinstall it
composer require psy/psysh --no-update
composer update psy/psysh
```

## Method 7: Quick Fix - Just Delete Config Cache

The simplest solution:

```bash
# On your server, run:
rm -f bootstrap/cache/config.php

# Or delete all cache:
rm -rf bootstrap/cache/*.php

# Restart PHP/web server
sudo systemctl restart php8.2-fpm  # or your PHP version
# OR
sudo service nginx reload
```

## Method 8: Set Timeout in Config File Directly

Edit `config/form-monitor.php` directly:

```php
'puppeteer' => env('FORM_MONITOR_PUPPETEER_TIMEOUT', 420), // Changed from 360 to 420
```

Then delete the cache:
```bash
rm -f bootstrap/cache/config.php
```

## Recommended Solution

**For immediate fix:**

1. Delete config cache manually:
   ```bash
   rm -f bootstrap/cache/config.php
   ```

2. Set timeout in `.env`:
   ```env
   FORM_MONITOR_PUPPETEER_TIMEOUT=420
   ```

3. Restart web server/PHP-FPM

4. Test - the new timeout should be active

**For long-term fix:**

1. Fix vendor files:
   ```bash
   composer install --no-dev
   ```

2. Then use artisan normally:
   ```bash
   php artisan config:clear
   ```

## Verify It Worked

After clearing cache, check:

```bash
# Check if config cache exists (should be gone)
ls -la bootstrap/cache/config.php

# Check config value (if you can run tinker)
php artisan tinker
# Then: config('form-monitor.timeouts.puppeteer')
```

