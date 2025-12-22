#!/bin/bash

# ============================================================
# UKM Band Laravel Deployment Script
# Domain: ukm-band.cyshe.my.id
# Run as root: sudo bash deploy.sh
# ============================================================

set -e

# Configuration - EDIT THESE
DOMAIN="ukm-band.cyshe.my.id"
APP_DIR="/var/www/ukm-band"
DB_NAME="ukm_band"
DB_USER="ukm_band_user"
DB_PASSWORD="$(openssl rand -base64 16)"
APP_KEY=""  # Will be generated
GITHUB_REPO="https://github.com/Ashlxxy/Tubes-Kelompok2-WebProPBO.git"
PHP_VERSION="8.4"
EMAIL="admin@cyshe.my.id"  # For SSL certificate

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() { echo -e "${GREEN}[✓]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[!]${NC} $1"; }
print_error() { echo -e "${RED}[✗]${NC} $1"; }

echo "============================================================"
echo "  UKM Band Laravel Deployment Script"
echo "  Domain: $DOMAIN"
echo "============================================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root: sudo bash deploy.sh"
    exit 1
fi

# ============================================================
# 1. SYSTEM UPDATE
# ============================================================
print_status "Updating system packages..."
apt update && apt upgrade -y

# ============================================================
# 2. INSTALL REQUIRED PACKAGES
# ============================================================
print_status "Installing essential packages..."
apt install -y software-properties-common curl wget git unzip zip

# ============================================================
# 3. INSTALL PHP 8.4
# ============================================================
print_status "Adding PHP repository..."
add-apt-repository -y ppa:ondrej/php
apt update

print_status "Installing PHP $PHP_VERSION and extensions..."
apt install -y php$PHP_VERSION \
    php$PHP_VERSION-fpm \
    php$PHP_VERSION-cli \
    php$PHP_VERSION-mysql \
    php$PHP_VERSION-mbstring \
    php$PHP_VERSION-xml \
    php$PHP_VERSION-curl \
    php$PHP_VERSION-zip \
    php$PHP_VERSION-gd \
    php$PHP_VERSION-bcmath \
    php$PHP_VERSION-intl \
    php$PHP_VERSION-readline \
    php$PHP_VERSION-opcache

# ============================================================
# 4. INSTALL NGINX
# ============================================================
print_status "Installing Nginx..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx

# ============================================================
# 5. INSTALL MYSQL
# ============================================================
print_status "Installing MySQL Server..."
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

# ============================================================
# 6. CREATE DATABASE AND USER
# ============================================================
print_status "Creating database and user..."
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

print_status "Database '$DB_NAME' created with user '$DB_USER'"

# ============================================================
# 7. INSTALL PHPMYADMIN
# ============================================================
print_status "Installing phpMyAdmin..."
DEBIAN_FRONTEND=noninteractive apt install -y phpmyadmin

# Configure phpMyAdmin with Nginx
ln -sf /usr/share/phpmyadmin /var/www/html/phpmyadmin

# ============================================================
# 8. INSTALL COMPOSER
# ============================================================
print_status "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

# ============================================================
# 9. INSTALL NODE.JS (for frontend assets)
# ============================================================
print_status "Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# ============================================================
# 10. CLONE REPOSITORY
# ============================================================
print_status "Cloning repository..."
rm -rf $APP_DIR
mkdir -p $APP_DIR
git clone $GITHUB_REPO $APP_DIR/temp
mv $APP_DIR/temp/tubes-laravel/* $APP_DIR/
mv $APP_DIR/temp/tubes-laravel/.* $APP_DIR/ 2>/dev/null || true
rm -rf $APP_DIR/temp

# ============================================================
# 11. INSTALL LARAVEL DEPENDENCIES
# ============================================================
print_status "Installing Laravel dependencies..."
cd $APP_DIR
composer install --no-interaction --optimize-autoloader --no-dev

# ============================================================
# 12. CONFIGURE ENVIRONMENT
# ============================================================
print_status "Configuring environment..."
cp .env.example .env 2>/dev/null || touch .env

# Generate .env file
cat > .env << EOF
APP_NAME="UKM Band"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://$DOMAIN

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file
CACHE_PREFIX=

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@$DOMAIN"
MAIL_FROM_NAME="\${APP_NAME}"
EOF

# Generate application key
php artisan key:generate --force

# ============================================================
# 13. SET PERMISSIONS
# ============================================================
print_status "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache
chmod -R 775 $APP_DIR/public/uploads 2>/dev/null || mkdir -p $APP_DIR/public/uploads && chmod -R 775 $APP_DIR/public/uploads

# ============================================================
# 14. RUN MIGRATIONS
# ============================================================
print_status "Running database migrations..."
cd $APP_DIR
php artisan migrate --force
php artisan db:seed --force 2>/dev/null || true

# ============================================================
# 15. OPTIMIZE LARAVEL
# ============================================================
print_status "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# ============================================================
# 16. CONFIGURE NGINX
# ============================================================
print_status "Configuring Nginx..."

cat > /etc/nginx/sites-available/$DOMAIN << 'NGINX_CONF'
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;
    root /var/www/ukm-band/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    # Increase max upload size
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/phpPHP_VERSION_PLACEHOLDER-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Audio file handling with proper Range support
    location ~* \.(mp3|wav|ogg|m4a)$ {
        add_header Accept-Ranges bytes;
        add_header Cache-Control "public, max-age=31536000";
    }
    
    # phpMyAdmin
    location /phpmyadmin {
        alias /usr/share/phpmyadmin;
        index index.php;
        
        location ~ ^/phpmyadmin/(.+\.php)$ {
            alias /usr/share/phpmyadmin/$1;
            fastcgi_pass unix:/var/run/php/phpPHP_VERSION_PLACEHOLDER-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }
}
NGINX_CONF

# Replace placeholders
sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /etc/nginx/sites-available/$DOMAIN
sed -i "s/PHP_VERSION_PLACEHOLDER/$PHP_VERSION/g" /etc/nginx/sites-available/$DOMAIN

# Enable site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test and reload Nginx
nginx -t && systemctl reload nginx

# ============================================================
# 17. INSTALL SSL CERTIFICATE
# ============================================================
print_status "Installing Certbot and obtaining SSL certificate..."
apt install -y certbot python3-certbot-nginx

print_warning "Obtaining SSL certificate for $DOMAIN..."
print_warning "Make sure your DNS is pointing to this server before proceeding!"
echo ""
read -p "Is your DNS configured and pointing to this server? (y/n): " dns_ready

if [ "$dns_ready" = "y" ] || [ "$dns_ready" = "Y" ]; then
    certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect
    print_status "SSL certificate installed successfully!"
else
    print_warning "Skipping SSL installation. Run this command later:"
    echo "sudo certbot --nginx -d $DOMAIN --redirect"
fi

# ============================================================
# 18. CONFIGURE FIREWALL
# ============================================================
print_status "Configuring firewall..."
ufw allow 'Nginx Full'
ufw allow OpenSSH
echo "y" | ufw enable

# ============================================================
# 19. CREATE SYSTEMD SERVICE FOR QUEUE WORKER (optional)
# ============================================================
print_status "Creating queue worker service..."
cat > /etc/systemd/system/ukm-band-queue.service << EOF
[Unit]
Description=UKM Band Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php $APP_DIR/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable ukm-band-queue
systemctl start ukm-band-queue

# ============================================================
# DEPLOYMENT COMPLETE
# ============================================================
echo ""
echo "============================================================"
echo -e "${GREEN}  DEPLOYMENT COMPLETE!${NC}"
echo "============================================================"
echo ""
echo "🌐 Website URL: https://$DOMAIN"
echo "🗄️  phpMyAdmin: https://$DOMAIN/phpmyadmin"
echo ""
echo "📋 Database Credentials:"
echo "   Database: $DB_NAME"
echo "   Username: $DB_USER"
echo "   Password: $DB_PASSWORD"
echo ""
echo "📁 App Directory: $APP_DIR"
echo ""
echo "⚠️  IMPORTANT: Save these credentials securely!"
echo ""
echo "============================================================"
echo ""

# Save credentials to file
cat > /root/ukm-band-credentials.txt << EOF
UKM Band Deployment Credentials
================================
Website: https://$DOMAIN
phpMyAdmin: https://$DOMAIN/phpmyadmin

Database:
  Name: $DB_NAME
  User: $DB_USER
  Password: $DB_PASSWORD

App Directory: $APP_DIR
EOF

print_status "Credentials saved to /root/ukm-band-credentials.txt"
