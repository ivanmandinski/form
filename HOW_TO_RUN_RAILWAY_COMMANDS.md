# How to Run Railway Commands

## Prerequisites

You need the Railway CLI installed on your computer.

### Install Railway CLI

**macOS/Linux:**
```bash
curl -fsSL https://railway.app/install.sh | sh
```

**Windows (PowerShell):**
```powershell
iwr https://railway.app/install.ps1 | iex
```

**Or using npm:**
```bash
npm install -g @railway/cli
```

## Step-by-Step: Run AdminUserSeeder

### Step 1: Login to Railway

```bash
railway login
```

This will open your browser to authenticate with Railway.

### Step 2: Link to Your Project

Navigate to your project directory:
```bash
cd /Users/ivanm/Downloads/form
```

Link to your Railway project:
```bash
railway link
```

This will show you a list of your Railway projects. Select the one you want to use (e.g., `fearless-light`).

### Step 3: Run the Seeder

Once linked, run the seeder command:

```bash
railway run php artisan db:seed --class=AdminUserSeeder
```

This will:
- Connect to your Railway project
- Run the command in your Railway environment
- Create the admin user with credentials:
  - Email: `admin@formmonitor.com`
  - Password: `password`

## Alternative: Run from Railway Dashboard

If you don't want to use the CLI, you can also run commands from the Railway dashboard:

1. Go to Railway Dashboard → Your Project → Your Web Service
2. Click on "Deployments" tab
3. Click on the latest deployment
4. Click "View Logs" or use the terminal/console feature
5. Run the command there

However, the CLI method is easier and more reliable.

## Verify It Worked

After running the seeder, you can verify the admin user was created:

```bash
railway run php artisan tinker
```

Then in tinker:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
echo $user->name . "\n";
echo $user->hasRole('admin') ? "Has admin role\n" : "No admin role\n";
exit
```

## Other Useful Railway Commands

### Run Migrations
```bash
railway run php artisan migrate
```

### Clear Cache
```bash
railway run php artisan config:clear
railway run php artisan cache:clear
```

### View Logs
```bash
railway logs
```

### Check Environment Variables
```bash
railway variables
```

### Open Railway Dashboard
```bash
railway open
```

## Troubleshooting

### "Command not found: railway"
- Make sure Railway CLI is installed
- Check if it's in your PATH
- Try reinstalling: `npm install -g @railway/cli`

### "Not linked to a project"
- Run `railway link` in your project directory
- Select the correct project from the list

### "Authentication required"
- Run `railway login` to authenticate
- Make sure you're logged into the correct Railway account

### Command fails with database errors
- Make sure your database service is running in Railway
- Check that database environment variables are set correctly
- Verify migrations have run: `railway run php artisan migrate:status`


