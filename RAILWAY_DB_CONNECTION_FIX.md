# Fix Database Connection Error on Railway

The error "could not translate host name 'postgres.railway.internal'" means the database connection variables aren't resolving correctly.

## Quick Fix

### Step 1: Verify Database Variables in Railway

1. Go to Railway Dashboard → Your Web Service → Variables tab
2. Check that you have:
   ```env
   DB_CONNECTION=pgsql
   DATABASE_URL=${{Postgres.DATABASE_URL}}
   ```
   
   **Important:** Replace `Postgres` with your actual PostgreSQL service name.

### Step 2: Check Your PostgreSQL Service Name

1. In Railway Dashboard, find your PostgreSQL service
2. Note its exact name (case-sensitive)
3. Make sure `DATABASE_URL` uses that exact name

### Step 3: Use Railway's Reference Feature

Instead of typing manually, use Railway's reference:

1. In your web service → Variables tab
2. Delete the existing `DATABASE_URL` if it exists
3. Click "Add Variable"
4. Variable name: `DATABASE_URL`
5. Click the "Reference" button (link icon)
6. Select your PostgreSQL service
7. Select `DATABASE_URL`
8. Railway will create the correct reference

### Step 4: Verify Service is Running

1. Check that your PostgreSQL service shows as "Active" in Railway
2. If it's stopped, start it

### Step 5: Run the Seeder Again

After fixing the variables:

```bash
railway run php artisan db:seed --class=AdminUserSeeder
```

## Alternative: Check What Railway Provides

If `DATABASE_URL` doesn't work, check what variables Railway actually provides:

1. Go to PostgreSQL service → Variables tab
2. Look for available variables
3. They might be named differently (e.g., `PGHOST`, `PGPORT`, etc.)

## Debug: Check Environment Variables

You can check what variables are actually set:

```bash
railway run env | grep -E "(DATABASE|DB_)"
```

This will show you what environment variables Railway is providing.

## Common Issues

### Issue 1: Service Name Mismatch
- The service name in `${{ServiceName.DATABASE_URL}}` doesn't match your actual service name
- **Fix:** Use Railway's Reference feature to ensure correct name

### Issue 2: DATABASE_URL Not Resolving
- Railway isn't resolving the variable reference
- **Fix:** Make sure you're using the Reference feature, not typing manually

### Issue 3: Database Service Not Running
- PostgreSQL service is stopped
- **Fix:** Start the service in Railway dashboard

### Issue 4: Wrong Environment
- Running command in wrong environment
- **Fix:** Make sure you're linked to the correct project: `railway link`

## Still Not Working?

If it still doesn't work, try using individual variables instead:

1. In PostgreSQL service → Variables tab, note these:
   - `PGHOST`
   - `PGPORT`
   - `PGDATABASE`
   - `PGUSER`
   - `PGPASSWORD`

2. In your web service → Variables tab, add:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   ```

3. Replace `Postgres` with your actual service name


