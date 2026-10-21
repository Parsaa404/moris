#!/usr/bin/env bash
# ==============================================================================
# Moris Website — One-Command Server Update Script
# Usage:
#   sudo moris-update
#   or: bash /home/ubuntu/update.sh
# ==============================================================================

set -euo pipefail

WEB_ROOT="/var/www/moris"
BRANCH="main"

echo "=============================================="
echo "  Updating Moris Website (morispt.com) ..."
echo "=============================================="

# Ensure git recognizes safe directory
sudo git config --global --add safe.directory "$WEB_ROOT" 2>/dev/null || true
git config --global --add safe.directory "$WEB_ROOT" 2>/dev/null || true

cd "$WEB_ROOT"

echo "1. Fetching latest changes from GitHub..."
sudo git fetch origin "$BRANCH"

echo "2. Applying updates (clean reset to origin/$BRANCH)..."
sudo git reset --hard "origin/$BRANCH"

echo "3. Updating file permissions..."
sudo chown -R www-data:www-data "$WEB_ROOT"
sudo find "$WEB_ROOT" -type d -exec chmod 755 {} \;
sudo find "$WEB_ROOT" -type f -exec chmod 644 {} \;
sudo chmod 750 "$WEB_ROOT/includes"
if [[ -f "$WEB_ROOT/includes/config.php" ]]; then
  sudo chmod 640 "$WEB_ROOT/includes/config.php"
fi
sudo mkdir -p "$WEB_ROOT/assets/images/uploads"
sudo chmod -R 775 "$WEB_ROOT/assets/images/uploads"

echo "4. Reloading Nginx & PHP-FPM..."
PHP_FPM_SERVICE="$(systemctl list-units --type=service --state=running | grep -o 'php[0-9.]*-fpm' | head -1 || echo 'php8.5-fpm')"
sudo systemctl reload "$PHP_FPM_SERVICE" 2>/dev/null || sudo systemctl restart "$PHP_FPM_SERVICE"
sudo systemctl reload nginx

echo ""
echo "=============================================="
echo "  Update Complete!"
echo "  Current commit: $(git log -n 1 --oneline)"
echo "  Site: https://morispt.com"
echo "=============================================="
