# Railway Database Connection Troubleshooting

If you're getting connection errors like "connection to server at 127.0.0.1, port 5432 failed", it means Laravel is not reading the `DATABASE_URL` correctly.

## Quick Fix Steps

### Step 1: Verify DATABASE_URL is Set in Railway

1. Go to Railway Dashboard → Your Web Service → Variables tab
2. Look for `DATABASE_URL` variable
3. Make sure it's set to: `${{Postgres.DATABASE_URL}}` (replace `Postgres` with your service name)

### Step 2: Check Your Database Service Name

1. In Railway dashboard, find your PostgreSQL service
2. Click on it to see its name (might be "Postgres", "PostgreSQL", "postgres", etc.)
3. The name is case-sensitive!

### Step 3: Verify Variable Reference Syntax

The syntax must be exactly:
```
DATABASE_URL=${{YourServiceName.DATABASE_URL}}
```

**Common mistakes:**
- ❌ `DATABASE_URL=${{Postgres.DATABASE_URL}}` (if service is named "PostgreSQL")
- ❌ `DATABASE_URL={{Postgres.DATABASE_URL}}` (missing `$`)
- ❌ `DATABASE_URL=${{postgres.DATABASE_URL}}` (wrong case)
- ✅ `DATABASE_URL=${{Postgres.DATABASE_URL}}` (correct, if service is "Postgres")

### Step 4: Remove Conflicting Variables

Make sure you **DON'T** have these variables set (they conflict with DATABASE_URL):
- ❌ `DB_HOST`
- ❌ `DB_PORT`
- ❌ `DB_DATABASE`
- ❌ `DB_USERNAME`
- ❌ `DB_PASSWORD`

If you have them, **delete them** and only keep:
- ✅ `DB_CONNECTION=pgsql`
- ✅ `DATABASE_URL=${{Postgres.DATABASE_URL}}`

### Step 5: Check Railway Variable Reference

1. In Railway, click on your PostgreSQL service
2. Go to "Variables" or "Connect" tab
3. Look for `DATABASE_URL` - it should exist
4. If it doesn't exist, Railway might use different variable names

### Step 6: Alternative - Use Individual Variables

If `DATABASE_URL` doesn't work, use individual variables with Railway's PostgreSQL variable names:

```env
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}
```

**Important:** Railway uses `PGHOST`, `PGPORT`, etc., NOT `DB_HOST`, `DB_PORT`.

### Step 7: Verify Database Service is Running

1. In Railway dashboard, check your PostgreSQL service
2. Make sure it shows as "Active" or "Running"
3. If it's stopped, start it

### Step 8: Test Connection Manually

You can test the connection using Railway CLI:

```bash
railway run php artisan tinker
```

Then in tinker:
```php
DB::connection()->getPdo();
```

Or check the connection:
```php
DB::connection()->getDatabaseName();
```

## Debugging: Check What Laravel Sees

Add this temporarily to your start command to see what variables Laravel is reading:

```bash
railway run env | grep -E "(DATABASE|DB_)"
```

Or check in Railway logs what environment variables are being used.

## Common Issues and Solutions

### Issue 1: "Connection to 127.0.0.1:5432 failed"

**Cause:** Laravel is using default values, meaning `DATABASE_URL` is not set or not being read.

**Solution:**
1. Verify `DATABASE_URL` exists in Railway variables
2. Check service name is correct
3. Remove any conflicting `DB_HOST`, `DB_PORT`, etc. variables
4. Redeploy the service

### Issue 2: "Invalid integer value for port"

**Cause:** Individual DB variables are being parsed incorrectly.

**Solution:** Use `DATABASE_URL` instead of individual variables.

### Issue 3: "Service name not found"

**Cause:** The service name in `${{ServiceName.VAR}}` doesn't match your actual service name.

**Solution:** Check the exact service name in Railway dashboard (case-sensitive).

### Issue 4: DATABASE_URL is empty

**Cause:** Railway might not provide `DATABASE_URL` for your PostgreSQL service.

**Solution:** Use individual variables with Railway's PostgreSQL variable names (`PGHOST`, `PGPORT`, etc.).

## Step-by-Step: Setting Up Database Variables

### Method 1: Using Railway's Reference Feature (Easiest)

1. Go to your web service → Variables tab
2. Click "Add Variable"
3. Type: `DATABASE_URL`
4. Click the "Reference" button (or link icon)
5. Select your PostgreSQL service
6. Select `DATABASE_URL` from the list
7. Railway auto-creates: `DATABASE_URL=${{YourServiceName.DATABASE_URL}}`

### Method 2: Manual Entry

1. Go to your web service → Variables tab
2. Click "Add Variable"
3. Variable name: `DATABASE_URL`
4. Variable value: `${{Postgres.DATABASE_URL}}` (replace `Postgres` with your service name)
5. Save

### Method 3: Using Individual Variables

1. Go to your PostgreSQL service → Variables tab
2. Note the variable names (likely `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`)
3. In your web service → Variables tab, add:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   ```

## After Fixing

1. **Redeploy your service** (or wait for auto-redeploy)
2. **Check logs** to verify connection succeeds
3. **Run migrations:**
   ```bash
   railway run php artisan migrate --force
   ```

## Still Not Working?

1. Check Railway logs for more details
2. Verify PostgreSQL service is running and healthy
3. Try connecting directly using Railway's connection string
4. Check if there are any network/firewall restrictions

