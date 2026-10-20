<?php
/**
 * Moris — entry point
 * Redirects visitors to the default language home page.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$lang = DEFAULT_LANG;
redirect($lang . '/');
