# Form Monitor

A Laravel 11 application for monitoring and testing web forms automatically. Built with Livewire, Volt, and Spatie Permission for role-based access control.

## Features

- **Form Monitoring**: Automatically test web forms using HTTP requests or headless Chrome (Dusk)
- **Scheduling**: Set up automated form checks with hourly, daily, weekly, or custom cron schedules
- **Role-Based Access**: Admin-only interface with Spatie Permission
- **Artifact Storage**: Save HTML snapshots and screenshots for each form check
- **Status Classification**: Automatically classify form submissions as success, failure, blocked, or error
- **reCAPTCHA Detection**: Detect and handle reCAPTCHA challenges without bypassing
- **Queue Support**: Redis-based job queuing with Horizon dashboard

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Database**: SQLite (default), MySQL/PostgreSQL supported
- **Frontend**: Livewire v3, Volt single-file components, Tailwind CSS
- **Authentication**: Laravel Breeze with Spatie Permission
- **HTTP Parsing**: Guzzle + Symfony DomCrawler/CssSelector
- **Headless Browser**: Laravel Dusk with Chrome
- **Queue System**: Redis + Horizon
- **Monitoring**: Telescope (local only)

## Installation

1. **Clone and install dependencies**:
   ```bash
   git clone <repository>
   cd form-monitor
   composer install
   npm install && npm run build
   ```

2. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure environment variables**:
   ```env
   APP_NAME="Form Monitor"
   QUEUE_CONNECTION=redis
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   
   # Redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   
   # Telescope (local only)
   TELESCOPE_ENABLED=true
   TELESCOPE_DRIVER=database
   
   # Dusk Configuration
   DUSK_DRIVER_URL=http://localhost:9515
   DUSK_START_CHROMEDRIVER=true
   ```

4. **Database setup**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   php artisan db:seed --class=DemoDataSeeder
   ```

5. **Start services**:
   ```bash
   # Start Horizon (in background)
   php artisan horizon
   
   # Start Dusk worker (in background)
   php artisan dusk:worker
   
   # Start scheduler (in background)
   php artisan schedule:work
   ```

## Default Admin Account

- **Email**: admin@formmonitor.com
- **Password**: password

## Usage

### 1. Add Targets

Navigate to Admin → Targets and add the URLs you want to monitor.

### 2. Configure Forms

For each target, add form configurations:
- **Selector**: CSS selector to identify the form
- **Field Mappings**: Name-value pairs for form fields
- **Schedule**: Set up automated checking frequency
- **Driver**: Choose between HTTP (fast) or Dusk (JavaScript support)

### 3. Monitor Results

View check results in the Runs section:
- **Status**: Success, failure, blocked, or error
- **Artifacts**: HTML snapshots and screenshots
- **Timing**: Start/finish times and duration

### 4. Scheduling

Forms can be scheduled with:
- **Manual**: Run only when triggered manually
- **Hourly**: Check every hour
- **Daily**: Check once per day
- **Weekly**: Check once per week
- **Cron**: Custom cron expressions

## Architecture

### Core Entities

- **Targets**: URLs to monitor
- **FormTargets**: Form configurations with selectors and schedules
- **FieldMappings**: Form field name-value pairs
- **CheckRuns**: Individual form check executions
- **CheckArtifacts**: HTML snapshots and screenshots

### Services

- **FormCheckService**: Core logic for HTTP and Dusk form checking
- **Scheduler**: Automated form execution based on schedules

### Classification Rules

1. **Success**: 2xx/3xx status + success selector match
2. **Failure**: Request OK but error/validation detected
3. **Blocked**: reCAPTCHA, WAF, 403/429 status
4. **Error**: Exceptions, timeouts, selector not found

## Development

### Running Tests

```bash
php artisan test
```

### Code Quality

```bash
./vendor/bin/pint
```

### Horizon Dashboard

Access the queue dashboard at `/horizon` (admin only)

### Telescope

Access the debugging dashboard at `/telescope` (local only)

## Security Notes

- Only test forms you own or have permission for
- reCAPTCHA is never bypassed - forms are marked as blocked
- Admin access is restricted to users with admin role
- All form submissions are logged for audit purposes

## Production Deployment

### Railway.app (Recommended)

For quick deployment on Railway.app, see [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md) for a complete guide.

### VPS Deployment

For traditional VPS deployment, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).

### General Production Checklist

1. Set `APP_ENV=production`
2. Configure Redis for production
3. Set up proper database (MySQL/PostgreSQL)
4. Configure Dusk workers for headless Chrome
5. Set up monitoring and alerting
6. Configure artifact retention policies

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
