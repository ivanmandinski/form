# Manual Installation Guide for Form Monitor

This guide assumes you've already manually transferred your files to the VPS. Follow these steps to complete the installation.

## Prerequisites

- VPS with Ubuntu 20.04/22.04 LTS
- Root or sudo access
- Files already transferred to `/var/www/form-monitor` (or your chosen directory)

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

# Install PHP and required extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl php8.2-readline php8.2-soap php8.2-sqlite3 php8.2-xsl php8.2-pdo php8.2-tokenizer php8.2-dom php8.2-fileinfo php8.2-filter php8.2-hash php8.2-openssl php8.2-pcre php8.2-reflection php8.2-session php8.2-simplexml php8.2-spl php8.2-standard php8.2-xmlreader php8.2-xmlwriter php8.2-zlib

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

## Step 5: Install MySQL

```bash
# Install MySQL Server
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

In the MySQL prompt, run:
```sql
CREATE DATABASE form_monitor;
CREATE USER 'form_monitor'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON form_monitor.* TO 'form_monitor'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 6: Install Nginx

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check Nginx status
sudo systemctl status nginx
```

## Step 7: Install Redis (Optional but Recommended)

```bash
# Install Redis
sudo apt install -y redis-server

# Start and enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check Redis status
sudo systemctl status redis-server
```

## Step 8: Install Google Chrome for Puppeteer

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

## Step 9: Configure Application

### 9.1 Navigate to Application Directory

```bash
# Navigate to your application directory
cd /var/www/form-monitor

# Set proper ownership
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### 9.2 Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### 9.3 Configure Environment

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

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=form_monitor
DB_USERNAME=form_monitor
DB_PASSWORD=your_secure_password

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

### 9.4 Generate Application Key and Run Migrations

```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### 9.5 Create Puppeteer Directory

```bash
# Create Puppeteer user data directory
sudo mkdir -p storage/puppeteer
sudo chown -R www-data:www-data storage/puppeteer
sudo chmod -R 755 storage/puppeteer
```

## Step 10: Configure Nginx

### 10.1 Create Nginx Site Configuration

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

### 10.2 Enable Site

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

## Step 11: Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

## Step 12: Setup Queue Worker

### 12.1 Create Queue Worker Service

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

### 12.2 Enable and Start Queue Worker

```bash
# Enable and start service
sudo systemctl enable form-monitor-worker
sudo systemctl start form-monitor-worker

# Check status
sudo systemctl status form-monitor-worker
```

## Step 13: Setup Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/form-monitor && php artisan schedule:run >> /dev/null 2>&1
```

## Step 14: Configure Firewall

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

## Step 15: Optimize Application

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

## Step 16: Test Installation

### 16.1 Test Application

```bash
# Test Laravel application
php artisan route:list

# Test API endpoint
curl -I https://yourdomain.com/api/public/health
```

### 16.2 Test Form Submission

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

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/form-monitor
   sudo chmod -R 755 /var/www/form-monitor
   sudo chmod -R 775 /var/www/form-monitor/storage
   sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
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

5. **Nginx Issues**
   ```bash
   # Check Nginx status
   sudo systemctl status nginx
   
   # Test Nginx configuration
   sudo nginx -t
   
   # View Nginx logs
   sudo tail -f /var/log/nginx/error.log
   ```

## Post-Installation Checklist

- [ ] All services running (Nginx, MySQL, Redis, PHP-FPM)
- [ ] SSL certificate installed and working
- [ ] Application accessible via domain
- [ ] API endpoints responding
- [ ] Database migrations completed
- [ ] Queue worker running
- [ ] Cron jobs scheduled
- [ ] Firewall configured
- [ ] File permissions set correctly
- [ ] Environment variables configured

## Maintenance Commands

### Update Application
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

### Monitor Logs
```bash
# Application logs
tail -f /var/www/form-monitor/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# System logs
sudo journalctl -f
```

### Check Service Status
```bash
sudo systemctl status nginx
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status php8.2-fpm
sudo systemctl status form-monitor-worker
```

---

**Installation Complete!** 🎉

Your Form Monitor application should now be running on your VPS. Access it at `https://yourdomain.com` and test the API endpoints.
