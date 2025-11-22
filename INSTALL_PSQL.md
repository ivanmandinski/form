# How to Install psql (PostgreSQL Client)

You need `psql` installed to connect to Railway's PostgreSQL database.

## macOS Installation

### Option 1: Using Homebrew (Recommended)

If you have Homebrew installed:

```bash
brew install postgresql@16
```

Or for the latest version:

```bash
brew install postgresql
```

This will install PostgreSQL client tools including `psql`.

### Option 2: Using Homebrew (Postgres.app)

Alternatively, you can install Postgres.app which includes psql:

```bash
brew install --cask postgresql
```

### Option 3: Download PostgreSQL

1. Go to https://www.postgresql.org/download/macosx/
2. Download the installer
3. Install it (this includes psql)

## Verify Installation

After installing, verify psql is available:

```bash
psql --version
```

You should see something like: `psql (PostgreSQL) 16.x`

## Connect to Railway

Once installed, you can connect:

```bash
railway connect postgres
```

## Alternative: Use Railway Dashboard

If you don't want to install psql, you can use Railway's web interface:

1. Go to Railway Dashboard → Your PostgreSQL service
2. Look for:
   - **"Query"** tab
   - **"Data"** tab  
   - **"Connect"** → Query interface
   - Database viewer

3. Run your SQL commands there directly in the browser

## Quick SQL Commands (Once Connected)

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

## Troubleshooting

### "Command not found: brew"
Install Homebrew first:
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### "Permission denied"
You might need to add PostgreSQL to your PATH. After installing with Homebrew:
```bash
echo 'export PATH="/opt/homebrew/opt/postgresql@16/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

Or for Intel Macs:
```bash
echo 'export PATH="/usr/local/opt/postgresql@16/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

## Recommended: Use Railway Dashboard Instead

If installing psql is complicated, **use Railway's web interface** - it's easier and doesn't require any local installation!


