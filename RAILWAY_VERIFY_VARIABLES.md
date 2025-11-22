# Verify Railway Variables Are Set Correctly

The error you're seeing means either:
1. `DATABASE_URL` is not set or not resolving correctly
2. Individual DB variables are still set and interfering

## Step 1: Check Your Web Service Variables

1. Go to Railway Dashboard → Your Web Service → Variables tab
2. **List ALL variables** that start with `DB_` or `DATABASE_`
3. You should ONLY have:
   - `DB_CONNECTION=pgsql`
   - `DATABASE_URL=${{ Postgres.DATABASE_URL }}`

## Step 2: Delete ALL Individual DB Variables

**Delete these if they exist:**
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- Any other `DB_*` variables except `DB_CONNECTION`

## Step 3: Verify DATABASE_URL is Set Correctly

1. In your web service → Variables tab
2. Find `DATABASE_URL`
3. The value should be: `${{ Postgres.DATABASE_URL }}`
   - Note: There's a space after `${{`
4. Make sure `Postgres` matches your service name exactly

## Step 4: Check if Railway is Resolving the Variable

1. In Railway, when you view the `DATABASE_URL` variable
2. Railway should show a link/icon indicating it's referencing another service
3. If it shows as plain text `${{ Postgres.DATABASE_URL }}`, it might not be resolving

## Step 5: Use Railway's Reference Feature (Recommended)

Instead of typing manually, use Railway's reference feature:

1. Delete the existing `DATABASE_URL` variable if it exists
2. Click "Add Variable"
3. Variable name: `DATABASE_URL`
4. **Click the "Reference" button** (link/chain icon)
5. Select "Postgres" service
6. Select `DATABASE_URL` from the dropdown
7. Railway will create the correct reference automatically

## Step 6: Verify Postgres Service Has DATABASE_URL

1. Go to Railway Dashboard → Postgres service → Variables tab
2. Look for `DATABASE_URL` - it should exist
3. If it doesn't exist, Railway might not provide it (unlikely but possible)

## Step 7: Test After Changes

After making changes:
1. Save all variables
2. Wait for Railway to redeploy (or trigger a redeploy)
3. Check the logs - the error should be gone

## Alternative: Check What Railway Actually Provides

If `DATABASE_URL` doesn't work, check what variables Railway actually provides:

1. Go to Postgres service → Variables tab
2. Note all variable names (might be `PGHOST`, `PGPORT`, etc.)
3. Share them and we can construct the connection differently

## Debug: Check Environment Variables at Runtime

You can check what variables Laravel sees:

```bash
railway run env | grep -E "(DATABASE|DB_)"
```

This will show you what environment variables are actually set when the app runs.

