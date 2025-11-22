# Form Monitor VPS Deployment Guide

## Overview

This guide will help you deploy the Form Monitor application to a VPS server. We'll cover server setup, application deployment, and configuration for production use.

## Prerequisites

- VPS with Ubuntu 20.04/22.04 LTS (recommended)
- Root or sudo access
- Domain name (optional but recommended)
- Basic knowledge of Linux commands

## Server Requirements

### Minimum Requirements
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD
- **Network**: 1Gbps

### Recommended Requirements
- **CPU**: 4 cores
- **RAM**: 8GB
- **Storage**: 50GB SSD
- **Network**: 1Gbps

## Step 1: Server Setup

### 1.1 Update System Packages

```bash
# Update package list
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
```

### 1.2 Install PHP 8.2+

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl php8.2-readline php8.2-soap php8.2-sqlite3 php8.2-xsl php8.2-pdo php8.2-tokenizer php8.2-dom php8.2-fileinfo php8.2-filter php8.2-hash php8.2-openssl php8.2-pcre php8.2-reflection php8.2-session php8.2-simplexml php8.2-spl php8.2-standard php8.2-xmlreader php8.2-xmlwriter php8.2-zlib

# Verify PHP installation
php -v
```

### 1.3 Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify Composer installation
composer --version
```

### 1.4 Install Node.js and NPM

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify Node.js and NPM installation
node --version
npm --version
```

### 1.5 Install MySQL

```bash
# Install MySQL Server
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- In MySQL prompt
CREATE DATABASE form_monitor;
CREATE USER 'form_monitor'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON form_monitor.* TO 'form_monitor'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.6 Install Nginx

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check Nginx status
sudo systemctl status nginx
```

### 1.7 Install Redis (Optional but Recommended)

```bash
# Install Redis
sudo apt install -y redis-server

# Start and enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check Redis status
sudo systemctl status redis-server
```

## Step 2: Application Deployment

### 2.1 Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/form-monitor
sudo chown -R $USER:$USER /var/www/form-monitor

# Clone your repository (replace with your actual repository URL)
cd /var/www/form-monitor
git clone https://github.com/yourusername/form-monitor.git .

# Or upload files via SCP/SFTP if you don't have a Git repository
```

### 2.2 Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### 2.3 Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

### 2.4 Environment Configuration

```bash
# .env file configuration
APP_NAME="Form Monitor"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=form_monitor
DB_USERNAME=form_monitor
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Form Monitor specific settings
FORM_MONITOR_USER_AGENT="Form Monitor Bot/1.0"
FORM_MONITOR_HTTP_TIMEOUT=30
FORM_MONITOR_PUPPETEER_TIMEOUT=120
FORM_MONITOR_MAX_CONCURRENT_PER_HOST=2
FORM_MONITOR_GLOBAL_MAX_CONCURRENT=10
FORM_MONITOR_ARTIFACT_RETENTION_DAYS=30
FORM_MONITOR_MAX_HTML_SIZE=1048576
FORM_MONITOR_SCREENSHOT_QUALITY=80

# Puppeteer settings
PUPPETEER_HEADLESS=true
PUPPETEER_DEBUG=false
PUPPETEER_USER_DATA_DIR=/var/www/form-monitor/storage/puppeteer

# CAPTCHA Solver settings (2captcha.com)
CAPTCHA_SOLVER_API_KEY=your_2captcha_api_key
CAPTCHA_SOLVER_PROVIDER=2captcha
CAPTCHA_SOLVER_TIMEOUT=120
CAPTCHA_SOLVER_RETRY_ATTEMPTS=3
CAPTCHA_SOLVE_RECAPTCHA_V3=true
CAPTCHA_SOLVE_RECAPTCHA_V2=true
CAPTCHA_SOLVE_HCAPTCHA=true
CAPTCHA_VISUAL_FEEDBACK=true
```

### 2.5 Generate Application Key

```bash
# Generate application key
php artisan key:generate
```

### 2.6 Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### 2.7 Set Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/form-monitor
sudo chmod -R 755 /var/www/form-monitor
sudo chmod -R 775 /var/www/form-monitor/storage
sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
```

## Step 3: Nginx Configuration

### 3.1 Create Nginx Site Configuration

```bash
# Create site configuration
sudo nano /etc/nginx/sites-available/form-monitor
```

### 3.2 Nginx Configuration File

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
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';";

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Client max body size (for file uploads)
    client_max_body_size 100M;
}
```

### 3.3 Enable Site and SSL

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

### 3.4 Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

## Step 4: PHP-FPM Configuration

### 4.1 Configure PHP-FPM Pool

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

### 4.2 PHP-FPM Pool Settings

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Security
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Performance
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
php_admin_value[post_max_size] = 100M
php_admin_value[upload_max_filesize] = 100M
```

### 4.3 Restart PHP-FPM

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Step 5: Queue Worker Setup

### 5.1 Create Queue Worker Service

```bash
# Create systemd service file
sudo nano /etc/systemd/system/form-monitor-worker.service
```

### 5.2 Queue Worker Service Configuration

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

### 5.3 Enable and Start Queue Worker

```bash
# Enable and start service
sudo systemctl enable form-monitor-worker
sudo systemctl start form-monitor-worker

# Check status
sudo systemctl status form-monitor-worker
```

## Step 6: Cron Jobs Setup

### 6.1 Setup Laravel Scheduler

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/form-monitor && php artisan schedule:run >> /dev/null 2>&1
```

## Step 7: Puppeteer Installation

### 7.1 Install Chrome/Chromium

```bash
# Install Chromium
sudo apt install -y chromium-browser

# Or install Google Chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt update
sudo apt install -y google-chrome-stable
```

### 7.2 Install Puppeteer Dependencies

```bash
# Install additional dependencies
sudo apt install -y libnss3-dev libatk-bridge2.0-dev libdrm2 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libxss1 libasound2

# Create Puppeteer user data directory
sudo mkdir -p /var/www/form-monitor/storage/puppeteer
sudo chown -R www-data:www-data /var/www/form-monitor/storage/puppeteer
sudo chmod -R 755 /var/www/form-monitor/storage/puppeteer
```

## Step 8: Security Configuration

### 8.1 Configure Firewall

```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306/tcp  # MySQL (if needed externally)
sudo ufw allow 6379/tcp  # Redis (if needed externally)

# Enable firewall
sudo ufw enable
```

### 8.2 Secure MySQL

```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add security settings
[mysqld]
bind-address = 127.0.0.1
skip-networking = false
max_connections = 100
max_user_connections = 50
```

### 8.3 Configure Fail2Ban

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Configure Fail2Ban
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
```

## Step 9: Monitoring and Logging

### 9.1 Setup Log Rotation

```bash
# Configure log rotation
sudo nano /etc/logrotate.d/form-monitor
```

```
/var/www/form-monitor/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        /bin/kill -USR1 `cat /run/nginx.pid 2>/dev/null` 2>/dev/null || true
    endscript
}
```

### 9.2 Setup Monitoring (Optional)

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Create monitoring script
sudo nano /usr/local/bin/monitor-form-monitor.sh
```

```bash
#!/bin/bash
# Form Monitor Health Check Script

# Check if services are running
services=("nginx" "php8.2-fpm" "mysql" "redis-server" "form-monitor-worker")

for service in "${services[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "WARNING: $service is not running"
        # Send alert (email, Slack, etc.)
    fi
done

# Check disk space
disk_usage=$(df /var/www/form-monitor | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $disk_usage -gt 80 ]; then
    echo "WARNING: Disk usage is above 80%"
fi

# Check memory usage
memory_usage=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
if [ $memory_usage -gt 90 ]; then
    echo "WARNING: Memory usage is above 90%"
fi
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/monitor-form-monitor.sh

# Add to crontab for regular monitoring
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/monitor-form-monitor.sh
```

## Step 10: Backup Strategy

### 10.1 Create Backup Script

```bash
# Create backup script
sudo nano /usr/local/bin/backup-form-monitor.sh
```

```bash
#!/bin/bash
# Form Monitor Backup Script

BACKUP_DIR="/var/backups/form-monitor"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="form-monitor-backup-$DATE.tar.gz"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u form_monitor -p'your_secure_password' form_monitor > $BACKUP_DIR/database-$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/$BACKUP_FILE \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    /var/www/form-monitor

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: $BACKUP_FILE"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/backup-form-monitor.sh

# Add to crontab for daily backups
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-form-monitor.sh
```

## Step 11: Performance Optimization

### 11.1 PHP OPcache Configuration

```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
```

### 11.2 Redis Configuration

```bash
# Edit Redis configuration
sudo nano /etc/redis/redis.conf
```

```ini
# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Security
requirepass your_redis_password
```

## Step 12: Final Testing

### 12.1 Test Application

```bash
# Test Laravel application
cd /var/www/form-monitor
php artisan route:list
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test API endpoints
curl -I https://yourdomain.com/api/public/health
```

### 12.2 Performance Testing

```bash
# Install Apache Bench
sudo apt install -y apache2-utils

# Test API performance
ab -n 100 -c 10 https://yourdomain.com/api/public/health
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/form-monitor
   sudo chmod -R 755 /var/www/form-monitor
   ```

2. **Database Connection Issues**
   ```bash
   # Check MySQL status
   sudo systemctl status mysql
   
   # Test database connection
   mysql -u form_monitor -p form_monitor
   ```

3. **Puppeteer Issues**
   ```bash
   # Check Chrome installation
   google-chrome --version
   
   # Test Puppeteer
   node -e "const puppeteer = require('puppeteer'); puppeteer.launch().then(browser => browser.close())"
   ```

4. **Queue Worker Issues**
   ```bash
   # Check queue worker status
   sudo systemctl status form-monitor-worker
   
   # View logs
   sudo journalctl -u form-monitor-worker -f
   ```

## Maintenance

### Regular Maintenance Tasks

1. **Update System Packages**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Update Application**
   ```bash
   cd /var/www/form-monitor
   git pull origin main
   composer install --optimize-autoloader --no-dev
   npm run build
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Monitor Logs**
   ```bash
   # Application logs
   tail -f /var/www/form-monitor/storage/logs/laravel.log
   
   # Nginx logs
   tail -f /var/log/nginx/access.log
   tail -f /var/log/nginx/error.log
   
   # System logs
   sudo journalctl -f
   ```

## Security Checklist

- [ ] Firewall configured (UFW)
- [ ] SSL certificate installed
- [ ] Database secured
- [ ] File permissions set correctly
- [ ] Fail2Ban configured
- [ ] Regular backups scheduled
- [ ] Monitoring setup
- [ ] Updates automated
- [ ] CAPTCHA solver API key secured
- [ ] Environment variables secured

## Support

For additional support:

- **Documentation**: `API_DOCUMENTATION.md`
- **Logs**: `/var/www/form-monitor/storage/logs/`
- **Configuration**: `/var/www/form-monitor/config/`
- **Environment**: `/var/www/form-monitor/.env`

---

**Deployment Complete!** 🚀

Your Form Monitor application should now be running on your VPS server. Access it at `https://yourdomain.com` and test the API endpoints.
