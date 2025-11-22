# Railway Deployment Guide for Form Monitor

This guide will help you deploy the Form Monitor Laravel application to Railway.app, a modern platform-as-a-service that simplifies deployment and scaling.

## Prerequisites

- A Railway account ([railway.app](https://railway.app))
- GitHub repository with your code (recommended) or Railway CLI installed
- Basic understanding of Laravel and environment variables

## Overview

Railway will automatically detect your Laravel application and configure PHP, but you'll need to:

1. Set up the main web service
2. Add a PostgreSQL/MySQL database service
3. Add a Redis service (for queues and cache)
4. Configure environment variables
5. Set up background workers (Horizon, Scheduler)
6. Configure Puppeteer/Chrome dependencies

## Step 1: Initial Setup

### Option A: Deploy from GitHub (Recommended)

1. **Create a Railway Account**
   - Go to [railway.app](https://railway.app) and sign up
   - Connect your GitHub account

2. **Create New Project**
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your repository

3. **Railway will automatically:**
   - Detect it's a PHP/Laravel application
   - Set up a web service
   - Start building your application

### Option B: Deploy using Railway CLI

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project in your repository
railway init

# Deploy
railway up
```

## Step 2: Add Database Service

1. **In Railway Dashboard:**
   - Click "+ New" in your project
   - Select "Database"
   - Choose **PostgreSQL** (recommended) or **MySQL**

2. **Railway will automatically:**
   - Create the database
   - Provide connection variables:
     - `DATABASE_URL` (or `MYSQL_URL` / `POSTGRES_URL`)
     - Individual variables: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_DATABASE`

## Step 3: Add Redis Service

1. **In Railway Dashboard:**
   - Click "+ New" in your project
   - Select "Database"
   - Choose **Redis**

2. **Railway will automatically provide:**
   - `REDIS_URL`
   - Or individual: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`

## Step 4: Configure Environment Variables

In your main web service, go to **Variables** tab and add:

### Application Configuration

```env
APP_NAME="Form Monitor"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Database Configuration

For PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.DB_HOST}}
DB_PORT=${{Postgres.DB_PORT}}
DB_DATABASE=${{Postgres.DB_DATABASE}}
DB_USERNAME=${{Postgres.DB_USER}}
DB_PASSWORD=${{Postgres.DB_PASSWORD}}
```

For MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=${{MySQL.DB_HOST}}
DB_PORT=${{MySQL.DB_PORT}}
DB_DATABASE=${{MySQL.DB_DATABASE}}
DB_USERNAME=${{MySQL.DB_USER}}
DB_PASSWORD=${{MySQL.DB_PASSWORD}}
```

### Redis Configuration

```env
REDIS_HOST=${{Redis.REDIS_HOST}}
REDIS_PASSWORD=${{Redis.REDIS_PASSWORD}}
REDIS_PORT=${{Redis.REDIS_PORT}}
REDIS_URL=${{Redis.REDIS_URL}}
```

### Cache and Queue Configuration

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=redis
```

### Mail Configuration (Update as needed)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Form Monitor Specific Settings

```env
FORM_MONITOR_USER_AGENT="Form Monitor Bot/1.0"
FORM_MONITOR_HTTP_TIMEOUT=30
FORM_MONITOR_PUPPETEER_TIMEOUT=120
FORM_MONITOR_MAX_CONCURRENT_PER_HOST=2
FORM_MONITOR_GLOBAL_MAX_CONCURRENT=10
FORM_MONITOR_ARTIFACT_RETENTION_DAYS=30
FORM_MONITOR_MAX_HTML_SIZE=1048576
FORM_MONITOR_SCREENSHOT_QUALITY=80
```

### Puppeteer Settings

```env
PUPPETEER_HEADLESS=true
PUPPETEER_DEBUG=false
PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
```

### CAPTCHA Solver Settings (Optional)

```env
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key
CAPTCHA_SOLVER_PROVIDER=2captcha
CAPTCHA_SOLVER_TIMEOUT=120
CAPTCHA_SOLVER_RETRY_ATTEMPTS=3
CAPTCHA_SOLVE_RECAPTCHA_V3=true
CAPTCHA_SOLVE_RECAPTCHA_V2=true
CAPTCHA_SOLVE_HCAPTCHA=true
CAPTCHA_VISUAL_FEEDBACK=true
```

### Telescope Configuration

```env
TELESCOPE_ENABLED=false
```

**Note:** Telescope should be disabled in production or protected with proper authentication.

### Generate APP_KEY

After setting up variables, generate the application key:

1. Go to your service's **Settings** tab
2. In the **Deploy** section, add a build command or run manually:
   ```bash
   php artisan key:generate --force
   ```

Or add this to your `railway.json` (see Step 6).

## Step 5: Configure Build Settings

Railway will detect your PHP application automatically, but you may want to customize the build:

### Create `nixpacks.toml` (Optional)

Create a `nixpacks.toml` file in your project root for custom build configuration:

```toml
[phases.setup]
nixPkgs = ["php82", "php82Packages.composer", "nodejs_20"]

[phases.install]
cmds = [
    "composer install --no-dev --optimize-autoloader",
    "npm ci",
    "npm run build"
]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
```

### Alternative: Use Railway's automatic detection

Railway will automatically detect PHP and Node.js if `composer.json` and `package.json` are present.

## Step 6: Create Railway Configuration

Create a `railway.json` file in your project root:

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan key:generate --force",
    "watchPatterns": [
      "**/*.php",
      "**/*.js",
      "**/*.css"
    ]
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

Or use a `Procfile` for multiple processes:

```procfile
web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
horizon: php artisan horizon
scheduler: php artisan schedule:work
```

## Step 7: Set Up Background Workers

Your application needs three processes running:
1. **Web server** (main app)
2. **Horizon** (queue worker)
3. **Scheduler** (cron jobs)

### Option A: Separate Services (Recommended)

Create three separate services in Railway:

1. **Web Service** (already created)
   - Uses the `web` process from Procfile
   - Public endpoint

2. **Horizon Worker Service**
   - Add a new service from the same repository
   - Set the start command: `php artisan horizon`
   - Same environment variables as web service

3. **Scheduler Service**
   - Add a new service from the same repository
   - Set the start command: `php artisan schedule:work`
   - Same environment variables as web service

### Option B: Single Service with Procfile

Use the `Procfile` approach and Railway will run all processes:

```procfile
web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
horizon: php artisan horizon
scheduler: php artisan schedule:work
```

## Step 8: Install Chrome/Puppeteer Dependencies

Puppeteer requires Chrome/Chromium. Add this to your build configuration:

### Update `nixpacks.toml`:

```toml
[phases.setup]
nixPkgs = [
    "php82",
    "php82Packages.composer",
    "nodejs_20",
    "chromium",
    "nss",
    "atk",
    "at-spi2-atk",
    "libdrm",
    "libxcomposite",
    "libxdamage",
    "libxrandr",
    "mesa",
    "libxss",
    "alsa-lib",
    "libatk-bridge"
]

[phases.install]
cmds = [
    "composer install --no-dev --optimize-autoloader",
    "npm ci",
    "npm run build",
    "ln -s /nix/store/*-chromium-*/bin/chromium /usr/bin/chrome || true",
    "ln -s /nix/store/*-chromium-*/bin/chromium /usr/bin/chromium-browser || true"
]

[providers]
php = "82"

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
```

### Alternative: Install via build script

Create a `.railway/build.sh` script:

```bash
#!/bin/bash
set -e

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci

# Build assets
npm run build

# Install Chrome dependencies for Puppeteer
apt-get update
apt-get install -y \
    chromium \
    chromium-chromedriver \
    libnss3-dev \
    libatk-bridge2.0-dev \
    libdrm2 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libxss1 \
    libasound2

# Generate app key if not set
php artisan key:generate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 9: Run Database Migrations

### Option 1: Automatic on Deploy

Update your Procfile or start command:
```procfile
web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

### Option 2: Manual via Railway CLI

```bash
railway run php artisan migrate --force
```

### Option 3: Run via Railway Dashboard

1. Go to your service
2. Click "Deployments"
3. Find a deployment
4. Click "View Logs"
5. Use the terminal to run: `php artisan migrate --force`

### Seed Database (Optional)

```bash
railway run php artisan db:seed --class=AdminUserSeeder
railway run php artisan db:seed --class=DemoDataSeeder
```

## Step 10: Configure Storage

Railway provides ephemeral storage. For persistent file storage:

1. **Use Railway Volume** (Recommended for artifacts)
   - Add a volume in your service settings
   - Mount it to `/app/storage/app`

2. **Use S3/Cloud Storage** (Better for production)
   - Configure AWS S3 or compatible storage
   - Update `config/filesystems.php`
   - Set `FILESYSTEM_DISK=s3` in environment variables

## Step 11: Set Up Custom Domain

1. **In Railway Dashboard:**
   - Go to your web service
   - Click "Settings" → "Networking"
   - Click "Generate Domain" for Railway domain, or
   - Click "+ Custom Domain" for your own domain

2. **For Custom Domain:**
   - Add your domain
   - Railway provides DNS records to add
   - SSL is automatically configured

3. **Update APP_URL:**
   - Set `APP_URL` environment variable to your custom domain

## Step 12: Configure Health Checks

Railway automatically health checks, but you can create a health check endpoint:

Add to `routes/web.php`:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
    ]);
});
```

## Step 13: Monitoring and Logs

### View Logs
- **Railway Dashboard**: Click on your service → "Deployments" → "View Logs"
- **Railway CLI**: `railway logs`

### Set Up Monitoring
- Railway provides built-in metrics
- Configure external monitoring if needed (Sentry, etc.)

## Step 14: Production Optimizations

After deployment, run these optimizations:

```bash
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache
railway run php artisan event:cache
```

## Environment Variable Reference

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_NAME` | Application name | Yes |
| `APP_ENV` | Environment (production) | Yes |
| `APP_KEY` | Laravel encryption key | Yes |
| `APP_DEBUG` | Debug mode (false) | Yes |
| `APP_URL` | Application URL | Yes |
| `DB_CONNECTION` | Database type (pgsql/mysql) | Yes |
| `DB_HOST` | Database host | Yes |
| `DB_DATABASE` | Database name | Yes |
| `DB_USERNAME` | Database user | Yes |
| `DB_PASSWORD` | Database password | Yes |
| `REDIS_HOST` | Redis host | Yes |
| `REDIS_PASSWORD` | Redis password | No |
| `REDIS_PORT` | Redis port | Yes |
| `CACHE_DRIVER` | Cache driver (redis) | Yes |
| `QUEUE_CONNECTION` | Queue driver (redis) | Yes |
| `SESSION_DRIVER` | Session driver (redis) | Yes |

## Troubleshooting

### Build Fails

1. **Check build logs** in Railway dashboard
2. **Verify Node.js version** - Railway uses Node 18+ by default
3. **Check PHP version** - Ensure PHP 8.2+ is specified

### Application Won't Start

1. **Check environment variables** - Ensure all required vars are set
2. **Check logs** - `railway logs` or dashboard logs
3. **Verify APP_KEY** - Run `php artisan key:generate --force`
4. **Check database connection** - Verify DB variables are correct

### Puppeteer/Chrome Issues

1. **Verify Chrome is installed** - Check build logs
2. **Set PUPPETEER_EXECUTABLE_PATH** environment variable
3. **Check Chrome dependencies** - Ensure all libraries are installed

### Queue Jobs Not Processing

1. **Verify Horizon is running** - Check worker service logs
2. **Check Redis connection** - Verify Redis variables
3. **Check Horizon config** - Ensure Horizon is configured correctly

### Database Connection Errors

1. **Verify database service** is running
2. **Check connection variables** use Railway's variable references (`${{Postgres.DB_HOST}}`)
3. **Test connection** - `railway run php artisan tinker` then test DB connection

### Storage Permission Issues

1. **Check storage permissions** - May need to adjust in build script
2. **Use volumes** - Mount Railway volumes for persistent storage
3. **Use cloud storage** - Consider S3 for production

## Cost Optimization

- **Use Railway's free tier** for testing (limited hours)
- **Scale down services** when not in use
- **Use Railway Hobby plan** for small production apps
- **Monitor usage** in Railway dashboard

## Maintenance

### Updating the Application

1. **Push to GitHub** (if using GitHub deployment)
2. **Railway automatically deploys** (if auto-deploy is enabled)
3. **Or manually deploy**: `railway up`

### Running Migrations

```bash
railway run php artisan migrate --force
```

### Clearing Cache

```bash
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan route:clear
railway run php artisan view:clear
```

### Viewing Queue Status

Access Horizon dashboard at: `https://your-app.up.railway.app/horizon`

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] Redis password set (if needed)
- [ ] `TELESCOPE_ENABLED=false` or properly secured
- [ ] CAPTCHA solver API keys secured
- [ ] Environment variables not exposed in logs
- [ ] SSL/HTTPS enabled (automatic on Railway)
- [ ] Admin routes protected

## Next Steps

1. **Set up monitoring** (Sentry, etc.)
2. **Configure backups** for database
3. **Set up alerts** for errors
4. **Optimize performance** based on usage
5. **Scale services** as needed

## Support

- **Railway Docs**: [docs.railway.app](https://docs.railway.app)
- **Railway Discord**: [discord.gg/railway](https://discord.gg/railway)
- **Laravel Docs**: [laravel.com/docs](https://laravel.com/docs)

---

**Deployment Complete!** 🚀

Your Form Monitor application should now be running on Railway. Access it at your Railway domain or custom domain.

