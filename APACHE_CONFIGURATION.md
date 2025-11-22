# Apache Configuration for Laravel Form Monitor

This guide covers Apache configuration for your Laravel application when using `.htaccess` files.

## 🎯 **The Problem**

Your Apache virtual host is pointing to `/var/www/form-monitor` instead of `/var/www/form-monitor/public`, which is why you need to add `/public` to the URL.

## 🔧 **Quick Fix**

SSH into your VPS and run the fix script:

```bash
ssh root@your-vps-ip
cd /var/www/form-monitor
./fix-apache-htaccess.sh
```

## 🛠️ **Manual Fix**

### **Step 1: Check Current Virtual Host**

```bash
# Find your active site
ls -la /etc/apache2/sites-enabled/

# Check current configuration
cat /etc/apache2/sites-available/your-site.conf
```

### **Step 2: Fix Document Root**

Edit your virtual host configuration:

```bash
sudo nano /etc/apache2/sites-available/your-site.conf
```

Change the `DocumentRoot` from:
```apache
DocumentRoot /var/www/form-monitor
```

To:
```apache
DocumentRoot /var/www/form-monitor/public
```

### **Step 3: Create .htaccess File**

Create the `.htaccess` file in the public directory:

```bash
sudo nano /var/www/form-monitor/public/.htaccess
```

Add this content:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### **Step 4: Enable mod_rewrite**

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

### **Step 5: Test Configuration**

```bash
# Test Apache configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

## 📋 **Complete Virtual Host Configuration**

Here's what your Apache virtual host should look like:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/form-monitor/public

    <Directory /var/www/form-monitor/public>
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/form-monitor>
        AllowOverride None
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## 🔍 **Key Configuration Points**

### **1. Document Root**
```apache
DocumentRoot /var/www/form-monitor/public
```
This is the most important line - it tells Apache where to serve files from.

### **2. Directory Permissions**
```apache
<Directory /var/www/form-monitor/public>
    AllowOverride All
    Require all granted
</Directory>
```
This allows `.htaccess` files to work and grants access to the public directory.

### **3. Security**
```apache
<Directory /var/www/form-monitor>
    AllowOverride None
    Require all denied
</Directory>
```
This prevents access to the main application directory.

## 🚀 **Quick Commands**

### **Check Current Document Root:**
```bash
grep -E "^\s*DocumentRoot\s+" /etc/apache2/sites-available/your-site.conf
```

### **Fix Document Root (One-liner):**
```bash
sudo sed -i 's|DocumentRoot /var/www/form-monitor|DocumentRoot /var/www/form-monitor/public|g' /etc/apache2/sites-available/your-site.conf
```

### **Enable mod_rewrite:**
```bash
sudo a2enmod rewrite && sudo systemctl restart apache2
```

### **Test and Restart:**
```bash
sudo apache2ctl configtest && sudo systemctl restart apache2
```

## 🔧 **Troubleshooting**

### **Common Issues:**

#### **1. mod_rewrite not enabled**
```bash
# Check if mod_rewrite is enabled
apache2ctl -M | grep rewrite

# Enable it if not
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### **2. .htaccess not working**
```bash
# Check if AllowOverride is set
grep -A 5 -B 5 "AllowOverride" /etc/apache2/sites-available/your-site.conf

# Should show: AllowOverride All
```

#### **3. Permission issues**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/form-monitor
sudo chmod -R 755 /var/www/form-monitor
sudo chmod -R 775 /var/www/form-monitor/storage
sudo chmod -R 775 /var/www/form-monitor/bootstrap/cache
```

#### **4. Configuration errors**
```bash
# Test configuration
sudo apache2ctl configtest

# Check error logs
sudo tail -f /var/log/apache2/error.log
```

## 📝 **SSL Configuration (Optional)**

If you want to add SSL later:

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/form-monitor/public

    <Directory /var/www/form-monitor/public>
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/form-monitor>
        AllowOverride None
        Require all denied
    </Directory>

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## ✅ **After Fixing**

Once you fix the Apache configuration:

- ✅ `http://yourdomain.com` will work (no `/public` needed)
- ✅ `http://yourdomain.com/api/public/health` will work
- ✅ All Laravel routes will work properly
- ✅ Static assets (CSS, JS, images) will load correctly

## 🧪 **Test Your Fix**

After fixing, test these URLs:

```bash
# Test main site
curl -I http://yourdomain.com/

# Test API
curl http://yourdomain.com/api/public/health

# Test with a real form
curl -X POST "http://yourdomain.com/api/forms/test" \
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

## 🔄 **Apache vs Nginx**

If you want to switch to Nginx later (recommended for better performance):

1. **Install Nginx:**
   ```bash
   sudo apt update
   sudo apt install nginx
   ```

2. **Stop Apache:**
   ```bash
   sudo systemctl stop apache2
   sudo systemctl disable apache2
   ```

3. **Start Nginx:**
   ```bash
   sudo systemctl start nginx
   sudo systemctl enable nginx
   ```

4. **Use the Nginx fix script:**
   ```bash
   ./fix-nginx-root.sh
   ```

The Apache fix script I created will automatically:
1. ✅ Check your current configuration
2. ✅ Enable mod_rewrite
3. ✅ Fix the document root
4. ✅ Create the .htaccess file
5. ✅ Test the configuration
6. ✅ Restart Apache
7. ✅ Show you the final configuration

Run `./fix-apache-htaccess.sh` and your website should work without needing `/public` in the URL! 🎉
