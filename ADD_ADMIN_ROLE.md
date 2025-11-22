# How to Add Admin Role to User

After creating the admin user, you need to assign the admin role.

## Solution 1: Using Railway Web Terminal (Recommended)

1. Go to Railway Dashboard → Your Web Service → Latest deployment
2. Open the terminal/console
3. Run:
   ```bash
   php artisan tinker
   ```
4. Then:
   ```php
   use App\Models\User;
   use Spatie\Permission\Models\Role;
   
   // Create admin role if it doesn't exist
   $adminRole = Role::firstOrCreate(['name' => 'admin']);
   
   // Find the user
   $user = User::where('email', 'admin@formmonitor.com')->first();
   
   // Assign admin role
   $user->assignRole($adminRole);
   
   // Verify
   echo "User: " . $user->name . "\n";
   echo "Has admin role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . "\n";
   exit
   ```

## Solution 2: Direct SQL (If Tinker Doesn't Work)

If you can't use tinker, you can add the role directly via SQL:

### Step 1: Check if roles table exists and has admin role

```sql
-- Check if admin role exists
SELECT * FROM roles WHERE name = 'admin';
```

If it doesn't exist, create it:
```sql
-- Create admin role
INSERT INTO roles (name, guard_name, created_at, updated_at)
VALUES ('admin', 'web', NOW(), NOW());
```

### Step 2: Get the role ID

```sql
-- Get the role ID
SELECT id FROM roles WHERE name = 'admin';
```

Note the `id` value (e.g., `1`).

### Step 3: Get the user ID

```sql
-- Get the user ID
SELECT id FROM users WHERE email = 'admin@formmonitor.com';
```

Note the `id` value (e.g., `1`).

### Step 4: Assign the role

```sql
-- Assign role to user (replace 1 with actual role_id and user_id)
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (1, 'App\\Models\\User', 1)
ON CONFLICT DO NOTHING;
```

**Important:** Replace the `1` values with the actual IDs from steps 2 and 3.

## Solution 3: Run the Seeder (If Database Connection Works)

If you can get the database connection working, you can just run:

```bash
railway run php artisan db:seed --class=AdminUserSeeder
```

This will:
1. Create the admin role
2. Create/update the admin user
3. Assign the role

## Solution 4: Complete Setup in One Go

If you're setting up everything from scratch, here's a complete SQL script:

```sql
-- 1. Create admin role if it doesn't exist
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'admin', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'admin');

-- 2. Get the role ID
-- (Note this down, you'll need it)

-- 3. Get the user ID  
-- (Note this down, you'll need it)

-- 4. Assign role to user (replace ROLE_ID and USER_ID)
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (ROLE_ID, 'App\\Models\\User', USER_ID)
ON CONFLICT DO NOTHING;
```

## Verify the Role is Assigned

After assigning the role, verify it:

### Using Tinker:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
echo "Has admin role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . "\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
```

### Using SQL:
```sql
SELECT u.email, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@formmonitor.com';
```

## What the Admin Role Gives Access To

Once the admin role is assigned, the user will have access to:
- Admin dashboard at `/admin/dashboard`
- Target management
- Form configuration
- Check run monitoring
- All administrative features

## Troubleshooting

### "Role not found"
Make sure the role exists:
```sql
SELECT * FROM roles;
```

If it doesn't exist, create it first (see Solution 2, Step 1).

### "User not found"
Make sure the user exists:
```sql
SELECT * FROM users WHERE email = 'admin@formmonitor.com';
```

### "Duplicate key error"
The role is already assigned. You can ignore this or check:
```sql
SELECT * FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@formmonitor.com');
```


