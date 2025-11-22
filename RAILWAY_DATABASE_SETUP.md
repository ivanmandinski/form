# How to Add Database Service in Railway

This guide shows you how to add a PostgreSQL or MySQL database to your Railway project.

## Step-by-Step Instructions

### Step 1: Open Your Railway Project

1. Go to [railway.app](https://railway.app) and sign in
2. Click on your project (the one with your Laravel app)

### Step 2: Add Database Service

1. **Click the "+ New" button** in your project dashboard
   - It's usually in the top right or bottom of the services list

2. **Select "Database"** from the dropdown menu
   - You'll see options like: PostgreSQL, MySQL, MongoDB, Redis, etc.

3. **Choose PostgreSQL** (recommended) or **MySQL**
   - PostgreSQL is recommended for Laravel applications
   - Click on the database type you want

### Step 3: Railway Automatically Creates the Database

Railway will:
- Create a new database service
- Generate database credentials automatically
- Provide connection variables

### Step 4: Link Database to Your Web Service

1. **Click on your web service** (the Laravel app service)

2. **Go to the "Variables" tab**

3. **Add these environment variables:**

   For **PostgreSQL** (Option 1 - Use DATABASE_URL - Recommended):
   ```env
   DB_CONNECTION=pgsql
   DATABASE_URL=${{Postgres.DATABASE_URL}}
   ```
   
   OR (Option 2 - Use individual variables):
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   ```
   
   **Note:** Railway provides `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD` for PostgreSQL, not `DB_HOST`, `DB_PORT`, etc.

   For **MySQL**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.DB_HOST}}
   DB_PORT=${{MySQL.DB_PORT}}
   DB_DATABASE=${{MySQL.DB_DATABASE}}
   DB_USERNAME=${{MySQL.DB_USER}}
   DB_PASSWORD=${{MySQL.DB_PASSWORD}}
   ```

   **Important:** Replace `Postgres` or `MySQL` with the **actual name** of your database service in Railway.

### Step 5: Find Your Database Service Name

1. Look at your Railway project dashboard
2. Find the database service you just created
3. Click on it to see its name (it might be called "Postgres", "PostgreSQL", "MySQL", or something else)
4. Use that exact name in the `${{ServiceName.VAR}}` syntax

### Step 6: Verify Variables Are Set

1. In your web service → Variables tab
2. You should see all the `DB_*` variables listed
3. They should show values like `${{Postgres.DB_HOST}}` (not actual values - Railway resolves these automatically)

## Visual Guide

```
Railway Project Dashboard
├── Your Web Service (Laravel App)
│   └── Variables Tab
│       ├── DB_CONNECTION=pgsql
│       ├── DB_HOST=${{Postgres.DB_HOST}}
│       ├── DB_PORT=${{Postgres.DB_PORT}}
│       ├── DB_DATABASE=${{Postgres.DB_DATABASE}}
│       ├── DB_USERNAME=${{Postgres.DB_USER}}
│       └── DB_PASSWORD=${{Postgres.DB_PASSWORD}}
│
└── Postgres Service (Database)
    └── Automatically provides:
        ├── DB_HOST
        ├── DB_PORT
        ├── DB_DATABASE
        ├── DB_USER
        └── DB_PASSWORD
```

## Quick Method: Use Railway's Variable Reference

Railway makes this easy:

1. **In your web service Variables tab**
2. **Click "Add Variable"**
3. **Type:** `DB_HOST`
4. **Click the "Reference" button** (or look for a link icon)
5. **Select your database service** from the list
6. **Select `DB_HOST`** from the database variables
7. Railway will automatically create: `DB_HOST=${{YourDatabaseService.DB_HOST}}`

Repeat for:
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME` (might be called `DB_USER` in Railway)
- `DB_PASSWORD`

## Alternative: Copy from Database Service

1. **Click on your database service** in Railway
2. **Go to the "Variables" tab**
3. **You'll see variables like:**
   - `PGHOST` (or `MYSQL_HOST`)
   - `PGPORT` (or `MYSQL_PORT`)
   - `PGDATABASE` (or `MYSQL_DATABASE`)
   - `PGUSER` (or `MYSQL_USER`)
   - `PGPASSWORD` (or `MYSQL_PASSWORD`)

4. **In your web service**, you have two options:

   **Option A: Use DATABASE_URL (Easiest - Recommended)**
   ```env
   DB_CONNECTION=pgsql
   DATABASE_URL=${{Postgres.DATABASE_URL}}
   ```
   
   **Option B: Use individual variables**
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   ```

## Complete Example

After setup, your web service variables should look like:

```env
# Application
APP_NAME=Form Monitor
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

# Database (using Railway DATABASE_URL - Easiest method)
DB_CONNECTION=pgsql
DATABASE_URL=${{Postgres.DATABASE_URL}}

# OR use individual variables (if DATABASE_URL doesn't work):
# DB_HOST=${{Postgres.PGHOST}}
# DB_PORT=${{Postgres.PGPORT}}
# DB_DATABASE=${{Postgres.PGDATABASE}}
# DB_USERNAME=${{Postgres.PGUSER}}
# DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Redis
REDIS_HOST=${{Redis.REDIS_HOST}}
REDIS_PASSWORD=${{Redis.REDIS_PASSWORD}}
REDIS_PORT=${{Redis.REDIS_PORT}}

# Drivers
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Telescope (disabled)
TELESCOPE_ENABLED=false
```

## Troubleshooting

### "Connection refused" Error

- **Check database service is running** - Make sure the database service shows as "Active" in Railway
- **Verify variable names** - Make sure you're using the correct service name in `${{ServiceName.VAR}}`
- **Check variable syntax** - Should be `${{ServiceName.VARIABLE}}` with double curly braces

### Can't Find Database Variables

1. Click on your database service
2. Go to "Variables" or "Connect" tab
3. Look for connection details
4. Railway might use different variable names (like `PGHOST` instead of `DB_HOST`)

### Wrong Service Name

- Railway service names are case-sensitive
- If your service is called "PostgreSQL", use `${{PostgreSQL.VAR}}`
- If it's called "postgres", use `${{postgres.VAR}}`
- Check the exact name in your Railway dashboard

## After Setup

Once the database is configured:

1. **Redeploy your web service** (or it will auto-redeploy)
2. **Migrations should run successfully**
3. **Your app should start without database errors**

## Next Steps

After the database is working:

1. Run migrations manually if needed:
   ```bash
   railway run php artisan migrate --force
   ```

2. Seed the database:
   ```bash
   railway run php artisan db:seed --class=AdminUserSeeder
   ```

3. Verify connection:
   ```bash
   railway run php artisan tinker
   # Then in tinker: DB::connection()->getPdo();
   ```

---

**That's it!** Your database service is now connected to your Laravel application.

