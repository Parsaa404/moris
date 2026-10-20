<?php
/**
 * Moris Website — Configuration
 *
 * Copy this file to config.php and update values for your environment.
 * config.php is ignored by git (never commit real passwords).
 */

declare(strict_types=1);

// Base URL path if site is in a subfolder (e.g. '/moris'). Use '' for domain root.
define('BASE_PATH', '');

// Site
define('SITE_NAME', 'Moris');
define('DEFAULT_LANG', 'en'); // en | fa

// Database
// - Local (MAMP): host 127.0.0.1, port 8889, user root, pass root
// - cPanel: use values from MySQL Databases panel, port is usually 3306 (omit DB_PORT)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); // MAMP: 8889 | cPanel: 3306
define('DB_NAME', 'moris_db');
define('DB_USER', 'moris_user');
define('DB_PASS', 'your_password_here');
define('DB_CHARSET', 'utf8mb4');

// Session name for admin login
define('SESSION_NAME', 'moris_admin_session');

// Timezone (Malaysia)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Development: set true to show PHP errors locally. MUST be false on production.
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
