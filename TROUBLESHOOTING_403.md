# Troubleshooting 403 Forbidden Error

A 403 Forbidden error typically indicates permission issues with your web server. Here are the most common causes and solutions.

## Common Causes of 403 Error

1. **File/Directory Permissions**
2. **Nginx Configuration Issues**
3. **PHP-FPM Configuration**
4. **SELinux/AppArmor Restrictions**
5. **Missing Index Files**
6. **Incorrect Document Root**

## Step 1: Check File Permissions

### Check Current Permissions
```bash
# Navigate to your application directory
cd /var/www/form-monitor

# Check current permissions
ls -la

# Check ownership
ls -la public/
```

### Fix File Permissions
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/form-monitor

# Set proper permissions
sudo chmod -R 755 /var/www/form-monitor
sudo chmod -R 775 /var/www/form-monitor/storage
sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
sudo chmod -R 775 /var/www/form-monitor/database

# Ensure public directory is accessible
sudo chmod 755 /var/www/form-monitor/public
sudo chmod 644 /var/www/form-monitor/public/index.php
```

## Step 2: Check Nginx Configuration

### Verify Nginx Configuration
```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check Nginx access logs
sudo tail -f /var/log/nginx/access.log
```

### Check Site Configuration
```bash
# Check if site is enabled
ls -la /etc/nginx/sites-enabled/

# Check site configuration
cat /etc/nginx/sites-available/form-monitor
```

### Fix Nginx Configuration
```bash
# Create proper Nginx configuration
sudo nano /etc/nginx/sites-available/form-monitor
```

Ensure your configuration looks like this:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/form-monitor/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

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
        fastcgi_hide_header X-Powered-By;
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

### Restart Nginx
```bash
# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Check Nginx status
sudo systemctl status nginx
```

## Step 3: Check PHP-FPM Configuration

### Check PHP-FPM Status
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check PHP-FPM configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

### Fix PHP-FPM Configuration
Ensure these settings in `/etc/php/8.2/fpm/pool.d/www.conf`:
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
```

### Restart PHP-FPM
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Check PHP-FPM status
sudo systemctl status php8.2-fpm
```

## Step 4: Check Laravel Application

### Verify Laravel Files
```bash
# Check if index.php exists
ls -la /var/www/form-monitor/public/index.php

# Check if .env file exists
ls -la /var/www/form-monitor/.env

# Check if storage directory is writable
ls -la /var/www/form-monitor/storage/
```

### Fix Laravel Configuration
```bash
# Navigate to application directory
cd /var/www/form-monitor

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions for Laravel
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Step 5: Check SELinux/AppArmor

### Check SELinux Status (if applicable)
```bash
# Check SELinux status
sestatus

# If SELinux is enabled, set proper context
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_execmem 1
sudo chcon -R -t httpd_exec_t /var/www/form-monitor
```

### Check AppArmor Status (if applicable)
```bash
# Check AppArmor status
sudo systemctl status apparmor

# If needed, disable AppArmor temporarily for testing
sudo systemctl stop apparmor
sudo systemctl disable apparmor
```

## Step 6: Test Step by Step

### Test 1: Basic File Access
```bash
# Create a test file
echo "<?php phpinfo(); ?>" | sudo tee /var/www/form-monitor/public/test.php

# Test access
curl http://yourdomain.com/test.php
```

### Test 2: Laravel Index
```bash
# Test Laravel index
curl -I http://yourdomain.com/

# Check response
curl -v http://yourdomain.com/
```

### Test 3: API Endpoint
```bash
# Test API endpoint
curl -I http://yourdomain.com/api/public/health

# Check response
curl -v http://yourdomain.com/api/public/health
```

## Step 7: Debug Mode

### Enable Laravel Debug Mode
```bash
# Edit .env file
nano /var/www/form-monitor/.env

# Set debug mode temporarily
APP_DEBUG=true
APP_ENV=local
```

### Check Laravel Logs
```bash
# Check Laravel logs
tail -f /var/www/form-monitor/storage/logs/laravel.log

# Check PHP error logs
sudo tail -f /var/log/php8.2-fpm.log
```

## Step 8: Quick Fix Script

Create a quick fix script:
```bash
# Create fix script
sudo nano /usr/local/bin/fix-403.sh
```

Add this content:
```bash
#!/bin/bash

echo "Fixing 403 Forbidden Error..."

# Set proper ownership
sudo chown -R www-data:www-data /var/www/form-monitor

# Set proper permissions
sudo chmod -R 755 /var/www/form-monitor
sudo chmod -R 775 /var/www/form-monitor/storage
sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
sudo chmod -R 775 /var/www/form-monitor/database

# Ensure public directory is accessible
sudo chmod 755 /var/www/form-monitor/public
sudo chmod 644 /var/www/form-monitor/public/index.php

# Clear Laravel caches
cd /var/www/form-monitor
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

echo "403 fix completed!"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/fix-403.sh

# Run the fix
sudo /usr/local/bin/fix-403.sh
```

## Common Solutions Summary

### Quick Fixes to Try:

1. **Fix Permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/form-monitor
   sudo chmod -R 755 /var/www/form-monitor
   sudo chmod -R 775 /var/www/form-monitor/storage
   sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
   ```

2. **Restart Services:**
   ```bash
   sudo systemctl restart nginx
   sudo systemctl restart php8.2-fpm
   ```

3. **Clear Laravel Caches:**
   ```bash
   cd /var/www/form-monitor
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Check Nginx Configuration:**
   ```bash
   sudo nginx -t
   ```

5. **Check Logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   tail -f /var/www/form-monitor/storage/logs/laravel.log
   ```

## Still Getting 403?

If you're still getting 403 errors after trying these solutions:

1. **Check the specific error** in Nginx error logs
2. **Verify your domain** is pointing to the correct IP
3. **Check if SSL certificate** is properly configured
4. **Ensure the application** is in the correct directory
5. **Test with a simple HTML file** first

Let me know what specific error messages you're seeing in the logs, and I can provide more targeted solutions!
