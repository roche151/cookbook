# Cookbook Application - Production Deployment Guide

This guide walks through deploying the Cookbook Laravel application to a production environment.

## Prerequisites

- **PHP 8.2+** with extensions: bcmath, ctype, curl, dom, fileinfo, filter, hash, json, mbstring, openssl, pdo, tokenizer, xml
- **Composer** (PHP dependency manager)
- **Node.js 18+** and npm (for frontend assets)
- **Web server** (Apache with mod_rewrite, Nginx, or equivalent)
- **MySQL 8.0+** or **PostgreSQL 12+** database
- **Git** (for code deployment)

## 1. Initial Server Setup

### Install PHP and Required Extensions

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.2-cli php8.2-fpm php8.2-mysql php8.2-pgsql \
  php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip

# Check PHP version
php -v
```

### Install Composer and Node.js

```bash
# Composer (global installation)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

## 2. Clone Repository and Install Dependencies

```bash
# Clone the repository
cd /var/www
git clone <repository-url> cookbook
cd cookbook

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install --production

# Build production assets
npm run build
```

## 3. Configure Environment Variables

### Create .env File

Copy `.env.example` to `.env` and configure for production:

```bash
cp .env.example .env
```

### Essential Environment Variables

```env
# Application
APP_NAME=Cookbook
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE  # Generate with: php artisan key:generate
APP_DEBUG=false                         # CRITICAL: Must be false in production
APP_URL=https://yourdomain.com          # Use https in production

# Database - MySQL Example
DB_CONNECTION=mysql
DB_HOST=localhost                       # Or your DB host
DB_PORT=3306
DB_DATABASE=cookbook_db                 # Create this database first
DB_USERNAME=cookbook_user               # Create this user
DB_PASSWORD=your_strong_password        # Use a strong password

# Database - PostgreSQL Example (if using PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=cookbook_db
DB_USERNAME=cookbook_user
DB_PASSWORD=your_strong_password

# Session & Cache
SESSION_DRIVER=database                 # Or redis for better performance
CACHE_STORE=database                    # Or redis
QUEUE_CONNECTION=database               # Or redis/beanstalkd for scaling

# Mail Configuration (SendGrid or Mailgun recommended)
MAIL_MAILER=smtp                        # smtp, mailgun, sendgrid, ses, postmark
MAIL_HOST=smtp.sendgrid.net            # For SendGrid
MAIL_PORT=587
MAIL_USERNAME=apikey                    # Your SendGrid API key
MAIL_PASSWORD=sg_...                    # Your SendGrid password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Cookbook"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error                         # error, warning, notice, info in production

# Additional
BCRYPT_ROUNDS=12
TRUSTED_PROXIES=*                       # If behind a reverse proxy
```

### Generate Application Key

```bash
php artisan key:generate
```

## 4. Database Setup

### Create Database and User

**MySQL:**
```sql
CREATE DATABASE cookbook_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cookbook_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON cookbook_db.* TO 'cookbook_user'@'localhost';
FLUSH PRIVILEGES;
```

**PostgreSQL:**
```sql
CREATE DATABASE cookbook_db;
CREATE USER cookbook_user WITH ENCRYPTED PASSWORD 'your_strong_password';
GRANT ALL PRIVILEGES ON DATABASE cookbook_db TO cookbook_user;
```

### Run Migrations

```bash
php artisan migrate --force
```

### (Optional) Seed Initial Data

```bash
php artisan db:seed --force
```

Creates test user: `test@example.com` / `password` and 12 sample recipes.

## 5. File & Directory Permissions

```bash
# Set proper permissions for Laravel
sudo chown -R www-data:www-data /var/www/cookbook
sudo chmod -R 755 /var/www/cookbook
sudo chmod -R 775 /var/www/cookbook/storage
sudo chmod -R 775 /var/www/cookbook/bootstrap/cache

# If using SELinux
sudo chcon -R -t httpd_sys_rw_content_t /var/www/cookbook/storage
sudo chcon -R -t httpd_sys_rw_content_t /var/www/cookbook/bootstrap/cache
```

## 6. Web Server Configuration

### Nginx Configuration Example

Create `/etc/nginx/sites-available/cookbook`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    root /var/www/cookbook/public;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }

    # Cache busting for versioned assets
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/cookbook /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Apache Configuration Example

Enable required modules:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

The application uses `.htaccess` for routing (included in Laravel).

## 7. SSL Certificate (HTTPS)

Use Let's Encrypt with Certbot for free SSL:

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot certonly --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

## 8. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 9. Queue & Background Jobs (Optional)

If using background jobs beyond simple database queue:

### Using Supervisor for Queue

```bash
sudo apt install supervisor
```

Create `/etc/supervisor/conf.d/cookbook-queue.conf`:

```ini
[program:cookbook-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cookbook/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/cookbook/storage/logs/queue.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cookbook-queue:*
```

## 10. Caching & Redis (Recommended for Performance)

```bash
# Install Redis
sudo apt install redis-server

# Update .env to use Redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Verify Redis is running
redis-cli ping  # Should return PONG
```

## 11. Monitoring & Logging

### Application Logs

Monitor logs in real-time:
```bash
tail -f /var/www/cookbook/storage/logs/laravel.log
```

Configure log rotation:
```bash
sudo nano /etc/logrotate.d/cookbook
```

```
/var/www/cookbook/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Health Monitoring

Add monitoring/health endpoint:
```bash
php artisan down  # Maintenance mode when deploying
php artisan up    # Bring back online
```

## 12. Backup Strategy

### Database Backups

```bash
# Manual backup
mysqldump -u cookbook_user -p cookbook_db > /backups/cookbook_$(date +%Y%m%d).sql

# PostgreSQL backup
pg_dump -U cookbook_user cookbook_db > /backups/cookbook_$(date +%Y%m%d).sql
```

### Automated Backups via Cron

```bash
# Add to crontab
0 2 * * * mysqldump -u cookbook_user -p'password' cookbook_db | gzip > /backups/cookbook_$(date +\%Y\%m\%d).sql.gz
```

## 13. Deployment Checklist

- [ ] Server meets PHP 8.2+ and Node.js requirements
- [ ] Database created and credentials configured
- [ ] .env file configured for production (APP_KEY generated, APP_DEBUG=false)
- [ ] Code cloned and dependencies installed
- [ ] Migrations ran successfully
- [ ] Assets built (`npm run build`)
- [ ] Web server configured (Nginx/Apache)
- [ ] SSL certificate installed and working
- [ ] File permissions set correctly
- [ ] Configuration cached
- [ ] Logs configured and accessible
- [ ] Email service (SendGrid/Mailgun) configured and tested
- [ ] Backup strategy implemented
- [ ] Monitoring in place

## 14. Troubleshooting

### 500 Error & Blank Page
- Check `/var/www/cookbook/storage/logs/laravel.log`
- Verify file permissions on `storage/` and `bootstrap/cache/`
- Ensure `.env` file exists and APP_KEY is set

### Database Connection Error
- Verify database credentials in `.env`
- Test connection: `mysql -h DB_HOST -u DB_USERNAME -p`
- Check PostgreSQL: `psql -h DB_HOST -U DB_USERNAME -d cookbook_db`

### Assets Not Loading
- Verify `npm run build` completed successfully
- Check `public/build/manifest.json` exists
- Clear browser cache

### Queue Not Processing
- Check Supervisor status: `sudo supervisorctl status`
- Monitor logs: `tail -f /var/www/cookbook/storage/logs/queue.log`
- Manually process: `php artisan queue:work`

## Contact & Support

For issues or questions, contact the development team or refer to [Laravel Documentation](https://laravel.com/docs).
