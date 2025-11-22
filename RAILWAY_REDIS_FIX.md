# Fix Redis Connection Error

The error "Failed to parse address ':' [tcp://:]" means Redis connection variables are not set or empty.

## Quick Fix

### Option 1: If You Have a Redis Service

1. **Find your Redis service name** in Railway:
   - Go to Railway Dashboard → Your Project
   - Find your Redis service
   - Note its exact name (might be "Redis", "redis", etc.)

2. **Add Redis variables to your web service:**
   - Go to your web service → Variables tab
   - Add these variables (replace `Redis` with your actual service name):

```env
REDIS_CLIENT=predis
REDIS_HOST=${{Redis.REDIS_HOST}}
REDIS_PASSWORD=${{Redis.REDIS_PASSWORD}}
REDIS_PORT=${{Redis.REDIS_PORT}}
```

3. **Use Railway's Reference feature:**
   - Click "Add Variable" → Type `REDIS_HOST`
   - Click "Reference" button → Select your Redis service → Select `REDIS_HOST`
   - Repeat for `REDIS_PASSWORD` and `REDIS_PORT`

### Option 2: If You Don't Have a Redis Service Yet

1. **Add Redis service in Railway:**
   - Click "+ New" → "Database" → Choose "Redis"
   - Railway will create it automatically

2. **Then follow Option 1 above**

### Option 3: Temporarily Disable Redis (Quick Fix)

If you don't need Redis right now, you can temporarily use database for cache/sessions:

1. **In your web service → Variables tab, add:**

```env
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
BROADCAST_DRIVER=log
```

2. **This will use your PostgreSQL database instead of Redis**

## Verify Redis Variables

After setting up, verify the variables are set:

1. Check Railway logs or run:
   ```bash
   railway run env | grep REDIS
   ```

2. You should see:
   - `REDIS_HOST` with a value (not empty)
   - `REDIS_PORT` with a value (not empty)
   - `REDIS_PASSWORD` (might be empty if no password)

## Common Issues

1. **Service name mismatch:** Make sure `Redis` in `${{Redis.REDIS_HOST}}` matches your actual service name exactly (case-sensitive)

2. **Variables not resolving:** Use Railway's Reference feature instead of typing manually

3. **Redis service not running:** Make sure your Redis service shows as "Active" in Railway

