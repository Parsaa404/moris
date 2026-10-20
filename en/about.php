<?php
/** English — About (content from database). */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$lang = 'en';
$dir = 'ltr';
$slug = 'about';
$activeNav = 'about';
$currentPage = 'about.php';

require dirname(__DIR__) . '/includes/cms_page.php';
