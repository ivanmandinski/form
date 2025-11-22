# How to Run SQL Commands on Railway PostgreSQL

## Step 1: Connect to Railway PostgreSQL

Open your terminal and run:

```bash
railway connect postgres
```

This will open a PostgreSQL shell (`psql`) connected to your Railway database.

## Step 2: Run the SQL Commands

Once you're in the `psql` prompt (you'll see `railway=#`), you can run the SQL commands. You can either:

### Option A: Copy and Paste Each Command

Copy and paste each command one at a time:

```sql
UPDATE roles SET guard_name = 'web' WHERE name = 'admin';
```

Press Enter. You should see: `UPDATE 1` (or `UPDATE 0` if already set)

Then run:

```sql
UPDATE model_has_roles
SET model_type = 'App\\Models\\User'
WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');
```

Press Enter. You should see: `UPDATE 1` (or `UPDATE 0` if already correct)

Then run:

```sql
DELETE FROM cache WHERE key LIKE '%spatie.permission%';
```

Press Enter. You might see: `DELETE 0` or `DELETE X` (number of cache entries deleted)

Finally, verify:

```sql
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

This should show your user with the admin role.

### Option B: Run All at Once

You can also run them all in sequence. Just paste them one after another, pressing Enter after each.

## Step 3: Exit psql

When you're done, type:

```sql
\q
```

And press Enter to exit.

## Complete Example Session

Here's what your terminal session should look like:

```bash
$ railway connect postgres
railway=# UPDATE roles SET guard_name = 'web' WHERE name = 'admin';
UPDATE 1
railway=# UPDATE model_has_roles SET model_type = 'App\\Models\\User' WHERE model_id = (SELECT id FROM users WHERE email = 'admin@tessa.tech');
UPDATE 1
railway=# DELETE FROM cache WHERE key LIKE '%spatie.permission%';
DELETE 2
railway=# SELECT u.email, r.name as role_name, r.guard_name, mhr.model_type FROM users u JOIN model_has_roles mhr ON u.id = mhr.model_id JOIN roles r ON mhr.role_id = r.id WHERE u.email = 'admin@tessa.tech';
      email           | role_name | guard_name |    model_type    
----------------------+-----------+------------+------------------
 admin@tessa.tech     | admin     | web        | App\Models\User
(1 row)
railway=# \q
$
```

## Troubleshooting

### "relation does not exist: cache"
If you get this error, you're probably using Redis for cache, not database cache. That's fine - skip the DELETE command. The permission cache will be cleared when you log out and log back in.

### "UPDATE 0"
This means the value was already correct. That's fine!

### "No rows returned" from SELECT
This means the role isn't assigned. Make sure you ran the UPDATE commands first.

## After Running SQL

1. **Log out** of your application
2. **Log back in** with `admin@tessa.tech`
3. **Try accessing** `/admin/dashboard`

The role should now work!


