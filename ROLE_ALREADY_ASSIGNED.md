# Role Already Assigned!

The error "duplicate key value violates unique constraint" means the admin role is **already assigned** to the user. This is actually good news!

## Verify the Role is Assigned

Run this to confirm:

```sql
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@tessa.tech';
```

You should see:
```
      email           |    name     | role_name 
----------------------+-------------+-----------
 admin@tessa.tech     | Admin User  | admin
```

## Check All Role Assignments

To see all role assignments:

```sql
SELECT 
    u.email,
    u.name as user_name,
    r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id;
```

## You're All Set!

The admin role is already assigned. You can now:

1. **Log in** at: `https://web-production-85cc5.up.railway.app/login`
2. **Email**: `admin@tessa.tech`
3. **Password**: (whatever password you set)

After logging in, you should be redirected to the admin dashboard at `/admin/dashboard`.

## If Login Doesn't Work

If you can't log in, the issue might be:
1. **Wrong password** - You may need to reset it
2. **Password not hashed** - Make sure the password in the database is bcrypt hashed

To check if password is hashed correctly:
```sql
SELECT email, LEFT(password, 10) as password_start FROM users WHERE email = 'admin@tessa.tech';
```

The password should start with `$2y$12$` if it's properly hashed.

## Reset Password (If Needed)

If you need to reset the password, use this hash for password "password":

```sql
UPDATE users 
SET password = '$2y$12$dGXaPge03zrCqjo88b9zpu7WNTj71.km0hWDdtPvfspm8FUd0789C'
WHERE email = 'admin@tessa.tech';
```

This sets the password to: `password`


