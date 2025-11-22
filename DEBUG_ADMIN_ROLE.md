# Debug: 403 Access Denied - Check Admin Role

If you're getting "403 Access denied. Admin role required", the role check is failing. Let's debug this step by step.

## Step 1: Verify Role is Assigned in Database

Run these SQL queries in psql (`railway connect postgres`):

```sql
-- Check if user exists
SELECT id, email, name FROM users WHERE email = 'admin@tessa.tech';

-- Check if role exists
SELECT id, name FROM roles WHERE name = 'admin';

-- Check if role is assigned to user
SELECT 
    u.id as user_id,
    u.email,
    u.name as user_name,
    r.id as role_id,
    r.name as role_name,
    mhr.model_type
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

**Expected:** Should return 1 row showing the user with admin role.

## Step 2: Check Model Type

The `model_type` should be exactly `App\Models\User` (with backslashes). Check:

```sql
SELECT model_type, COUNT(*) 
FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech')
GROUP BY model_type;
```

If it's wrong, fix it:

```sql
UPDATE model_has_roles
SET model_type = 'App\\Models\\User'
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech')
  AND model_type != 'App\\Models\\User';
```

## Step 3: Clear Laravel Permission Cache

Laravel caches permissions. Clear the cache:

```bash
railway run php artisan permission:cache-reset
```

Or:

```bash
railway run php artisan cache:clear
railway run php artisan config:clear
```

## Step 4: Test Role Check in Tinker

Test if the role check works:

```bash
railway run php artisan tinker
```

Then:

```php
$user = App\Models\User::where('email', 'admin@tessa.tech')->first();
echo "User: " . $user->email . "\n";
echo "Has admin role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . "\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
exit
```

**Expected:** Should show "Has admin role: Yes" and "Roles: admin"

## Step 5: Check Guard Name

Make sure the role has the correct guard name:

```sql
SELECT id, name, guard_name FROM roles WHERE name = 'admin';
```

The `guard_name` should be `web`. If it's different, update it:

```sql
UPDATE roles SET guard_name = 'web' WHERE name = 'admin';
```

## Step 6: Reassign Role (If Needed)

If nothing works, remove and reassign the role:

```sql
-- Remove existing assignment
DELETE FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech')
  AND role_id = (SELECT id FROM roles WHERE name = 'admin');

-- Reassign
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'admin' 
  AND u.email = 'admin@tessa.tech';
```

Then clear cache again:

```bash
railway run php artisan permission:cache-reset
```

## Step 7: Verify Current User in Session

Check what user is actually logged in:

```bash
railway run php artisan tinker
```

```php
// Check if you're logged in
echo "Logged in: " . (auth()->check() ? 'Yes' : 'No') . "\n";
if (auth()->check()) {
    $user = auth()->user();
    echo "User: " . $user->email . "\n";
    echo "Has admin role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . "\n";
}
exit
```

## Common Issues

### Issue 1: Permission Cache Not Cleared
**Solution:** Run `php artisan permission:cache-reset`

### Issue 2: Wrong Model Type
**Solution:** Make sure `model_type` is exactly `App\Models\User` (with escaped backslashes)

### Issue 3: Wrong Guard Name
**Solution:** Make sure role has `guard_name = 'web'`

### Issue 4: User Not Actually Logged In
**Solution:** Log out and log back in

### Issue 5: Session Issue
**Solution:** Clear browser cookies/session and log in again

## Quick Fix Script

Run this complete SQL script to fix everything:

```sql
-- 1. Ensure role exists with correct guard
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'admin', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'admin' AND guard_name = 'web');

-- 2. Fix guard if wrong
UPDATE roles SET guard_name = 'web' WHERE name = 'admin';

-- 3. Remove existing assignment
DELETE FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech')
  AND role_id = (SELECT id FROM roles WHERE name = 'admin');

-- 4. Reassign with correct model_type
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'admin' 
  AND u.email = 'admin@tessa.tech'
  AND r.guard_name = 'web';

-- 5. Verify
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

Then clear cache:

```bash
railway run php artisan permission:cache-reset
```


