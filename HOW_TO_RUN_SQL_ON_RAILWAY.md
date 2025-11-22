# How to Run SQL Commands on Railway PostgreSQL

There are several ways to run SQL commands on your Railway PostgreSQL database.

## Method 1: Railway Dashboard - Database Query Interface (Easiest)

1. **Go to Railway Dashboard** → Your Project
2. **Click on your PostgreSQL service**
3. Look for one of these options:
   - **"Query"** tab/button
   - **"Data"** tab
   - **"Connect"** → Then look for a query interface
   - **"Postgres"** → Query interface

4. **Run your SQL commands** in the query editor

## Method 2: Railway CLI - Connect to Database

If Railway provides a connection command:

```bash
railway connect postgres
```

This might open a PostgreSQL client or provide connection details.

## Method 3: Use psql Command Line Tool

### Step 1: Get Connection String

1. Go to Railway Dashboard → PostgreSQL service → Variables tab
2. Look for connection details or click "Connect"
3. Railway might show you a connection string like:
   ```
   postgresql://postgres:PASSWORD@HOST:PORT/railway
   ```

### Step 2: Connect with psql

If you have `psql` installed locally:

```bash
psql "postgresql://postgres:PASSWORD@HOST:PORT/railway"
```

Or Railway might provide a direct connection command.

## Method 4: Railway CLI - Run psql

Try this command:

```bash
railway run psql
```

Or:

```bash
railway connect postgres -- psql
```

## Method 5: Use Railway's Database Viewer

1. Go to Railway Dashboard → PostgreSQL service
2. Look for **"Data"** or **"Tables"** tab
3. Some Railway plans include a database viewer where you can:
   - Browse tables
   - Run queries
   - Edit data

## Method 6: External Database Tool

If Railway provides connection details, you can use external tools:

### Using pgAdmin or DBeaver:

1. Get connection details from Railway:
   - Host
   - Port
   - Database name
   - Username
   - Password

2. Connect using your preferred database tool

### Using TablePlus (macOS):

1. Get connection string from Railway
2. TablePlus can import connection strings directly

## Method 7: Railway Shell + psql

If Railway has a shell feature:

```bash
railway shell
```

Then inside the shell:
```bash
psql $DATABASE_URL
```

## Quick SQL Commands to Add Admin Role

Once you have access to run SQL, use these commands:

### Create Admin Role:
```sql
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'admin', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'admin');
```

### Assign Role to User:
```sql
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r
CROSS JOIN users u
WHERE r.name = 'admin' 
  AND u.email = 'admin@formmonitor.com'
ON CONFLICT DO NOTHING;
```

### Verify:
```sql
SELECT u.email, u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@formmonitor.com';
```

## Finding Connection Details

If you need connection details:

1. **Railway Dashboard** → PostgreSQL service → **Variables** tab
2. Look for:
   - `PGHOST` or `POSTGRES_HOST`
   - `PGPORT` or `POSTGRES_PORT`
   - `PGDATABASE` or `POSTGRES_DB`
   - `PGUSER` or `POSTGRES_USER`
   - `PGPASSWORD` or `POSTGRES_PASSWORD`
   - Or `DATABASE_URL` (full connection string)

## Troubleshooting

### "Command not found: psql"
Install PostgreSQL client tools:
- **macOS**: `brew install postgresql`
- **Ubuntu/Debian**: `sudo apt-get install postgresql-client`
- **Windows**: Download from postgresql.org

### "Connection refused"
- Make sure your PostgreSQL service is running
- Check that you're using the correct host/port
- Railway might require VPN or specific network access

### "Authentication failed"
- Verify username and password from Railway variables
- Make sure you're using the correct database name

## Recommended Approach

**Start with Method 1** (Railway Dashboard Query Interface) - it's the easiest and doesn't require any local tools.

If that's not available, try **Method 4** (`railway run psql`) or check Railway's documentation for their specific database access method.


