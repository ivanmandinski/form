# Final Debug: 403 Access Denied

If you're still getting "Access denied. Admin role required" after fixing the database, let's debug step by step.

## Step 1: Verify Role Assignment in Database

Run this in psql to make absolutely sure:

```sql
-- Check everything
SELECT 
    u.id as user_id,
    u.email,
    r.id as role_id,
    r.name as role_name,
    r.guard_name,
    mhr.model_type,
    mhr.model_id
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

**Expected output:**
- `role_name` = `admin`
- `guard_name` = `web`
- `model_type` = `App\Models\User` (exactly this, with backslashes)

## Step 2: Check What User is Logged In

The issue might be that you're logged in as a different user. Check:

1. **Log out completely** from the application
2. **Clear browser cookies** for the site
3. **Log back in** with `admin@tessa.tech`

## Step 3: Verify Role Check Works

Since we can't easily run artisan, let's add a debug route temporarily to check if the role is detected.

Add this to `routes/web.php` (temporarily for debugging):

```php
Route::get('/debug-role', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    
    $user = auth()->user();
    return [
        'email' => $user->email,
        'id' => $user->id,
        'has_admin_role' => $user->hasRole('admin'),
        'roles' => $user->getRoleNames()->toArray(),
        'all_roles' => $user->roles->pluck('name')->toArray(),
    ];
})->middleware('auth');
```

Then visit: `https://web-production-85cc5.up.railway.app/debug-role`

This will show you if the role is being detected.

## Step 4: Check Session/Cache

The issue might be cached permissions. Since we can't run artisan easily, try:

1. **Log out completely**
2. **Clear browser cookies/cache**
3. **Wait a few minutes** (cache might expire)
4. **Log back in**

## Step 5: Manual Role Check in Database

Double-check the exact values in the database:

```sql
-- Check user
SELECT id, email FROM users WHERE email = 'admin@tessa.tech';

-- Check role
SELECT id, name, guard_name FROM roles WHERE name = 'admin';

-- Check assignment
SELECT * FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');
```

Make sure:
- `model_type` is exactly `App\Models\User` (with backslashes)
- The `role_id` matches the role `id`
- The `model_id` matches the user `id`

## Step 6: Reassign Role Completely

If nothing works, remove and recreate the assignment:

```sql
-- Remove all role assignments for this user
DELETE FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');

-- Recreate with exact values
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'admin' AND guard_name = 'web'),
    'App\\Models\\User',
    (SELECT id FROM users WHERE email = 'admin@tessa.tech');

-- Verify
SELECT 
    u.email,
    r.name,
    r.guard_name,
    mhr.model_type
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

## Step 7: Check if Using Redis Cache

If you're using Redis for cache, the permission cache might be in Redis. You'll need to either:

1. Use Railway's web terminal to run: `php artisan permission:cache-reset`
2. Or wait for cache to expire (usually 24 hours)
3. Or restart your Railway service (this might clear cache)

## Quick Test: Bypass Middleware Temporarily

To verify the route works, you can temporarily comment out the admin middleware in `routes/web.php`:

```php
// Temporarily remove 'admin' middleware to test
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('dashboard');
    // ... rest of routes
});
```

**Important:** Only do this for testing, then restore the middleware!

## Most Likely Issue

The most common issue is that **permission cache needs to be cleared**. Since you're using Redis (based on your config), you need to either:

1. Use Railway's web terminal to run: `php artisan permission:cache-reset`
2. Or restart your Railway service
3. Or wait for cache to expire

Try the debug route first (Step 3) to see what's actually happening!


