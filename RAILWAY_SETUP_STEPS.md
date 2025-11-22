# Railway Database Setup - Step by Step

Follow these exact steps to connect your web service to the Postgres database:

## Step 1: Go to Your Web Service

1. Railway Dashboard → Project: `fearless-light` → Environment: `production`
2. Click on your **web service** (NOT the Postgres service)
3. Go to the **"Variables"** tab

## Step 2: Create DATABASE_URL Variable

1. Click **"Add Variable"** button
2. **Variable name:** `DATABASE_URL`
3. **Variable value:** `${{ Postgres.DATABASE_URL }}`
   - Note: There's a space after `${{` - make sure it's exactly: `${{ Postgres.DATABASE_URL }}`
4. Click **"Save"** or **"Add"**

## Step 3: Add DB_CONNECTION Variable

1. Click **"Add Variable"** again
2. **Variable name:** `DB_CONNECTION`
3. **Variable value:** `pgsql`
4. Click **"Save"**

## Step 4: Remove Conflicting Variables (If They Exist)

If you have any of these variables, **delete them**:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

These will conflict with `DATABASE_URL`.

## Step 5: Verify

Your web service should now have these two variables:
- `DB_CONNECTION=pgsql`
- `DATABASE_URL=${{ Postgres.DATABASE_URL }}`

## Step 6: Wait for Redeploy

Railway will automatically redeploy your service. Check the logs - the database connection error should be gone!

## Important Notes

- Make sure there's a **space** in `${{ Postgres.DATABASE_URL }}` (after `${{`)
- The service name `Postgres` must match exactly (case-sensitive)
- Railway will resolve `${{ Postgres.DATABASE_URL }}` to the actual connection string at runtime

## Troubleshooting

If it still doesn't work:
1. Check that `Postgres` matches your actual service name exactly
2. Verify the variable is saved correctly in Railway
3. Check Railway logs for any errors
4. Make sure the Postgres service is running

