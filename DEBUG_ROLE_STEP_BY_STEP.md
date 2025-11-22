# Step-by-Step Debug: Assign Admin Role

Let's debug this systematically. Run each command and share the results.

## Step 1: Check if User Exists

```sql
SELECT id, email, name FROM users WHERE email = 'admin@tessa.tech';
```

**Expected:** Should return 1 row with the user details.

**If 0 rows:** The user doesn't exist. We need to create it first.

## Step 2: Check if Role Exists

```sql
SELECT id, name FROM roles WHERE name = 'admin';
```

**Expected:** Should return 1 row with id and name.

**If 0 rows:** The role doesn't exist (but you created it, so this should work).

## Step 3: Check Table Structure

Let's verify the `model_has_roles` table structure:

```sql
\d model_has_roles
```

This will show the table structure and column names.

## Step 4: Check What's in model_has_roles

```sql
SELECT * FROM model_has_roles LIMIT 5;
```

This shows if there are any existing role assignments.

## Step 5: Try Manual Insert with Explicit IDs

First, get the actual IDs:

```sql
-- Get user ID
SELECT id FROM users WHERE email = 'admin@tessa.tech';

-- Get role ID
SELECT id FROM roles WHERE name = 'admin';
```

Then use those exact IDs (replace USER_ID and ROLE_ID):

```sql
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (ROLE_ID, 'App\\Models\\User', USER_ID);
```

**Note:** Remove `ON CONFLICT DO NOTHING` to see if there's an error.

## Step 6: Check for Conflicts

If you get a conflict error, check what's already there:

```sql
SELECT * FROM model_has_roles 
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');
```

## Alternative: Check All Users and Roles

```sql
-- List all users
SELECT id, email, name FROM users;

-- List all roles
SELECT id, name FROM roles;

-- List all role assignments
SELECT mhr.*, u.email, r.name as role_name
FROM model_has_roles mhr
LEFT JOIN users u ON mhr.model_id = u.id AND mhr.model_type = 'App\\Models\\User'
LEFT JOIN roles r ON mhr.role_id = r.id;
```

## Common Issues

1. **User doesn't exist** - Need to create it first
2. **Role doesn't exist** - Need to create it (but you already did)
3. **Wrong model_type** - Should be exactly `App\Models\User` (with backslashes escaped)
4. **Primary key conflict** - Role already assigned
5. **Foreign key constraint** - IDs don't match

Run Step 1 and Step 2 first and share the results!


