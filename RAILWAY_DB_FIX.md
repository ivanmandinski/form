# Quick Fix: Database Connection Error

## The Problem
Laravel is trying to connect to `127.0.0.1:5432` instead of your Railway database. This means `DATABASE_URL` is not being read correctly.

## Immediate Fix (3 Steps)

### Step 1: Check Your Railway Variables

1. Go to **Railway Dashboard** → Your **Web Service** → **Variables** tab
2. Look for `DATABASE_URL` - does it exist?
3. What is its value? It should be: `${{Postgres.DATABASE_URL}}` (or your service name)

### Step 2: Verify Your PostgreSQL Service Name

1. In Railway dashboard, find your **PostgreSQL service**
2. **Click on it** to see its details
3. **Note the exact name** (it might be "Postgres", "PostgreSQL", "postgres", etc.)
4. The name is **case-sensitive**!

### Step 3: Fix the Variable

**Option A: If DATABASE_URL doesn't exist or is wrong**

1. In your web service → Variables tab
2. **Delete** any existing `DATABASE_URL` variable
3. Click **"Add Variable"**
4. Variable name: `DATABASE_URL`
5. Variable value: `${{YourServiceName.DATABASE_URL}}` (replace with your actual service name)
6. **Save**

**Option B: If Railway doesn't provide DATABASE_URL**

Use individual variables instead:

1. Go to your **PostgreSQL service** → **Variables** tab
2. Note these variable names (they should be like `PGHOST`, `PGPORT`, etc.)
3. In your **web service** → **Variables** tab, add:

```env
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}
```

**Important:** Replace `Postgres` with your actual service name, and use `PGHOST`, `PGPORT`, etc. (NOT `DB_HOST`, `DB_PORT`).

## Remove Conflicting Variables

Make sure you **DELETE** these if they exist (they conflict):
- `DB_HOST`
- `DB_PORT`  
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

**Keep only:**
- `DB_CONNECTION=pgsql`
- `DATABASE_URL=${{YourServiceName.DATABASE_URL}}`

OR (if using individual variables):
- `DB_CONNECTION=pgsql`
- `DB_HOST=${{YourServiceName.PGHOST}}`
- `DB_PORT=${{YourServiceName.PGPORT}}`
- `DB_DATABASE=${{YourServiceName.PGDATABASE}}`
- `DB_USERNAME=${{YourServiceName.PGUSER}}`
- `DB_PASSWORD=${{YourServiceName.PGPASSWORD}}`

## After Fixing

1. **Redeploy** your service (Railway will auto-redeploy when you save variables)
2. **Check the logs** - the connection error should be gone
3. **Migrations should run successfully**

## Test Connection

After fixing, test the connection:

```bash
railway run php artisan tinker
```

Then in tinker:
```php
DB::connection()->getPdo();
```

If it works, you'll see the PDO object. If it fails, you'll see the error.

## Still Not Working?

1. **Check Railway logs** for more details
2. **Verify PostgreSQL service is running** (should show as "Active")
3. **Try the alternative method** (individual variables instead of DATABASE_URL)
4. **Check service name** - make sure it matches exactly (case-sensitive)

