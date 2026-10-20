<?php
/**
 * Log out and return to the login page.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

logout();
redirect('admin/login.php');
