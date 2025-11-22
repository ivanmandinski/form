# How to Find Your Service Name in Railway

The service name is needed for variable references like `${{ServiceName.DATABASE_URL}}`. Here's how to find it:

## Method 1: From the Project Dashboard (Easiest)

1. **Go to Railway Dashboard** → Select your project
2. **Look at the list of services** in your project
3. **Find your PostgreSQL service** (or Redis service)
4. **The service name is displayed** as the title/name of that service card

Example:
```
Your Project
├── web-production-xxxxx  ← This is your web service name
├── Postgres              ← This is your PostgreSQL service name
└── Redis                 ← This is your Redis service name
```

## Method 2: Click on the Service

1. **Click on your PostgreSQL service** in the Railway dashboard
2. **Look at the top of the page** - the service name is shown in the header/breadcrumb
3. **Or check the URL** - it might show something like:
   ```
   https://railway.app/project/xxx/service/yyy
   ```
   The service name is usually visible in the page title or header

## Method 3: From the Variables Tab

1. **Click on your PostgreSQL service**
2. **Go to the "Variables" tab**
3. **Look at the variable names** - they might reference the service name
4. **Or check the "Connect" tab** - it might show connection details with the service name

## Method 4: Check Service Settings

1. **Click on your PostgreSQL service**
2. **Go to "Settings"** (gear icon or settings tab)
3. **Look for "Service Name"** or "Name" field
4. This shows the exact service name

## Common Service Names

Railway often uses these default names:
- `Postgres` or `PostgreSQL` (for PostgreSQL databases)
- `MySQL` (for MySQL databases)
- `Redis` (for Redis)
- `web-production-xxxxx` (for web services, where xxxxx is a random string)

## Important Notes

1. **Service names are case-sensitive!**
   - `Postgres` ≠ `postgres` ≠ `POSTGRES`
   - Use the exact case as shown in Railway

2. **Service names can be changed:**
   - You can rename services in Railway
   - Use the name as it appears NOW, not what it was originally

3. **Check both services:**
   - You need the PostgreSQL service name for database variables
   - You need the Redis service name for Redis variables

## Example: Finding and Using Service Names

Let's say you found:
- PostgreSQL service name: `Postgres`
- Redis service name: `Redis`

Then in your web service variables, you would use:

```env
# Database
DB_CONNECTION=pgsql
DATABASE_URL=${{Postgres.DATABASE_URL}}

# Redis
REDIS_HOST=${{Redis.REDIS_HOST}}
REDIS_PASSWORD=${{Redis.REDIS_PASSWORD}}
REDIS_PORT=${{Redis.REDIS_PORT}}
```

## Visual Guide

```
Railway Dashboard
│
├── Project: "My Form Monitor App"
│   │
│   ├── Service: "web-production-85cc5"  ← Web service
│   │   └── Variables tab
│   │       └── Need to add: DATABASE_URL=${{Postgres.DATABASE_URL}}
│   │
│   ├── Service: "Postgres"  ← PostgreSQL service (THIS IS THE NAME!)
│   │   └── Variables tab
│   │       └── Has: DATABASE_URL, PGHOST, PGPORT, etc.
│   │
│   └── Service: "Redis"  ← Redis service (THIS IS THE NAME!)
│       └── Variables tab
│           └── Has: REDIS_HOST, REDIS_PASSWORD, etc.
```

## Quick Test

Once you find the service name, you can verify it works:

1. In your web service → Variables tab
2. Add: `DATABASE_URL=${{YourServiceName.DATABASE_URL}}`
3. Save
4. Check if Railway shows a green checkmark or link icon next to the variable
5. If it shows an error or doesn't resolve, the service name is wrong

## Still Can't Find It?

1. **Take a screenshot** of your Railway dashboard and look for the service name
2. **Check the service card** - the name is usually the largest text on the card
3. **Look in the sidebar** - if you click on a service, the sidebar might show the name
4. **Check the page title** - the browser tab title might show the service name

## Pro Tip: Use Railway's Reference Feature

Railway makes this easier:

1. In your web service → Variables tab
2. Click "Add Variable"
3. Type: `DATABASE_URL`
4. Click the **"Reference" button** (or link/chain icon)
5. Railway will show you a list of services
6. Select your PostgreSQL service
7. Select `DATABASE_URL` from the list
8. Railway automatically creates: `DATABASE_URL=${{CorrectServiceName.DATABASE_URL}}`

This way, you don't need to know the service name - Railway does it for you!

