#!/usr/bin/env bash
#
# Moris website — Ubuntu 22.04 / 24.04 automated install
# Run ON THE SERVER as root:
#   cd /var/www/moris && sudo bash deploy/ubuntu-install.sh
#
# Config: copy deploy/ubuntu-env.example → deploy/ubuntu-env and edit.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

if [[ -f "$SCRIPT_DIR/ubuntu-env" ]]; then
  # shellcheck disable=SC1091
  set -a
  source "$SCRIPT_DIR/ubuntu-env"
  set +a
fi

MORIS_DOMAIN="${MORIS_DOMAIN:-}"
MORIS_WEB_ROOT="${MORIS_WEB_ROOT:-/var/www/moris}"
MORIS_DB_NAME="${MORIS_DB_NAME:-moris_db}"
MORIS_DB_USER="${MORIS_DB_USER:-moris_user}"
MORIS_DB_PASS="${MORIS_DB_PASS:-}"
MORIS_DB_HOST="${MORIS_DB_HOST:-localhost}"
MORIS_DB_PORT="${MORIS_DB_PORT:-3306}"
MORIS_CERTBOT_EMAIL="${MORIS_CERTBOT_EMAIL:-}"
MORIS_RUN_SSL="${MORIS_RUN_SSL:-0}"

log() { echo "[morris-deploy] $*"; }
die() { echo "[morris-deploy] ERROR: $*" >&2; exit 1; }

[[ "${EUID:-$(id -u)}" -eq 0 ]] || die "Run as root: sudo bash deploy/ubuntu-install.sh"
[[ -n "$MORIS_DOMAIN" ]] || die "Set MORIS_DOMAIN in deploy/ubuntu-env"
[[ -n "$MORIS_DB_PASS" ]] || die "Set MORIS_DB_PASS in deploy/ubuntu-env"
[[ -f "$PROJECT_ROOT/sql/schema.sql" ]] || die "Run from project folder (missing sql/schema.sql)"

log "Installing packages (nginx, mysql, php-fpm)..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq nginx mysql-server \
  php-fpm php-mysql php-mbstring php-xml php-curl php-gd php-fileinfo \
  certbot python3-certbot-nginx rsync

log "Publishing site to ${MORIS_WEB_ROOT} ..."
mkdir -p "$MORIS_WEB_ROOT"
rsync -a \
  --exclude 'includes/config.php' \
  --exclude 'includes/.installed' \
  --exclude 'assets/images/uploads/*' \
  --exclude '.git/' \
  --exclude 'deploy/ubuntu-env' \
  "$PROJECT_ROOT/" "$MORIS_WEB_ROOT/"

mkdir -p "$MORIS_WEB_ROOT/assets/images/uploads"
touch "$MORIS_WEB_ROOT/assets/images/uploads/.gitkeep"

log "MySQL: database and user..."
mysql --protocol=socket -e "CREATE DATABASE IF NOT EXISTS \`${MORIS_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if mysql --protocol=socket -N -e "SELECT COUNT(*) FROM mysql.user WHERE user='${MORIS_DB_USER}' AND host='localhost';" | grep -q '^1$'; then
  mysql --protocol=socket -e "ALTER USER '${MORIS_DB_USER}'@'localhost' IDENTIFIED BY '${MORIS_DB_PASS}';"
else
  mysql --protocol=socket -e "CREATE USER '${MORIS_DB_USER}'@'localhost' IDENTIFIED BY '${MORIS_DB_PASS}';"
fi
mysql --protocol=socket -e "GRANT ALL PRIVILEGES ON \`${MORIS_DB_NAME}\`.* TO '${MORIS_DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

TABLE_COUNT="$(mysql --protocol=socket -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${MORIS_DB_NAME}';")"
if [[ "${TABLE_COUNT:-0}" -eq 0 ]]; then
  log "Importing schema..."
  mysql --protocol=socket "$MORIS_DB_NAME" < "$MORIS_WEB_ROOT/sql/schema.sql"
else
  log "Schema already present — skip import."
fi

log "Writing includes/config.php ..."
export MORIS_WEB_ROOT MORIS_DB_HOST MORIS_DB_PORT MORIS_DB_NAME MORIS_DB_USER MORIS_DB_PASS
php <<'PHP'
<?php
declare(strict_types=1);
$root = getenv('MORIS_WEB_ROOT') ?: '/var/www/moris';
$cfg = $root . '/includes/config.php';
$lines = [
    '<?php',
    'declare(strict_types=1);',
    '',
    "define('BASE_PATH', '');",
    "define('SITE_NAME', 'Moris');",
    "define('DEFAULT_LANG', 'en');",
    '',
    'define(' . "'DB_HOST', " . var_export(getenv('MORIS_DB_HOST') ?: 'localhost', true) . ');',
    'define(' . "'DB_PORT', " . var_export(getenv('MORIS_DB_PORT') ?: '3306', true) . ');',
    'define(' . "'DB_NAME', " . var_export(getenv('MORIS_DB_NAME') ?: 'moris_db', true) . ');',
    'define(' . "'DB_USER', " . var_export(getenv('MORIS_DB_USER') ?: 'moris_user', true) . ');',
    'define(' . "'DB_PASS', " . var_export(getenv('MORIS_DB_PASS') ?: '', true) . ');',
    "define('DB_CHARSET', 'utf8mb4');",
    '',
    "define('SESSION_NAME', 'moris_admin_session');",
    '',
    "date_default_timezone_set('Asia/Kuala_Lumpur');",
    '',
    "define('DEBUG_MODE', false);",
    'error_reporting(0);',
    "ini_set('display_errors', '0');",
    '',
];
file_put_contents($cfg, implode("\n", $lines) . "\n");
chmod(0640, $cfg);
PHP

PHP_SOCK="$(ls /run/php/php*-fpm.sock 2>/dev/null | sort -V | tail -1)"
[[ -n "$PHP_SOCK" ]] || die "PHP-FPM socket not found under /run/php/"

log "Nginx site for ${MORIS_DOMAIN} (PHP: ${PHP_SOCK}) ..."
NGINX_SITE="/etc/nginx/sites-available/moris"
sed -e "s/YOUR_DOMAIN/${MORIS_DOMAIN}/g" \
    -e "s|unix:/run/php/php8.3-fpm.sock|unix:${PHP_SOCK}|" \
    "$MORIS_WEB_ROOT/deploy/nginx-moris.conf.example" > "$NGINX_SITE"

ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/moris
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx php*-fpm mysql >/dev/null 2>&1 || true
systemctl restart php*-fpm nginx mysql

log "Permissions for uploads..."
chown -R www-data:www-data "$MORIS_WEB_ROOT"
find "$MORIS_WEB_ROOT" -type d -exec chmod 755 {} \;
find "$MORIS_WEB_ROOT" -type f -exec chmod 644 {} \;
chmod 750 "$MORIS_WEB_ROOT/includes"
chmod 640 "$MORIS_WEB_ROOT/includes/config.php"
chmod -R 775 "$MORIS_WEB_ROOT/assets/images/uploads"

CREDS="/root/moris-deploy-credentials.txt"
{
  echo "Moris deploy — $(date -Iseconds)"
  echo "Domain: ${MORIS_DOMAIN}"
  echo "Web root: ${MORIS_WEB_ROOT}"
  echo "DB name: ${MORIS_DB_NAME}"
  echo "DB user: ${MORIS_DB_USER}"
  echo "DB pass: ${MORIS_DB_PASS}"
  echo ""
  echo "Public EN:  http://${MORIS_DOMAIN}/en/"
  echo "Public FA:  http://${MORIS_DOMAIN}/fa/"
  echo "Admin:      http://${MORIS_DOMAIN}/admin/install.php  (first time only)"
  echo "Admin login: http://${MORIS_DOMAIN}/admin/login.php"
} > "$CREDS"
chmod 600 "$CREDS"

if [[ "$MORIS_RUN_SSL" == "1" && -n "$MORIS_CERTBOT_EMAIL" ]]; then
  log "SSL via Certbot..."
  certbot --nginx -d "$MORIS_DOMAIN" -d "www.${MORIS_DOMAIN}" \
    --non-interactive --agree-tos -m "$MORIS_CERTBOT_EMAIL" --redirect || \
    log "Certbot failed — point DNS to this server first, then run certbot manually."
fi

log "Done."
echo ""
echo "=============================================="
echo "  Website (English): http://${MORIS_DOMAIN}/en/"
echo "  Website (Persian): http://${MORIS_DOMAIN}/fa/"
echo "  Admin setup:       http://${MORIS_DOMAIN}/admin/install.php"
echo "  (Create admin once, then DELETE admin/install.php)"
echo "=============================================="
echo "Credentials saved: ${CREDS}"
