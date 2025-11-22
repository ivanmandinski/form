# Fix: Could not open input file: artisan

This error means Railway can't find the `artisan` file. Here are solutions:

## Solution 1: Specify Full Path

Try specifying the full path to artisan:

```bash
railway run php /app/artisan permission:cache-reset
```

Or:

```bash
railway run sh -c "cd /app && php artisan permission:cache-reset"
```

## Solution 2: Check Railway Service Root

Railway might be running commands from a different directory. Check:

```bash
railway run pwd
```

This will show the current working directory.

## Solution 3: Use Railway Web Terminal

Instead of `railway run`, use Railway's web terminal:

1. Go to Railway Dashboard → Your Web Service
2. Click on "Deployments" → Latest deployment
3. Look for "Terminal" or "Console" option
4. Run commands there directly:
   ```bash
   php artisan permission:cache-reset
   ```

## Solution 4: Check Railway Configuration

Make sure your `railway.json` or service settings have the correct root directory set.

## Solution 5: Clear Cache via SQL (Alternative)

If you can't run artisan commands, you can manually clear the permission cache by:

1. **Clear the cache table** (if using database cache):
   ```sql
   DELETE FROM cache WHERE key LIKE '%spatie.permission%';
   ```

2. **Or truncate cache table** (if safe to do):
   ```sql
   TRUNCATE TABLE cache;
   ```

## Solution 6: Reassign Role and Clear Cache

Since you can't run artisan easily, let's make sure the role is correctly assigned:

### In psql (`railway connect postgres`):

```sql
-- 1. Ensure role has correct guard
UPDATE roles SET guard_name = 'web' WHERE name = 'admin';

-- 2. Ensure model_type is correct
UPDATE model_has_roles
SET model_type = 'App\\Models\\User'
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');

-- 3. Clear permission cache (if using database cache)
DELETE FROM cache WHERE key LIKE '%spatie.permission%';

-- 4. Verify assignment
SELECT 
    u.email,
    r.name as role_name,
    r.guard_name,
    mhr.model_type
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

## Solution 7: Check What Cache Driver You're Using

Check your cache configuration. If you're using Redis or file cache, the SQL method won't work.

In Railway variables, check:
- `CACHE_DRIVER` or `CACHE_STORE`

If it's `redis`, you'll need to clear Redis cache or use Railway's web terminal.

## Recommended: Use Railway Web Terminal

The easiest solution is to use Railway's web terminal if available:

1. Railway Dashboard → Your Web Service
2. Look for terminal/console option
3. Run: `php artisan permission:cache-reset`

This avoids path issues entirely.


