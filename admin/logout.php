<?php
/**
 * Log out and return to the login page.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    logout();
    redirect('admin/login.php');
}

redirect('admin/index.php');
