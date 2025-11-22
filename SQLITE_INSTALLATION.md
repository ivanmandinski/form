# SQLite Installation Guide for Form Monitor

This guide is specifically for installations using SQLite database instead of MySQL.

## Prerequisites

- VPS with Ubuntu 20.04/22.04 LTS
- Root or sudo access
- Files already transferred to `/var/www/form-monitor`

## Step 1: Update System and Install Basic Packages

```bash
# Update package list
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
```

## Step 2: Install PHP 8.2

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and required extensions (including SQLite)
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-sqlite3 php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl php8.2-readline php8.2-soap php8.2-xsl php8.2-pdo php8.2-tokenizer php8.2-dom php8.2-fileinfo php8.2-filter php8.2-hash php8.2-openssl php8.2-pcre php8.2-reflection php8.2-session php8.2-simplexml php8.2-spl php8.2-standard php8.2-xmlreader php8.2-xmlwriter php8.2-zlib

# Verify PHP installation
php -v
```

## Step 3: Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify Composer installation
composer --version
```

## Step 4: Install Node.js and NPM

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify Node.js and NPM installation
node --version
npm --version
```

## Step 5: Install Nginx

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check Nginx status
sudo systemctl status nginx
```

## Step 6: Install Redis (Optional but Recommended)

```bash
# Install Redis
sudo apt install -y redis-server

# Start and enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check Redis status
sudo systemctl status redis-server
```

## Step 7: Install Google Chrome for Puppeteer

```bash
# Install Google Chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt update
sudo apt install -y google-chrome-stable

# Install Puppeteer dependencies
sudo apt install -y libnss3-dev libatk-bridge2.0-dev libdrm2 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libxss1 libasound2

# Verify Chrome installation
google-chrome --version
```

## Step 8: Configure Application

### 8.1 Navigate to Application Directory

```bash
# Navigate to your application directory
cd /var/www/form-monitor

# Set proper ownership
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache database
```

### 8.2 Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### 8.3 Configure Environment for SQLite

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

Update the following in your `.env` file:
```bash
APP_NAME="Form Monitor"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

# SQLite Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/form-monitor/database/database.sqlite

# Redis Configuration
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Form Monitor specific settings
FORM_MONITOR_USER_AGENT="Form Monitor Bot/1.0"
FORM_MONITOR_HTTP_TIMEOUT=30
FORM_MONITOR_PUPPETEER_TIMEOUT=120

# Puppeteer settings
PUPPETEER_HEADLESS=true
PUPPETEER_DEBUG=false
PUPPETEER_USER_DATA_DIR=/var/www/form-monitor/storage/puppeteer

# CAPTCHA Solver settings (optional)
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key
CAPTCHA_SOLVER_PROVIDER=2captcha
```

### 8.4 Create SQLite Database and Run Migrations

```bash
# Create SQLite database file
touch database/database.sqlite

# Set proper permissions for database file
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### 8.5 Create Puppeteer Directory

```bash
# Create Puppeteer user data directory
sudo mkdir -p storage/puppeteer
sudo chown -R www-data:www-data storage/puppeteer
sudo chmod -R 755 storage/puppeteer
```

## Step 9: Configure Nginx

### 9.1 Create Nginx Site Configuration

```bash
# Create site configuration
sudo nano /etc/nginx/sites-available/form-monitor
```

Add the following configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/form-monitor/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Client max body size
    client_max_body_size 100M;
}
```

### 9.2 Enable Site

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/form-monitor /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## Step 10: Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

## Step 11: Setup Queue Worker

### 11.1 Create Queue Worker Service

```bash
# Create systemd service file
sudo nano /etc/systemd/system/form-monitor-worker.service
```

Add the following content:
```ini
[Unit]
Description=Form Monitor Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/form-monitor/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/form-monitor

[Install]
WantedBy=multi-user.target
```

### 11.2 Enable and Start Queue Worker

```bash
# Enable and start service
sudo systemctl enable form-monitor-worker
sudo systemctl start form-monitor-worker

# Check status
sudo systemctl status form-monitor-worker
```

## Step 12: Setup Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/form-monitor && php artisan schedule:run >> /dev/null 2>&1
```

## Step 13: Configure Firewall

```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# Enable firewall
sudo ufw enable
```

## Step 14: Optimize Application

```bash
# Navigate to application directory
cd /var/www/form-monitor

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
composer dump-autoload --optimize
```

## Step 15: Test Installation

### 15.1 Test Application

```bash
# Test Laravel application
php artisan route:list

# Test API endpoint
curl -I https://yourdomain.com/api/public/health
```

### 15.2 Test Form Submission

```bash
# Test form submission with curl
curl -X POST "https://yourdomain.com/api/forms/test" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://httpbin.org/forms/post",
    "selector_type": "css",
    "selector_value": "form",
    "field_mappings": [
      {
        "name": "custname",
        "value": "Test User"
      }
    ]
  }'
```

## SQLite-Specific Considerations

### Database File Permissions

```bash
# Ensure proper permissions for SQLite database
sudo chown www-data:www-data /var/www/form-monitor/database/database.sqlite
sudo chmod 664 /var/www/form-monitor/database/database.sqlite

# Ensure database directory is writable
sudo chmod 775 /var/www/form-monitor/database
```

### Backup SQLite Database

```bash
# Create backup script for SQLite
sudo nano /usr/local/bin/backup-form-monitor-sqlite.sh
```

Add the following content:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/form-monitor"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# SQLite database backup
cp /var/www/form-monitor/database/database.sqlite $BACKUP_DIR/database-$DATE.sqlite

# Application backup
tar -czf $BACKUP_DIR/form-monitor-backup-$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    /var/www/form-monitor

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.sqlite" -mtime +7 -delete

echo "SQLite backup completed: database-$DATE.sqlite"
```

```bash
# Make backup script executable
sudo chmod +x /usr/local/bin/backup-form-monitor-sqlite.sh

# Add to crontab for daily backups
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-form-monitor-sqlite.sh
```

## Troubleshooting SQLite Issues

### Common SQLite Issues

1. **Permission Errors**
   ```bash
   sudo chown www-data:www-data /var/www/form-monitor/database/database.sqlite
   sudo chmod 664 /var/www/form-monitor/database/database.sqlite
   sudo chmod 775 /var/www/form-monitor/database
   ```

2. **Database Locked**
   ```bash
   # Check if database is locked
   sudo lsof /var/www/form-monitor/database/database.sqlite
   
   # Kill any processes using the database
   sudo pkill -f database.sqlite
   ```

3. **Database Corruption**
   ```bash
   # Check database integrity
   sqlite3 /var/www/form-monitor/database/database.sqlite "PRAGMA integrity_check;"
   
   # If corrupted, restore from backup
   cp /var/backups/form-monitor/database-YYYYMMDD.sqlite /var/www/form-monitor/database/database.sqlite
   ```

4. **Migration Issues**
   ```bash
   # Reset migrations
   php artisan migrate:reset
   php artisan migrate --force
   ```

## Advantages of SQLite

- **No separate database server** required
- **Simpler setup** and maintenance
- **Lower resource usage**
- **Built-in backup** (just copy the file)
- **No network latency** for database operations
- **Perfect for small to medium applications**

## Limitations of SQLite

- **Single writer** limitation
- **No concurrent writes** from multiple processes
- **Limited scalability** for high-traffic applications
- **No built-in replication**

## Performance Optimization for SQLite

```bash
# Add to .env file
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/form-monitor/database/database.sqlite

# Optimize SQLite settings in config/database.php
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DATABASE_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    'busy_timeout' => 30000, // 30 seconds
],
```

## Post-Installation Checklist for SQLite

- [ ] SQLite database file created and permissions set
- [ ] Database migrations completed successfully
- [ ] Application accessible via domain
- [ ] API endpoints responding correctly
- [ ] Queue worker running
- [ ] Cron jobs scheduled
- [ ] Firewall configured
- [ ] SSL certificate installed
- [ ] Backup system configured
- [ ] Environment variables configured for SQLite

---

**SQLite Installation Complete!** 🎉

Your Form Monitor application is now running with SQLite database. The setup is simpler and requires fewer resources than MySQL.
