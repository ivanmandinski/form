# Assign Admin Role to admin@tessa.tech

Use these SQL commands with the correct email address.

## Quick Solution - Run in psql:

```sql
-- Assign role to user with correct email
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'admin' 
  AND u.email = 'admin@tessa.tech'
ON CONFLICT DO NOTHING;

-- Verify it worked
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

## If That Doesn't Work - Manual Assignment:

### Step 1: Get the IDs

```sql
-- Get user ID
SELECT id, email, name FROM users WHERE email = 'admin@tessa.tech';

-- Get role ID
SELECT id, name FROM roles WHERE name = 'admin';
```

### Step 2: Assign using actual IDs

Replace `USER_ID` and `ROLE_ID` with the actual IDs from Step 1:

```sql
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (ROLE_ID, 'App\\Models\\User', USER_ID)
ON CONFLICT DO NOTHING;
```

### Step 3: Verify

```sql
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

## Complete Check Script

Run this to check everything:

```sql
-- Check if user exists
SELECT id, email, name FROM users WHERE email = 'admin@tessa.tech';

-- Check if role exists
SELECT id, name FROM roles WHERE name = 'admin';

-- Assign role
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'admin' 
  AND u.email = 'admin@tessa.tech'
ON CONFLICT DO NOTHING;

-- Verify assignment
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```


