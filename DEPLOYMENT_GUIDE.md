# OnlineFood System - Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying the enhanced OnlineFood system with delivery tracking, M-Pesa integration, and security features.

## Prerequisites

### Server Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Web Server**: Apache 2.4+ or Nginx
- **SSL Certificate**: Required for production
- **Extensions**: 
  - cURL
  - OpenSSL
  - JSON
  - PDO
  - GD (for image processing)

### Domain & SSL
- Domain name pointing to your server
- SSL certificate (Let's Encrypt recommended)
- HTTPS enabled

## Installation Steps

### 1. Server Setup

#### For Apache:
```bash
# Enable required modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

#### For Nginx:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    root /var/www/html/Classic;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
}
```

### 2. Database Setup

```sql
-- Create database
CREATE DATABASE onlinefoodphp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with secure credentials)
CREATE USER 'onlinefood_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON onlinefoodphp.* TO 'onlinefood_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Application Deployment

```bash
# Clone or upload files to server
cd /var/www/html/
git clone https://github.com/your-repo/Classic.git
cd Classic

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/Classic
sudo chmod -R 755 /var/www/html/Classic
sudo chmod -R 777 /var/www/html/Classic/logs
sudo chmod -R 777 /var/www/html/Classic/images
```

### 4. Database Import

```bash
# Import the enhanced database schema
mysql -u onlinefood_user -p onlinefoodphp < "DATABASE FILE/onlinefoodphp_enhanced.sql"
```

### 5. Configuration

#### Environment Variables
Create a `.env` file in the root directory:

```env
# Database
DB_HOST=localhost
DB_USER=onlinefood_user
DB_PASS=your_secure_password
DB_NAME=onlinefoodphp

# Security
ENCRYPTION_KEY=your-32-character-encryption-key
SESSION_SECRET=your-session-secret-key

# M-Pesa (Get from Safaricom Developer Portal)
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_PASSKEY=your_passkey
MPESA_ENVIRONMENT=sandbox

# Google Maps
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

# Application
APP_URL=https://yourdomain.com
APP_ENV=production
```

#### Update Database Connection
Edit `connection/config.php`:

```php
<?php
// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'onlinefoodphp');
?>
```

### 6. M-Pesa Integration Setup

#### Get M-Pesa API Credentials
1. Register at [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Create an app to get Consumer Key and Secret
3. Generate Passkey from your app settings
4. Update the `.env` file with your credentials

#### Test M-Pesa Integration
```bash
# Test the callback URL
curl -X POST https://yourdomain.com/mpesa_callback.php \
  -H "Content-Type: application/json" \
  -d '{"ResultCode":"0","CheckoutRequestID":"test123"}'
```

### 7. Google Maps Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Maps JavaScript API
4. Create API key with restrictions:
   - HTTP referrers: yourdomain.com/*
   - API restrictions: Maps JavaScript API only
5. Update the API key in your `.env` file

### 8. Security Configuration

#### File Permissions
```bash
# Secure sensitive files
sudo chmod 600 /var/www/html/Classic/.env
sudo chmod 600 /var/www/html/Classic/connection/config.php

# Create logs directory
sudo mkdir -p /var/www/html/Classic/logs
sudo chown www-data:www-data /var/www/html/Classic/logs
sudo chmod 755 /var/www/html/Classic/logs
```

#### Firewall Setup
```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# iptables (CentOS/RHEL)
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -j DROP
```

### 9. Driver Interface Setup

#### Create Driver Accounts
```sql
-- Add drivers to the database
INSERT INTO drivers (name, phone, email, vehicle_number, vehicle_type, status) VALUES
('John Driver', '+254700123456', 'john@yourdomain.com', 'KCA 123A', 'Motorcycle', 'available'),
('Jane Rider', '+254700789012', 'jane@yourdomain.com', 'KCB 456B', 'Motorcycle', 'available');
```

#### Driver Login
Create `driver/login.php`:

```php
<?php
include("../connection/connect.php");
include("../includes/security.php");

if ($_POST) {
    $phone = Security::sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM drivers WHERE phone = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $driver = $stmt->get_result()->fetch_assoc();
    
    if ($driver && password_verify($password, $driver['password'])) {
        $_SESSION['driver_id'] = $driver['id'];
        header('location: dashboard.php');
    } else {
        $error = "Invalid credentials";
    }
}
?>
```

### 10. Testing

#### Test Checklist
- [ ] Database connection works
- [ ] User registration/login
- [ ] Restaurant and menu display
- [ ] Cart functionality
- [ ] Checkout with location tracking
- [ ] Delivery fee calculation
- [ ] M-Pesa payment integration
- [ ] Order tracking
- [ ] Driver interface
- [ ] Real-time status updates

#### Performance Testing
```bash
# Test with Apache Bench
ab -n 1000 -c 10 https://yourdomain.com/

# Monitor server resources
htop
iotop
```

## Maintenance

### Regular Tasks
1. **Database Backups**
   ```bash
   # Daily backup script
   mysqldump -u onlinefood_user -p onlinefoodphp > backup_$(date +%Y%m%d).sql
   ```

2. **Log Rotation**
   ```bash
   # Add to /etc/logrotate.d/onlinefood
   /var/www/html/Classic/logs/*.log {
       daily
       missingok
       rotate 30
       compress
       notifempty
       create 644 www-data www-data
   }
   ```

3. **Security Updates**
   ```bash
   # Update system packages
   sudo apt update && sudo apt upgrade
   
   # Monitor security logs
   tail -f /var/www/html/Classic/logs/security.log
   ```

### Monitoring
- Set up monitoring for disk space, memory, and CPU usage
- Monitor error logs for issues
- Set up alerts for failed payments or delivery issues

## Troubleshooting

### Common Issues

1. **M-Pesa Callback Not Working**
   - Check SSL certificate
   - Verify callback URL is accessible
   - Check server logs for errors

2. **Google Maps Not Loading**
   - Verify API key is correct
   - Check domain restrictions
   - Ensure HTTPS is enabled

3. **Location Tracking Issues**
   - Check browser geolocation permissions
   - Verify Google Maps API key
   - Test on HTTPS only

4. **Database Connection Errors**
   - Verify database credentials
   - Check MySQL service status
   - Ensure proper permissions

### Support
For technical support, check:
- Application logs: `/var/www/html/Classic/logs/`
- Server logs: `/var/log/apache2/` or `/var/log/nginx/`
- Database logs: `/var/log/mysql/`

## Security Best Practices

1. **Regular Updates**: Keep PHP, MySQL, and server software updated
2. **Backup Strategy**: Implement automated daily backups
3. **Monitoring**: Set up intrusion detection and monitoring
4. **Access Control**: Use strong passwords and limit admin access
5. **SSL/TLS**: Always use HTTPS in production
6. **Rate Limiting**: Implement rate limiting for API endpoints
7. **Input Validation**: Always validate and sanitize user input
8. **Error Handling**: Don't expose sensitive information in error messages

## Performance Optimization

1. **Caching**: Implement Redis or Memcached for session storage
2. **CDN**: Use a CDN for static assets
3. **Database**: Optimize queries and add proper indexes
4. **Images**: Compress and optimize images
5. **Minification**: Minify CSS and JavaScript files

This deployment guide ensures your OnlineFood system is secure, scalable, and ready for production use. 