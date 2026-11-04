<?php
/**
 * Dynamic Homepage CMS Helper Library
 * Manages homepage sections, content, bilingual values, draft/published workflows,
 * automatic schema migration/seeding, URL/path sanitization, and media library.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Sanitize a CMS URL or link (allows relative paths, #anchors, http://, https://, mailto:, tel:).
 * Blocks javascript:, data:, vbscript:, and control characters to prevent XSS.
 */
function sanitize_cms_url(string $url): string
{
    $url = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $url) ?? '');
    if ($url === '') {
        return '';
    }
    if (preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $url)) {
        return '#';
    }
    return $url;
}

/**
 * Sanitize an image path stored in the CMS.
 * Rejects path traversal (..), null bytes, and dangerous URI schemes.
 */
function sanitize_image_path(string $path): string
{
    $path = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $path) ?? '');
    if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
        return '';
    }
    if (preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $path)) {
        return '';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return filter_var($path, FILTER_VALIDATE_URL) ? $path : '';
    }
    $path = ltrim($path, '/');
    if (!str_starts_with($path, 'assets/images/')) {
        return '';
    }
    return $path;
}

/**
 * Ensure `homepage_sections` and `homepage_content` tables exist and are seeded.
 * Runs automatically once if tables are missing on a newly deployed or upgraded server.
 */
function ensure_homepage_cms_tables(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $pdo = db();
        $pdo->exec("
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
        ");

        $pdo->exec("
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
        ");

        $secCount = (int) $pdo->query("SELECT COUNT(*) FROM homepage_sections")->fetchColumn();
        if ($secCount === 0) {
            $sections = [
                ['hero',     'Hero Section (EARN IT)',                        'بخش هیرو (به دستش بیار)',       1, 1],
                ['basics',   'Intro / Back to Basics',                        'مقدمه و اصول بنیادین',          2, 1],
                ['sparring', 'Illustrated Content / Where Do You Want To Be', 'بخش تصویری و تمرینات',          3, 1],
                ['booking',  'Consultation & Chat Booking',                   'رزرو مشاوره و چت مستقیم',       4, 1],
                ['bio',      'About Coach Mori',                              'درباره مربی موری',              5, 1],
                ['gallery',  'Gallery & Results Showcase',                    'گالری و نتایج شاگردان',         6, 1],
            ];
            $stmtSec = $pdo->prepare("
                INSERT IGNORE INTO homepage_sections
                    (section_key, name_en, name_fa, sort_order, is_active, draft_sort_order, is_draft_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($sections as $s) {
                $stmtSec->execute([$s[0], $s[1], $s[2], $s[3], $s[4], $s[3], $s[4]]);
            }
        }

        $contentCount = (int) $pdo->query("SELECT COUNT(*) FROM homepage_content")->fetchColumn();
        if ($contentCount === 0) {
            seed_default_homepage_content($pdo);
        }
    } catch (Throwable) {
        // Silently allow fallback defaults if DB is unreachable
    }
}

/**
 * Seed default bilingual content into `homepage_content` when empty.
 */
function seed_default_homepage_content(PDO $pdo): void
{
    $heroHighlights = [
        [
            'id' => 1,
            'icon' => 'award',
            'title_en' => '13+ Years in the Ring',
            'title_fa' => '۱۳+ سال تجربه در رینگ',
            'desc_en' => 'Championship coaching & competitive experience',
            'desc_fa' => 'مربی‌گری قهرمانی و سابقه مسابقات حرفه‌ای',
            'link' => '#bio',
        ],
        [
            'id' => 2,
            'icon' => 'dumbbell',
            'title_en' => 'Personal Training & Boxing',
            'title_fa' => 'تمرین خصوصی و بوکس',
            'desc_en' => 'Tailored 1-on-1 strength & conditioning',
            'desc_fa' => 'برنامه‌ریزی دقیق و شخصی‌سازی شده قدرت و آمادگی جسمانی',
            'link' => '#sparring',
        ],
        [
            'id' => 3,
            'icon' => 'map-pin',
            'title_en' => 'Kuala Lumpur & Online',
            'title_fa' => 'کوالالامپور و آنلاین',
            'desc_en' => 'Weight training, fat loss & athletic programming',
            'desc_fa' => 'تمرینات با وزنه، چربی‌سوزی و برنامه‌ریزی ورزشی',
            'link' => '#consultation',
        ],
    ];

    $chatCards = [
        [
            'id' => 1,
            'type' => 'phone',
            'title_en' => 'Phone or Call',
            'title_fa' => 'تماس تلفنی مستقیم',
            'val_en' => '+60 12-345 6789',
            'val_fa' => '+60 12-345 6789',
            'btn_en' => 'Call Now',
            'btn_fa' => 'تماس بگیرید',
            'link' => 'tel:+60123456789',
            'icon' => 'phone',
        ],
        [
            'id' => 2,
            'type' => 'email',
            'title_en' => 'Send an Email',
            'title_fa' => 'ارسال ایمیل',
            'val_en' => 'hello@moris.example',
            'val_fa' => 'hello@moris.example',
            'btn_en' => 'Send Email',
            'btn_fa' => 'ارسال ایمیل',
            'link' => 'mailto:hello@moris.example',
            'icon' => 'mail',
        ],
        [
            'id' => 3,
            'type' => 'whatsapp',
            'title_en' => 'WhatsApp Chat',
            'title_fa' => 'چت واتساپ',
            'val_en' => 'Instant direct reply',
            'val_fa' => 'پاسخ سریع و مستقیم',
            'btn_en' => 'Message',
            'btn_fa' => 'ارسال پیام',
            'link' => 'https://wa.me/60123456789',
            'icon' => 'whatsapp',
        ],
    ];

    $bioQuotes = [
        [
            'id' => 1,
            'quote_en' => 'Professional and utilizes a scientific approach to training.',
            'quote_fa' => 'رویکردی کاملاً حرفه‌ای و علمی به تناسب اندام و تمرین دارد.',
            'author_en' => 'Henry James — Executive & Boxer',
            'author_fa' => 'هنری جیمز — مدیر و بوکسور',
        ],
        [
            'id' => 2,
            'quote_en' => 'In 6 months my power and agility skyrocketed without any joint pain.',
            'quote_fa' => 'در عرض ۶ ماه قدرت و چابکی من بدون هیچ دردی در مفاصل چند برابر شد.',
            'author_en' => 'Farhad K. — Software Architect',
            'author_fa' => 'فرهاد ک. — مهندس نرم‌افزار',
        ],
        [
            'id' => 3,
            'quote_en' => 'Clear instructions, dedicated attention, and actual results.',
            'quote_fa' => 'توضیحات بسیار شفاف، توجه اختصاصی در طول تمرین و دستیابی به نتایج واقعی.',
            'author_en' => 'Sarah M. — Triathlete',
            'author_fa' => 'سارا م. — ورزشکار سه‌گانه',
        ],
    ];

    $galleryItems = [
        [
            'id' => 1,
            'image' => 'assets/images/gallery-sara.jpg',
            'name_en' => 'Sara',
            'name_fa' => 'سارا',
            'tag_en' => 'Boxing & Conditioning',
            'tag_fa' => 'بوکس و آمادگی جسمانی',
            'quote_en' => 'Boxing training with Mori completely transformed my stamina, reaction speed, and mental focus. His technique-first methodology makes every single round challenging and rewarding.',
            'quote_fa' => 'تمرینات بوکس با موری استقامت، سرعت واکنش و تمرکز ذهنی من را به کلی دگرگون کرد. متدولوژی تکنیک‌محور او باعث می‌شود هر راند پرچالش و لذت‌بخش باشد.',
        ],
        [
            'id' => 2,
            'image' => 'assets/images/gallery-megan.jpg',
            'name_en' => 'Megan',
            'name_fa' => 'مگان',
            'tag_en' => 'Fat Loss & Strength',
            'tag_fa' => 'کاهش چربی و افزایش قدرت',
            'quote_en' => 'Lost 11kg and gained incredible core and back definition. Mori designed a structured strength regimen that worked seamlessly with my demanding schedule. Best trainer in KL.',
            'quote_fa' => '۱۱ کیلوگرم وزن کم کردم و عضلات شکم و پشتم تفکیک فوق‌العاده‌ای پیدا کرد. موری یک برنامه قدرتی منظم تنظیم کرد که کاملاً با مشغله کاری من هماهنگ بود. بهترین مربی در کوالالامپور.',
        ],
    ];

    $items = [
        ['hero', 'headline', 'EARN IT.', 'به دستش بیار.', 'text', 'Main Headline'],
        ['hero', 'tagline_1', 'Your Confidence,', 'اعتمادبه‌نفست،', 'text', 'Tagline Line 1'],
        ['hero', 'tagline_2', 'Your Strength,', 'قدرتت،', 'text', 'Tagline Line 2'],
        ['hero', 'tagline_3', 'Your Walk.', 'مسیرت.', 'text', 'Tagline Line 3'],
        ['hero', 'bg_image', 'assets/images/hero-earn-it.webp', 'assets/images/hero-earn-it.webp', 'image', 'Background Image (Desktop LTR)'],
        ['hero', 'bg_image_rtl', 'assets/images/hero-earn-it-rtl.webp', 'assets/images/hero-earn-it-rtl.webp', 'image', 'Background Image (Desktop RTL / Persian)'],
        ['hero', 'highlights', json_encode($heroHighlights, JSON_UNESCAPED_UNICODE), json_encode($heroHighlights, JSON_UNESCAPED_UNICODE), 'json', 'Bottom Highlights Cards'],

        ['basics', 'eyebrow', 'EFT · Philosophy & Foundations', 'EFT · فلسفه و اصول بنیادین', 'text', 'Eyebrow Tag'],
        ['basics', 'title', 'Back to basics.', 'بازگشت به اصول اولیه.', 'text', 'Section Title'],
        ['basics', 'lead', "It's about EFT (Effort, Focus and Technique). Mastery comes from the fundamentals — stripping away the noise and building true strength, coordination, and mental toughness.", "همه چیز درباره EFT است (تلاش، تمرکز و تکنیک). تسلط واقعی از درک عمیق پایه‌ها شکل می‌گیرد — با کنار گذاشتن حواشی و ساختن قدرت واقعی، هماهنگی عصب و عضله و سرسختی ذهنی.", 'textarea', 'Lead Text'],
        ['basics', 'desc', "Whether in the ring or on the gym floor, precision and consistency always win. No gimmicks, no cookie-cutter templates. Every movement has purpose, every round builds capability, and every workout is tailored to your real physiological adaptation.", "چه درون رینگ مسابقه و چه روی تشک باشگاه، همیشه دقت و استمرار پیروز می‌شوند. بدون حقه‌های نمایشی، بدون برنامه‌های تکراری و کپی‌شده. هر حرکت هدفی مشخص دارد، هر راند توانایی شما را می‌سازد و هر جلسه بر اساس تطبیق فیزیولوژیک واقعی بدن شما طراحی می‌شود.", 'textarea', 'Detailed Description'],
        ['basics', 'image', 'assets/images/basics-gym.jpg', 'assets/images/basics-gym.jpg', 'image', 'Gym Artwork / Photo'],
        ['basics', 'btn1_text', 'Explore Packages', 'مشاهده پکیج‌ها', 'text', 'Primary Button Label'],
        ['basics', 'btn1_url', 'packages.php', 'packages.php', 'url', 'Primary Button URL'],
        ['basics', 'btn2_text', 'Book Free Consultation', 'رزرو مشاوره رایگان', 'text', 'Secondary Button Label'],
        ['basics', 'btn2_url', '#consultation', '#consultation', 'url', 'Secondary Button URL'],

        ['sparring', 'eyebrow', 'Philosophy / Mission', 'ماموریت و متدولوژی', 'text', 'Eyebrow Tag'],
        ['sparring', 'title', 'Where do you want to be?', 'می‌خواهی به کجا برسی؟', 'text', 'Section Title'],
        ['sparring', 'text', '1-1 Personal Training or Group Training in dedicated sessions. Tailored for your specific physiological needs and athletic aspirations.', 'تمرینات کاملاً اختصاصی ۱ به ۱ یا جلسات گروهی هدفمند. متناسب با نیازهای فیزیولوژیک، اهداف بدنی و سبک زندگی شما.', 'textarea', 'Description Text'],
        ['sparring', 'image', 'assets/images/sparring-boxers.jpg', 'assets/images/sparring-boxers.jpg', 'image', 'Sparring Artwork / Image'],
        ['sparring', 'features', "Biomechanically sound weight training & posture correction\nAuthentic boxing fundamentals: footwork, head movement & power\nSustainable body recomposition & accelerated fat loss\nInjury prevention with progressive load management", "تمرینات وزنه با بیومکانیک صحیح و اصلاح ساختار اسکلتی و قامت\nآموزش اصولی بوکس کاربردی: حرکت پا، مانور سر و ضربات سنگین\nبازسازی ماندگار ترکیب بدنی، عضله‌سازی و چربی‌سوزی پرشتاب\nپیشگیری قطعی از آسیب‌دیدگی با مدیریت علمی و پیش‌رونده فشار", 'textarea', 'Bullet Points (One per line)'],
        ['sparring', 'btn_text', 'Book a Session', 'رزرو یک جلسه', 'text', 'Button Label'],
        ['sparring', 'btn_url', '#consultation', '#consultation', 'url', 'Button URL'],

        ['booking', 'chat_title', 'Start a chat.', 'گفتگو را شروع کنید.', 'text', 'Chat Bar Title'],
        ['booking', 'chat_cards', json_encode($chatCards, JSON_UNESCAPED_UNICODE), json_encode($chatCards, JSON_UNESCAPED_UNICODE), 'json', 'Direct Contact Options'],
        ['booking', 'calendar_title', 'Book Your Free Consultation:', 'رزرو جلسه مشاوره رایگان:', 'text', 'Calendar Heading'],
        ['booking', 'calendar_subtitle', 'Select an available date and time slot to discuss your training goals and schedule.', 'تاریخ و ساعت مدنظرتان را در تقویم انتخاب کنید تا درباره اهداف و برنامه تمرینی‌تان صحبت کنیم.', 'textarea', 'Calendar Subtitle'],
        ['booking', 'time_slots', "09:00 AM\n11:00 AM\n02:00 PM\n05:00 PM\n07:00 PM\nFlexible", "۰۹:۰۰ صبح\n۱۱:۰۰ صبح\n۱۴:۰۰ بعدازظهر\n۱۷:۰۰ بعدازظهر\n۱۹:۰۰ عصر\nشناور / منعطف", 'textarea', 'Available Time Slots (One per line)'],
        ['booking', 'submit_btn_text', 'Confirm Consultation Request', 'تایید و ارسال درخواست مشاوره', 'text', 'Submit Button Label'],

        ['bio', 'eyebrow', 'A Brief Bio', 'معرفی کوتاه', 'text', 'Eyebrow Tag'],
        ['bio', 'title', 'Get to know me.', 'با من بیشتر آشنا شوید.', 'text', 'Main Title'],
        ['bio', 'subtitle', '(Mori — smile in Persian)', '(موری)', 'text', 'Subtitle / Persian Meaning'],
        ['bio', 'image', 'assets/images/coach-mori.jpg', 'assets/images/coach-mori.jpg', 'image', 'Coach Portrait Photo'],
        ['bio', 'narrative', "Since my teenage years, physical excellence has always been my passion.\n\nOver the years, I have studied and tried many strategies to achieve it, realizing that the journey itself is as valuable as the destination and that having an intellectually sound plan in place is of paramount importance.\n\nTherefore, I am confident in stating that with my knowledge and first-hand experience, I will guide you to reach your goals faster and injury-free by creating bespoke programs tailored to your personal needs and current limitations.\n\nWith over 13 years of training and coaching experience, I have developed a tested, results-focused method. My clients' results and feedback have proven its efficacy.\n\nLet me help you unlock the fittest version of yourself.", "از دوران نوجوانی، تعالی و اوج آمادگی بدنی همواره اشتیاق و انگیزه اصلی من بوده است.\n\nطی سال‌های متمادی، مسیرها و استراتژی‌های گوناگونی را بررسی، مطالعه و تجربه کرده‌ام؛ و آموختم که خودِ این مسیر به اندازه رسیدن به هدف ارزشمند است و داشتن برنامه‌ای متکی بر دانش و اصول علمی نقشی حیاتی در رسیدن به موفقیت دارد.\n\nبه همین دلیل با اطمینان باور دارم که می‌توانم با تلفیق دانش روز و تجربه عملی مستقیم، شما را با برنامه‌هایی سفارشی و متناسب با شرایط، توانمندی‌ها و محدودیت‌های بدنی‌تان سریع‌تر و به دور از هرگونه آسیب به اوج آمادگی برسانم.\n\nبا بیش از ۱۳ سال سابقه تخصصی در ورزش و مربی‌گری، متدی جامع و اثبات‌شده را به کار گرفته‌ام که بازخوردها و تغییرات واقعی شاگردانم گواه اثربخشی آن است.\n\nاجازه دهید بهترین و آماده‌ترین نسخه بدنی‌تان را با هم بسازیم.", 'textarea', 'Coach Bio Narrative (Paragraphs)'],
        ['bio', 'btn_text', 'Learn More About Mori', 'اطلاعات بیشتر درباره موری', 'text', 'CTA Button Label'],
        ['bio', 'btn_url', 'about.php', 'about.php', 'url', 'CTA Button URL'],
        ['bio', 'quotes', json_encode($bioQuotes, JSON_UNESCAPED_UNICODE), json_encode($bioQuotes, JSON_UNESCAPED_UNICODE), 'json', 'Testimonial Quotes Under Artwork'],

        ['gallery', 'title', 'Our Gallery', 'گالری ما', 'text', 'Section Title'],
        ['gallery', 'subtitle', 'Check out our clients results and transformations', 'تصاویر نتایج و تغییرات شاگردان ما را مشاهده کنید', 'text', 'Section Subtitle'],
        ['gallery', 'items', json_encode($galleryItems, JSON_UNESCAPED_UNICODE), json_encode($galleryItems, JSON_UNESCAPED_UNICODE), 'json', 'Showcase Transformation Cards'],
        ['gallery', 'btn_text', 'View Full Gallery', 'مشاهده گالری کامل', 'text', 'Full Gallery Button Label'],
        ['gallery', 'btn_url', 'gallery.php', 'gallery.php', 'url', 'Full Gallery Button URL'],
    ];

    $stmtContent = $pdo->prepare("
        INSERT IGNORE INTO homepage_content
            (section_key, content_key, value_en, value_fa, draft_value_en, draft_value_fa, content_type, label)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmtContent->execute([
            $item[0], $item[1], $item[2], $item[3], $item[2], $item[3], $item[4], $item[5],
        ]);
    }
}

/**
 * Check if the current request is an admin previewing draft changes.
 */
function is_admin_preview(): bool
{
    if (empty($_GET['preview'])) {
        return false;
    }

    return !empty($_SESSION['admin_id']);
}

/**
 * Fetch sections for public or preview rendering.
 *
 * @param bool $isPreview If true, uses draft_sort_order and is_draft_active
 * @return array List of active section rows
 */
function get_homepage_sections(bool $isPreview = false): array
{
    ensure_homepage_cms_tables();

    try {
        $pdo = db();
        if ($isPreview) {
            $stmt = $pdo->query(
                "SELECT id, section_key, name_en, name_fa, draft_sort_order AS sort_order, is_draft_active AS is_active 
                 FROM homepage_sections 
                 WHERE is_draft_active = 1 
                 ORDER BY draft_sort_order ASC, id ASC"
            );
        } else {
            $stmt = $pdo->query(
                "SELECT id, section_key, name_en, name_fa, sort_order, is_active 
                 FROM homepage_sections 
                 WHERE is_active = 1 
                 ORDER BY sort_order ASC, id ASC"
            );
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            return $rows;
        }
    } catch (Throwable) {
        // Fallback below
    }

    return [
        ['section_key' => 'hero', 'sort_order' => 1, 'is_active' => 1],
        ['section_key' => 'basics', 'sort_order' => 2, 'is_active' => 1],
        ['section_key' => 'sparring', 'sort_order' => 3, 'is_active' => 1],
        ['section_key' => 'booking', 'sort_order' => 4, 'is_active' => 1],
        ['section_key' => 'bio', 'sort_order' => 5, 'is_active' => 1],
        ['section_key' => 'gallery', 'sort_order' => 6, 'is_active' => 1],
    ];
}

/**
 * Fetch all sections for Admin management.
 */
function get_all_homepage_sections(): array
{
    ensure_homepage_cms_tables();

    try {
        $pdo = db();
        return $pdo->query(
            "SELECT * FROM homepage_sections ORDER BY draft_sort_order ASC, id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return [];
    }
}

/**
 * Fetch all homepage content records.
 *
 * @param bool $isPreview If true, returns draft values
 * @return array Structured as [$section_key][$content_key] => ['en' => ..., 'fa' => ..., 'type' => ...]
 */
function get_homepage_content(bool $isPreview = false): array
{
    ensure_homepage_cms_tables();

    static $cacheLive = null;
    static $cacheDraft = null;

    if ($isPreview && $cacheDraft !== null) {
        return $cacheDraft;
    }
    if (!$isPreview && $cacheLive !== null) {
        return $cacheLive;
    }

    $structured = [];

    try {
        $pdo = db();
        $rows = $pdo->query("SELECT * FROM homepage_content")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $sec = $row['section_key'];
            $key = $row['content_key'];
            $type = $row['content_type'];

            $valEn = $isPreview ? ($row['draft_value_en'] ?? $row['value_en']) : $row['value_en'];
            $valFa = $isPreview ? ($row['draft_value_fa'] ?? $row['value_fa']) : $row['value_fa'];

            $structured[$sec][$key] = [
                'en' => $valEn ?? '',
                'fa' => $valFa ?? '',
                'type' => $type,
                'label' => $row['label'] ?? $key,
                'draft_en' => $row['draft_value_en'] ?? '',
                'draft_fa' => $row['draft_value_fa'] ?? '',
                'live_en' => $row['value_en'] ?? '',
                'live_fa' => $row['value_fa'] ?? '',
            ];
        }
    } catch (Throwable) {
        // Fallback to empty
    }

    if ($isPreview) {
        $cacheDraft = $structured;
        return $cacheDraft;
    }

    $cacheLive = $structured;
    return $cacheLive;
}

/**
 * Helper to retrieve a single content value for a given section, key, and language.
 */
function hp_field(array $content, string $section, string $key, string $lang, string $default = ''): string
{
    if (isset($content[$section][$key])) {
        $val = $content[$section][$key][$lang] ?? '';
        if ($val !== '') {
            return (string) $val;
        }
        // Fallback to English if Persian is empty
        $fallback = $content[$section][$key]['en'] ?? '';
        if ($fallback !== '') {
            return (string) $fallback;
        }
    }
    return $default;
}

/**
 * Helper to retrieve and decode JSON content (highlights, chat cards, quotes, gallery).
 */
function hp_json(array $content, string $section, string $key, array $default = []): array
{
    if (isset($content[$section][$key]['en'])) {
        $raw = $content[$section][$key]['en'];
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }
    }
    return $default;
}

/**
 * Check whether there are any unpublished changes in drafts vs live.
 */
function has_homepage_unpublished_changes(): bool
{
    ensure_homepage_cms_tables();

    try {
        $pdo = db();
        $secStmt = $pdo->query("
            SELECT COUNT(*) FROM homepage_sections 
            WHERE sort_order != draft_sort_order OR is_active != is_draft_active
        ");
        if ((int) $secStmt->fetchColumn() > 0) {
            return true;
        }

        $contentStmt = $pdo->query("
            SELECT COUNT(*) FROM homepage_content 
            WHERE (draft_value_en IS NOT NULL AND BINARY draft_value_en != BINARY COALESCE(value_en, ''))
               OR (draft_value_fa IS NOT NULL AND BINARY draft_value_fa != BINARY COALESCE(value_fa, ''))
        ");
        return (int) $contentStmt->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Publish all draft changes to live.
 */
function publish_homepage_drafts(): void
{
    ensure_homepage_cms_tables();
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->exec("
            UPDATE homepage_sections 
            SET sort_order = draft_sort_order, is_active = is_draft_active
        ");

        $pdo->exec("
            UPDATE homepage_content 
            SET value_en = draft_value_en, value_fa = draft_value_fa
        ");

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Discard all drafts and revert to current live values.
 */
function revert_homepage_drafts(): void
{
    ensure_homepage_cms_tables();
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->exec("
            UPDATE homepage_sections 
            SET draft_sort_order = sort_order, is_draft_active = is_active
        ");

        $pdo->exec("
            UPDATE homepage_content 
            SET draft_value_en = value_en, draft_value_fa = value_fa
        ");

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Save draft values for a specific section.
 */
function save_homepage_section_draft(string $sectionKey, array $fieldsEn, array $fieldsFa): void
{
    ensure_homepage_cms_tables();
    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE homepage_content 
        SET draft_value_en = ?, draft_value_fa = ?
        WHERE section_key = ? AND content_key = ?
    ");

    foreach ($fieldsEn as $key => $valEn) {
        $valFa = $fieldsFa[$key] ?? $valEn;
        $stmt->execute([$valEn, $valFa, $sectionKey, $key]);
    }
}

/**
 * Save section order and active state in draft mode.
 */
function save_homepage_sections_order(array $orders, array $activeKeys): void
{
    ensure_homepage_cms_tables();
    $pdo = db();
    $stmt = $pdo->prepare("
        UPDATE homepage_sections 
        SET draft_sort_order = ?, is_draft_active = ?
        WHERE section_key = ?
    ");

    foreach ($orders as $secKey => $sortOrder) {
        $isActive = in_array($secKey, $activeKeys, true) ? 1 : 0;
        $stmt->execute([(int) $sortOrder, $isActive, $secKey]);
    }
}

/**
 * Scan media library files.
 *
 * @return array Array of image items with metadata
 */
function get_media_library_images(): array
{
    $folders = [
        'assets/images/uploads' => __DIR__ . '/../assets/images/uploads',
        'assets/images' => __DIR__ . '/../assets/images',
    ];

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $images = [];

    foreach ($folders as $relPrefix => $fullDir) {
        if (!is_dir($fullDir)) {
            continue;
        }

        $files = scandir($fullDir);
        if ($files === false) {
            continue;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($fullDir . '/' . $file)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                continue;
            }

            $filePath = $fullDir . '/' . $file;
            $relPath = $relPrefix . '/' . $file;
            $fileSize = filesize($filePath) ?: 0;
            $mtime = filemtime($filePath) ?: 0;

            $dim = '';
            if ($ext !== 'svg') {
                $imgInfo = @getimagesize($filePath);
                if ($imgInfo) {
                    $dim = $imgInfo[0] . ' × ' . $imgInfo[1] . ' px';
                }
            } else {
                $dim = 'SVG Vector';
            }

            $images[] = [
                'filename' => $file,
                'path' => $relPath,
                'size_human' => round($fileSize / 1024, 1) . ' KB',
                'size_bytes' => $fileSize,
                'mtime' => $mtime,
                'date_human' => date('Y-m-d H:i', $mtime),
                'dimensions' => $dim,
                'is_upload' => str_starts_with($relPath, 'assets/images/uploads/'),
            ];
        }
    }

    usort($images, static fn($a, $b) => $b['mtime'] <=> $a['mtime']);

    return $images;
}

if (!function_exists('render_highlight_icon')) {
    /**
     * Render SVG icon for Hero Bottom Highlights.
     */
    function render_highlight_icon(string $icon): string
    {
        switch ($icon) {
            case 'dumbbell':
                return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 5v14M18 5v14M2 9h4v6H2zM18 9h4v6h-4zM6 12h12"/></svg>';
            case 'map-pin':
                return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>';
            case 'star':
                return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            case 'calendar':
                return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
            case 'award':
            default:
                return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>';
        }
    }
}

if (!function_exists('render_chat_icon')) {
    /**
     * Render SVG icon for Chat / Direct Contact Cards.
     */
    function render_chat_icon(string $icon): string
    {
        switch ($icon) {
            case 'mail':
                return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
            case 'whatsapp':
                return '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.5-1.3-.7-1.8-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A3 3 0 0 0 6.3 9c0 1.8 1.3 3.5 1.5 3.7s2.5 3.9 6.1 5.2c2.2.8 2.6.6 3.1.6s1.4-.6 1.6-1.1.2-1 .1-1.1-.3-.2-.5-.3z"/></svg>';
            case 'instagram':
                return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>';
            case 'phone':
            default:
                return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
        }
    }
}

if (!function_exists('resolve_hp_url')) {
    /**
     * Resolve a homepage button or card URL for the given language.
     */
    function resolve_hp_url(string $rawUrl, string $lang): string
    {
        $rawUrl = sanitize_cms_url($rawUrl);
        if ($rawUrl === '' || str_starts_with($rawUrl, '#') || str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://') || str_starts_with($rawUrl, 'mailto:') || str_starts_with($rawUrl, 'tel:')) {
            return $rawUrl !== '' ? $rawUrl : '#';
        }
        if (str_starts_with($rawUrl, $lang . '/')) {
            return url($rawUrl);
        }
        return url($lang . '/' . ltrim($rawUrl, '/'));
    }
}
