<?php
/**
 * Moris — entry point
 * Redirects visitors to the default language home page.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$lang = setting('default_language', DEFAULT_LANG) === 'fa' ? 'fa' : 'en';
redirect($lang . '/');
