-- Moris Website — MySQL Schema
-- Import via phpMyAdmin on cPanel or: mysql -u user -p moris_db < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Admin users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Site settings (key-value, all editable in admin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- CMS pages (home, about, services — EN + FA)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(50) NOT NULL,
  `title_en` VARCHAR(200) NOT NULL DEFAULT '',
  `title_fa` VARCHAR(200) NOT NULL DEFAULT '',
  `content_en` LONGTEXT NULL,
  `content_fa` LONGTEXT NULL,
  `meta_description_en` VARCHAR(300) NULL,
  `meta_description_fa` VARCHAR(300) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Training packages (exact prices — editable in admin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(150) NOT NULL,
  `name_fa` VARCHAR(150) NOT NULL DEFAULT '',
  `description_en` TEXT NULL,
  `description_fa` TEXT NULL,
  `features_en` TEXT NULL COMMENT 'One feature per line',
  `features_fa` TEXT NULL COMMENT 'One feature per line',
  `price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'MYR' COMMENT 'MYR, USD, EUR',
  `sessions_count` INT UNSIGNED NULL COMMENT 'e.g. 10 sessions',
  `session_duration_min` INT UNSIGNED NULL DEFAULT 60,
  `includes_boxing` TINYINT(1) NOT NULL DEFAULT 0,
  `includes_weights` TINYINT(1) NOT NULL DEFAULT 1,
  `is_online` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Blog categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(100) NOT NULL,
  `name_fa` VARCHAR(100) NOT NULL DEFAULT '',
  `slug` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Blog posts
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NULL,
  `slug` VARCHAR(150) NOT NULL,
  `title_en` VARCHAR(200) NOT NULL,
  `title_fa` VARCHAR(200) NOT NULL DEFAULT '',
  `excerpt_en` TEXT NULL,
  `excerpt_fa` TEXT NULL,
  `content_en` LONGTEXT NULL,
  `content_fa` LONGTEXT NULL,
  `featured_image` VARCHAR(255) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_slug` (`slug`),
  KEY `idx_blog_category` (`category_id`),
  KEY `idx_blog_published` (`is_published`, `published_at`),
  CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Gallery categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(100) NOT NULL,
  `name_fa` VARCHAR(100) NOT NULL DEFAULT '',
  `slug` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gallery_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Gallery images
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NULL,
  `title_en` VARCHAR(150) NOT NULL DEFAULT '',
  `title_fa` VARCHAR(150) NOT NULL DEFAULT '',
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_category` (`category_id`),
  KEY `idx_gallery_sort` (`sort_order`),
  CONSTRAINT `fk_gallery_category` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Contact form messages
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `goal` VARCHAR(200) NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_read` (`is_read`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Homepage sections (order & visibility — live + draft)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(50) NOT NULL,
  `name_en` VARCHAR(100) NOT NULL,
  `name_fa` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `draft_sort_order` INT NOT NULL DEFAULT 0,
  `is_draft_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_homepage_section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Homepage content (bilingual fields & JSON — live + draft)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `homepage_content` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(50) NOT NULL,
  `content_key` VARCHAR(100) NOT NULL,
  `value_en` MEDIUMTEXT NULL,
  `value_fa` MEDIUMTEXT NULL,
  `draft_value_en` MEDIUMTEXT NULL,
  `draft_value_fa` MEDIUMTEXT NULL,
  `content_type` VARCHAR(30) NOT NULL DEFAULT 'text',
  `label` VARCHAR(150) NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_section_content` (`section_key`, `content_key`),
  KEY `idx_section` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Demo / placeholder data (idempotent — safe to re-run)
-- --------------------------------------------------------

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Moris'),
('tagline_en', 'Personal Training · Boxing · Fat Loss'),
('tagline_fa', 'تمرین شخصی · بوکس · کاهش چربی'),
('contact_email', 'hello@moris.example'),
('contact_phone', '+60'),
('contact_whatsapp', '+60'),
('instagram_url', ''),
('youtube_url', ''),
('default_language', 'en');

INSERT IGNORE INTO `pages` (`slug`, `title_en`, `title_fa`, `content_en`, `content_fa`) VALUES
('home', 'Transform Your Body', 'بدنت را متحول کن',
 '<p>Personal training in Kuala Lumpur. Weight training, conditioning, and boxing — tailored to your goals.</p>',
 '<p>تمرین شخصی در کوالالامپور. تمرینات وزنه، آمادگی جسمانی و بوکس — متناسب با اهداف شما.</p>'),
('about', 'About Moris', 'درباره موریس',
 '<p>13+ years of coaching experience. Boxer, fitness athlete, and personal trainer based in KL.</p>',
 '<p>بیش از ۱۳ سال تجربه مربیگری. بوکسور، ورزشکار فیتنس و مربی شخصی در کوالالامپور.</p>'),
('services', 'Services', 'خدمات',
 '<p>One-on-one personal training, boxing coaching, and online programming for clients worldwide.</p>',
 '<p>تمرین شخصی یک به یک، مربیگری بوکس و برنامه‌ریزی آنلاین برای مشتریان در سراسر جهان.</p>');

INSERT IGNORE INTO `packages` (`id`, `name_en`, `name_fa`, `description_en`, `price`, `currency`, `sessions_count`, `includes_boxing`, `includes_weights`, `sort_order`) VALUES
(1, '10 Sessions — Weight Training', '۱۰ جلسه — تمرین وزنه',
 'One-on-one weight training in KL. Edit price and details in admin.', 1500.00, 'MYR', 10, 0, 1, 1),
(2, '10 Sessions — Boxing', '۱۰ جلسه — بوکس',
 'One-on-one boxing coaching. Edit price and details in admin.', 1500.00, 'MYR', 10, 1, 0, 2),
(3, '10 Sessions — Weight + Boxing', '۱۰ جلسه — وزنه + بوکس',
 'Combined program. Edit price and details in admin.', 2000.00, 'MYR', 10, 1, 1, 3);

INSERT IGNORE INTO `blog_categories` (`name_en`, `name_fa`, `slug`, `sort_order`) VALUES
('Client Stories', 'داستان مشتریان', 'client-stories', 1),
('Training Tips', 'نکات تمرینی', 'training-tips', 2);

INSERT IGNORE INTO `gallery_categories` (`name_en`, `name_fa`, `slug`, `sort_order`) VALUES
('Training', 'تمرین', 'training', 1),
('Boxing', 'بوکس', 'boxing', 2);

INSERT IGNORE INTO `homepage_sections` (`section_key`, `name_en`, `name_fa`, `sort_order`, `is_active`, `draft_sort_order`, `is_draft_active`) VALUES
('hero',     'Hero Section (EARN IT)',                        'بخش هیرو (به دستش بیار)',       1, 1, 1, 1),
('basics',   'Intro / Back to Basics',                        'مقدمه و اصول بنیادین',          2, 1, 2, 1),
('sparring', 'Illustrated Content / Where Do You Want To Be', 'بخش تصویری و تمرینات',          3, 1, 3, 1),
('booking',  'Consultation & Chat Booking',                   'رزرو مشاوره و چت مستقیم',       4, 1, 4, 1),
('bio',      'About Coach Mori',                              'درباره مربی موری',              5, 1, 5, 1),
('gallery',  'Gallery & Results Showcase',                    'گالری و نتایج شاگردان',         6, 1, 6, 1);

-- Admin user is NOT seeded here.
-- After import, open /admin/install.php once to create your admin account (Step 2).
