#!/usr/bin/env bash
set -e

echo "=== 1. Setting up MySQL ==="
sudo mysql << 'SQL'
CREATE DATABASE IF NOT EXISTS moris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'moris_user'@'localhost' IDENTIFIED BY 'MoriDb_7x9qZ_p2026kLmW';
ALTER USER 'moris_user'@'localhost' IDENTIFIED BY 'MoriDb_7x9qZ_p2026kLmW';
GRANT ALL PRIVILEGES ON moris_db.* TO 'moris_user'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "=== 2. Importing Schema ==="
sudo mysql moris_db < /var/www/moris/sql/schema.sql

echo "=== 3. Writing includes/config.php ==="
cat << 'CFG' | sudo tee /var/www/moris/includes/config.php > /dev/null
<?php
/**
 * Moris Website — Configuration
 */

declare(strict_types=1);

define('BASE_PATH', '');
define('SITE_NAME', 'Moris');
define('DEFAULT_LANG', 'en');

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'moris_db');
define('DB_USER', 'moris_user');
define('DB_PASS', 'MoriDb_7x9qZ_p2026kLmW');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_NAME', 'moris_admin_session');
date_default_timezone_set('Asia/Kuala_Lumpur');

define('DEBUG_MODE', false);
error_reporting(0);
ini_set('display_errors', '0');
CFG

echo "=== 4. Configuring Nginx ==="
PHP_SOCK=$(ls /run/php/php*-fpm.sock 2>/dev/null | sort -V | tail -1)
echo "Using PHP Socket: $PHP_SOCK"

cat << NGINX | sudo tee /etc/nginx/sites-available/moris > /dev/null
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;

    root /var/www/moris;
    index index.php index.html;

    client_max_body_size 16M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_SOCK;
    }

    location ~* ^/(includes|sql|vendor)/ {
        deny all;
    }

    location ~ /\. {
        deny all;
    }

    location ~* \.(md|sql|env|git)$ {
        deny all;
    }
}
NGINX

sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/moris /etc/nginx/sites-enabled/moris
sudo nginx -t
sudo systemctl reload nginx

echo "=== 5. Setting file permissions ==="
sudo rm -f /var/www/moris/includes/.installed
sudo mkdir -p /var/www/moris/assets/images/uploads
sudo chown -R www-data:www-data /var/www/moris
sudo find /var/www/moris -type d -exec chmod 755 {} \;
sudo find /var/www/moris -type f -exec chmod 644 {} \;
sudo chmod 750 /var/www/moris/includes
sudo chmod 640 /var/www/moris/includes/config.php
sudo chmod -R 775 /var/www/moris/assets/images/uploads

echo "=== 6. Verifying Services ==="
curl -sI http://127.0.0.1/en/ | head -n 5
echo "=== Setup Completed Successfully! ==="
