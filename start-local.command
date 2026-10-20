#!/bin/bash
# Double-click this file to run your Moris website on your Mac.
cd "$(dirname "$0")" || exit 1

PHP="/Applications/MAMP/bin/php/php8.3.30/bin/php"
if [[ ! -x "$PHP" ]]; then
  PHP=$(ls /Applications/MAMP/bin/php/*/bin/php 2>/dev/null | tail -1)
fi
if [[ ! -x "$PHP" ]]; then
  echo "ERROR: MAMP PHP not found. Install MAMP from https://www.mamp.info/"
  read -r -p "Press Enter to close..."
  exit 1
fi

if [[ ! -f "fa/index.php" ]]; then
  echo "ERROR: Wrong folder — fa/index.php missing."
  echo "Run this from: Moris Website on Desktop"
  read -r -p "Press Enter to close..."
  exit 1
fi

# Stop another app using port 8000 (often causes "Not Found" for /fa/ and /admin/)
if lsof -iTCP:8000 -sTCP:LISTEN >/dev/null 2>&1; then
  echo "Closing old server on port 8000..."
  lsof -tiTCP:8000 -sTCP:LISTEN | xargs kill 2>/dev/null || true
  sleep 1
fi

echo "=============================================="
echo "  Moris website — local links"
echo "=============================================="
echo ""
echo "  PUBLIC (English):  http://localhost:8000/en/"
echo "  PUBLIC (Persian):   http://localhost:8000/fa/"
echo "  ADMIN LOGIN:        http://localhost:8000/admin/login.php"
echo ""
echo "  (There is no visitor login — only admin login.)"
echo ""
echo "  1) Open MAMP → Start Servers (for database)"
echo "  2) Keep THIS window open while using the site"
echo "  3) On iPhone/iPad: tap the ☰ menu to see Home, About, etc."
echo ""
echo "=============================================="
echo ""

open "http://localhost:8000/en/" 2>/dev/null || true
exec "$PHP" -S localhost:8000 router.php
