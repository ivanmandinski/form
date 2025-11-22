# Debug: Role Assignment Issue

The INSERT returned `INSERT 0 0` which means no rows were inserted. This usually means:
1. The user doesn't exist with that email
2. The CROSS JOIN didn't find matching records

## Step 1: Check if User Exists

Run this in psql:

```sql
SELECT id, email, name FROM users WHERE email = 'admin@formmonitor.com';
```

If this returns 0 rows, the user doesn't exist. You'll need to create it first.

## Step 2: Check if Role Exists

```sql
SELECT id, name FROM roles WHERE name = 'admin';
```

This should return the role (you just created it).

## Step 3: Manual Assignment

Once you have both IDs, assign manually:

```sql
-- Replace ROLE_ID and USER_ID with actual IDs from steps 1 and 2
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (ROLE_ID, 'App\\Models\\User', USER_ID)
ON CONFLICT DO NOTHING;
```

## Alternative: Complete Script

Run this complete script to check and assign:

```sql
-- Check user
SELECT id, email, name FROM users WHERE email = 'admin@formmonitor.com';

-- Check role
SELECT id, name FROM roles WHERE name = 'admin';

-- If both exist, assign (replace IDs)
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (1, 'App\\Models\\User', 1)
ON CONFLICT DO NOTHING;

-- Verify
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@formmonitor.com';
```


