<?php
/**
 * Persian home — Redesigned Editorial Layout based on reference wireframe (RTL).
 * Dynamically powered by Admin Panel Homepage Builder while preserving all original texts & layout.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/homepage_cms.php';

if (!empty($_GET['preview'])) {
    require_once dirname(__DIR__) . '/includes/auth.php';
    auth_start();
} else {
    public_session();
}

$lang = 'fa';
$dir = 'rtl';
$activeNav = 'home';
$currentPage = '';

$page = get_page('home');
$title = $page ? lang_field($page, 'title', $lang) : SITE_NAME;
$tagline = setting('tagline_fa', 'تمرین شخصی · بوکس · کاهش چربی');
$packages = get_active_packages();

$phone = setting('contact_phone', '+60 12-345 6789');
$whatsapp = setting('contact_whatsapp', '+60 12-345 6789');
$email = setting('contact_email', 'hello@moris.example');
$whatsappText = preg_replace('/[^0-9]/', '', $whatsapp);

// Handle Consultation Booking Submission
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_consultation') {
    require_once dirname(__DIR__) . '/includes/security.php';
    if (!form_check()) {
        $bookingError = 'نشست شما منقضی شده است. لطفاً صفحه را رفرش کرده و دوباره تلاش کنید.';
    } elseif (!rate_limit_allow('booking_' . md5(client_ip()), 5, 600)) {
        $bookingError = 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً چند دقیقه صبر کنید.';
    } else {
        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
        $userEmail = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 120);
        $userPhone = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30);
        $userGoal = mb_substr(trim((string) ($_POST['goal'] ?? '')), 0, 100);
        $bookDate = mb_substr(trim((string) ($_POST['consultation_date'] ?? '')), 0, 20);
        $bookSlot = mb_substr(trim((string) ($_POST['consultation_slot'] ?? '')), 0, 20);
        $honeypot = trim((string) ($_POST['website'] ?? ''));

        if ($name === '') {
            $bookingError = 'لطفاً نام خود را وارد کنید.';
        } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $bookingError = 'لطفاً یک آدرس ایمیل معتبر وارد کنید.';
        } elseif ($honeypot !== '') {
            $bookingSuccess = true;
        } else {
            $consultationDetails = "رزرو مشاوره رایگان\nتاریخ: " . ($bookDate ?: 'شناور') . "\nساعت ترجیحی: " . ($bookSlot ?: 'شناور') . "\nهدف: " . ($userGoal ?: 'تناسب اندام عمومی');
            $fullGoal = 'Free Consultation (' . ($bookDate ?: 'Pending') . ' ' . ($bookSlot ?: '') . ')';

            try {
                require_once dirname(__DIR__) . '/includes/db.php';
                $stmt = db()->prepare(
                    'INSERT INTO contact_messages (name, email, phone, goal, message) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$name, $userEmail, $userPhone, $fullGoal, $consultationDetails]);
                send_contact_notification([
                    'name' => $name,
                    'email' => $userEmail,
                    'phone' => $userPhone,
                    'goal' => $fullGoal,
                    'message' => $consultationDetails
                ]);
                $bookingSuccess = true;
            } catch (Throwable $e) {
                $bookingError = 'خطایی در ثبت درخواست رخ داد. لطفاً مستقیماً در واتساپ پیام دهید.';
            }
        }
    }
}

$isPreview = is_admin_preview();
$sections = get_homepage_sections($isPreview);
$hp = get_homepage_content($isPreview);

// Default Bio Quotes with CMS override
$defaultBioQuotes = [
    [
        'quote_fa' => 'رویکردی کاملاً حرفه‌ای و علمی به تناسب اندام و تمرین دارد.',
        'author_fa' => 'هنری جیمز — مدیر و بوکسور'
    ],
    [
        'quote_fa' => 'در عرض ۶ ماه قدرت و چابکی من بدون هیچ دردی در مفاصل چند برابر شد.',
        'author_fa' => 'فرهاد ک. — مهندس نرم‌افزار'
    ],
    [
        'quote_fa' => 'توضیحات بسیار شفاف، توجه اختصاصی در طول تمرین و دستیابی به نتایج واقعی.',
        'author_fa' => 'سارا م. — ورزشکار سه‌گانه'
    ]
];
$rawBioQuotes = hp_json($hp, 'bio', 'quotes', $defaultBioQuotes);
$jsBioQuotes = [];
foreach ($rawBioQuotes as $q) {
    $qText = $q['quote_fa'] ?? $q['quote_en'] ?? '';
    $qAuth = $q['author_fa'] ?? $q['author_en'] ?? '';
    if ($qText !== '') {
        $jsBioQuotes[] = [
            'text' => '«' . $qText . '»',
            'author' => $qAuth,
        ];
    }
}

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — مربی شخصی، آموزش بوکس و تناسب اندام';

require dirname(__DIR__) . '/includes/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/editorial-home.css')) ?>">

<div class="editorial-home">
<?php foreach ($sections as $sec): 
    $secKey = $sec['section_key'] ?? '';
?>

    <?php if ($secKey === 'hero'): 
        $heroHeadline = hp_field($hp, 'hero', 'headline', $lang, 'به دستش بیار.');
        $tag1 = hp_field($hp, 'hero', 'tagline_1', $lang, 'اعتمادبه‌نفست،');
        $tag2 = hp_field($hp, 'hero', 'tagline_2', $lang, 'قدرتت،');
        $tag3 = hp_field($hp, 'hero', 'tagline_3', $lang, 'مسیرت.');
        $bgLtr = hp_field($hp, 'hero', 'bg_image', 'en', 'assets/images/hero-earn-it.webp');
        $bgRtl = hp_field($hp, 'hero', 'bg_image_rtl', 'en', 'assets/images/hero-earn-it-rtl.webp');

        $defaultHighlights = [
            [
                'icon' => 'award',
                'title_fa' => '۱۳+ سال تجربه در رینگ',
                'desc_fa' => 'مربی‌گری قهرمانی و سابقه مسابقات حرفه‌ای'
            ],
            [
                'icon' => 'dumbbell',
                'title_fa' => 'تمرین خصوصی و بوکس',
                'desc_fa' => 'برنامه‌ریزی دقیق و شخصی‌سازی شده قدرت و آمادگی جسمانی'
            ],
            [
                'icon' => 'map-pin',
                'title_fa' => 'کوالالامپور و آنلاین',
                'desc_fa' => 'تمرینات با وزنه، چربی‌سوزی و برنامه‌ریزی ورزشی'
            ]
        ];
        $highlights = hp_json($hp, 'hero', 'highlights', $defaultHighlights);

        $heroStyle = sprintf(
            "--hero-bg-url: url('%s'); --hero-bg-rtl-url: url('%s');",
            e(url($bgLtr)),
            e(url($bgRtl))
        );
    ?>
    <!-- ======================================================================
         Section B: Cinematic Full-Width Hero Section (Reference: EARN IT. - RTL)
         ====================================================================== -->
    <section class="ed-hero-section ed-hero-section--rtl" id="hero" style="<?= $heroStyle ?>">
        <div class="ed-hero-container">
            <div class="ed-hero-main-content">
                <h1 class="ed-hero-headline"><?= e($heroHeadline) ?></h1>
                <div class="ed-hero-tagline">
                    <?php if ($tag1 !== ''): ?><span class="ed-tagline-line"><?= e($tag1) ?></span><?php endif; ?>
                    <?php if ($tag2 !== ''): ?><span class="ed-tagline-line"><?= e($tag2) ?></span><?php endif; ?>
                    <?php if ($tag3 !== ''): ?><span class="ed-tagline-line"><?= e($tag3) ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Bottom Information Highlights (3 Items horizontally aligned) -->
            <div class="ed-hero-highlights">
                <?php foreach ($highlights as $hl): 
                    $hlTitle = $hl['title_fa'] ?? $hl['title_en'] ?? '';
                    $hlDesc = $hl['desc_fa'] ?? $hl['desc_en'] ?? '';
                    $hlIcon = $hl['icon'] ?? 'award';
                ?>
                <div class="ed-highlight-item">
                    <div class="ed-highlight-icon">
                        <?= render_highlight_icon($hlIcon) ?>
                    </div>
                    <div class="ed-highlight-text">
                        <strong class="ed-highlight-title"><?= e($hlTitle) ?></strong>
                        <span class="ed-highlight-desc"><?= e($hlDesc) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($secKey === 'basics'): 
        $basicsImg = hp_field($hp, 'basics', 'image', $lang, 'assets/images/basics-gym.jpg');
        $basicsEyebrow = hp_field($hp, 'basics', 'eyebrow', $lang, 'EFT · فلسفه و اصول بنیادین');
        $basicsTitle = hp_field($hp, 'basics', 'title', $lang, 'بازگشت به اصول اولیه.');
        $basicsLead = hp_field($hp, 'basics', 'lead', $lang, 'همه چیز درباره EFT است (تلاش، تمرکز و تکنیک). تسلط واقعی از درک عمیق پایه‌ها شکل می‌گیرد — با کنار گذاشتن حواشی و ساختن قدرت واقعی، هماهنگی عصب و عضله و سرسختی ذهنی.');
        $basicsDesc = hp_field($hp, 'basics', 'desc', $lang, 'چه درون رینگ مسابقه و چه روی تشک باشگاه، همیشه دقت و استمرار پیروز می‌شوند. بدون حقه‌های نمایشی، بدون برنامه‌های تکراری و کپی‌شده. هر حرکت هدفی مشخص دارد، هر راند توانایی شما را می‌سازد و هر جلسه بر اساس تطبیق فیزیولوژیک واقعی بدن شما طراحی می‌شود.');
        $btn1Text = hp_field($hp, 'basics', 'btn1_text', $lang, 'مشاهده پکیج‌ها');
        $btn1Url = hp_field($hp, 'basics', 'btn1_url', $lang, 'packages.php');
        $btn2Text = hp_field($hp, 'basics', 'btn2_text', $lang, 'رزرو مشاوره رایگان');
        $btn2Url = hp_field($hp, 'basics', 'btn2_url', $lang, '#consultation');
    ?>
    <!-- ======================================================================
         Section C: Introductory Content Section ("Back to Basics")
         ====================================================================== -->
    <section class="ed-basics-section" id="basics">
        <div class="ed-basics-grid">
            <div class="ed-basics-artwork">
                <img src="<?= e(url($basicsImg)) ?>" 
                     alt="<?= e($basicsTitle) ?>" 
                     class="ed-basics-calligraphy"
                     loading="lazy">
            </div>

            <div class="ed-basics-content">
                <span class="ed-basics-eyebrow"><?= e($basicsEyebrow) ?></span>
                <h2 class="ed-basics-title"><?= e($basicsTitle) ?></h2>
                <p class="ed-basics-lead">
                    <?= nl2br(e($basicsLead)) ?>
                </p>
                <p class="ed-basics-desc">
                    <?= nl2br(e($basicsDesc)) ?>
                </p>
                <div class="cta-row">
                    <?php if ($btn1Text !== ''): ?>
                        <a href="<?= e(resolve_hp_url($btn1Url, $lang)) ?>" class="btn-cta"><?= e($btn1Text) ?></a>
                    <?php endif; ?>
                    <?php if ($btn2Text !== ''): ?>
                        <a href="<?= e(resolve_hp_url($btn2Url, $lang)) ?>" class="btn-outline"><?= e($btn2Text) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($secKey === 'sparring'): 
        $sparringEyebrow = hp_field($hp, 'sparring', 'eyebrow', $lang, 'ماموریت و متدولوژی');
        $sparringTitle = hp_field($hp, 'sparring', 'title', $lang, 'می‌خواهی به کجا برسی؟');
        $sparringText = hp_field($hp, 'sparring', 'text', $lang, 'تمرینات کاملاً اختصاصی ۱ به ۱ یا جلسات گروهی هدفمند. متناسب با نیازهای فیزیولوژیک، اهداف بدنی و سبک زندگی شما.');
        $sparringImg = hp_field($hp, 'sparring', 'image', $lang, 'assets/images/sparring-boxers.jpg');
        $defaultFeaturesStr = "تمرینات وزنه با بیومکانیک صحیح و اصلاح ساختار اسکلتی و قامت\nآموزش اصولی بوکس کاربردی: حرکت پا، مانور سر و ضربات سنگین\nبازسازی ماندگار ترکیب بدنی، عضله‌سازی و چربی‌سوزی پرشتاب\nپیشگیری قطعی از آسیب‌دیدگی با مدیریت علمی و پیش‌رونده فشار";
        $sparringFeatures = parse_features(hp_field($hp, 'sparring', 'features', $lang, $defaultFeaturesStr));
        $sparringBtnText = hp_field($hp, 'sparring', 'btn_text', $lang, 'رزرو یک جلسه');
        $sparringBtnUrl = hp_field($hp, 'sparring', 'btn_url', $lang, '#consultation');
    ?>
    <!-- ======================================================================
         Section D: Large Illustrated Content Section ("Where do you want to be?")
         ====================================================================== -->
    <section class="ed-sparring-section" id="sparring">
        <div class="ed-sparring-container">
            <div class="ed-sparring-content">
                <span class="ed-sparring-eyebrow"><?= e($sparringEyebrow) ?></span>
                <h2 class="ed-sparring-title"><?= e($sparringTitle) ?></h2>
                <p class="ed-sparring-text">
                    <?= nl2br(e($sparringText)) ?>
                </p>
                <ul class="ed-sparring-features">
                    <?php foreach ($sparringFeatures as $feat): ?>
                        <li class="ed-sparring-feature-item"><?= e($feat) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($sparringBtnText !== ''): ?>
                    <a href="<?= e(resolve_hp_url($sparringBtnUrl, $lang)) ?>" class="btn-cta"><?= e($sparringBtnText) ?></a>
                <?php endif; ?>
            </div>

            <div class="ed-sparring-artwork">
                <img src="<?= e(url($sparringImg)) ?>" 
                     alt="<?= e($sparringTitle) ?>" 
                     class="ed-sparring-art"
                     loading="lazy">
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($secKey === 'booking'): 
        $chatTitle = hp_field($hp, 'booking', 'chat_title', $lang, 'گفتگو را شروع کنید.');
        $defaultChatCards = [
            [
                'icon' => 'phone',
                'title_fa' => 'تماس تلفنی مستقیم',
                'val_fa' => $phone,
                'btn_fa' => 'تماس بگیرید',
                'link' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone)
            ],
            [
                'icon' => 'mail',
                'title_fa' => 'ارسال ایمیل',
                'val_fa' => $email,
                'btn_fa' => 'ارسال ایمیل',
                'link' => 'mailto:' . $email
            ],
            [
                'icon' => 'whatsapp',
                'title_fa' => 'چت واتساپ',
                'val_fa' => 'پاسخ سریع و مستقیم',
                'btn_fa' => 'ارسال پیام',
                'link' => 'https://wa.me/' . $whatsappText
            ]
        ];
        $chatCards = hp_json($hp, 'booking', 'chat_cards', $defaultChatCards);
        $calTitle = hp_field($hp, 'booking', 'calendar_title', $lang, 'رزرو جلسه مشاوره رایگان:');
        $calSubtitle = hp_field($hp, 'booking', 'calendar_subtitle', $lang, 'تاریخ و ساعت مدنظرتان را در تقویم انتخاب کنید تا درباره اهداف و برنامه تمرینی‌تان صحبت کنیم.');
        $timeSlots = parse_features(hp_field($hp, 'booking', 'time_slots', $lang, "۰۹:۰۰ صبح\n۱۱:۰۰ صبح\n۱۴:۰۰ بعدازظهر\n۱۷:۰۰ بعدازظهر\n۱۹:۰۰ عصر\nشناور / منعطف"));
        $submitBtnText = hp_field($hp, 'booking', 'submit_btn_text', $lang, 'تایید و ارسال درخواست مشاوره');
        $firstSlot = $timeSlots[0] ?? '۰۹:۰۰ صبح';
    ?>
    <!-- ======================================================================
         Section E: Consultation or Primary Action Section
         ====================================================================== -->
    <section class="ed-booking-section" id="consultation">
        <div class="ed-booking-inner">
            <div class="ed-chat-bar">
                <h2 class="ed-chat-title"><?= e($chatTitle) ?></h2>
                <div class="ed-chat-options">
                    <?php foreach ($chatCards as $card): 
                        $cTitle = $card['title_fa'] ?? $card['title_en'] ?? '';
                        $cVal = $card['val_fa'] ?? $card['val_en'] ?? '';
                        $cBtn = $card['btn_fa'] ?? $card['btn_en'] ?? '';
                        $cLink = sanitize_cms_url((string) ($card['link'] ?? '#')) ?: '#';
                        $cIcon = $card['icon'] ?? 'phone';
                        $isExt = str_starts_with($cLink, 'http');
                    ?>
                    <a href="<?= e($cLink) ?>" <?= $isExt ? 'target="_blank" rel="noopener"' : '' ?> class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <?= render_chat_icon($cIcon) ?>
                        </div>
                        <h3><?= e($cTitle) ?></h3>
                        <p dir="ltr"><?= e($cVal) ?></p>
                        <span class="ed-chat-btn-label"><?= e($cBtn) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ed-calendar-wrapper">
                <div class="ed-calendar-heading-row">
                    <h3 class="ed-calendar-title"><?= e($calTitle) ?></h3>
                    <p class="ed-calendar-subtitle"><?= e($calSubtitle) ?></p>
                </div>

                <?php if ($bookingSuccess): ?>
                    <div class="ed-booking-feedback is-success">
                        <strong>سپاسگزاریم!</strong> درخواست مشاوره شما با موفقیت ثبت شد. مربی موری به زودی زمان جلسه را با شما هماهنگ خواهد کرد.
                    </div>
                <?php elseif ($bookingError !== ''): ?>
                    <div class="ed-booking-feedback is-error">
                        <?= e($bookingError) ?>
                    </div>
                <?php endif; ?>

                <div class="ed-booking-grid-layout">
                    <div class="ed-calendar-box">
                        <div class="ed-cal-header">
                            <div class="ed-cal-month-title" id="ed-cal-month-title">اکتبر ۲۰۲۶</div>
                            <div class="ed-cal-nav">
                                <button type="button" class="ed-cal-arrow" id="ed-cal-prev" aria-label="ماه قبل">&lsaquo;</button>
                                <button type="button" class="ed-cal-arrow" id="ed-cal-next" aria-label="ماه بعد">&rsaquo;</button>
                            </div>
                        </div>

                        <div class="ed-cal-weekdays">
                            <span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span><span>ش</span><span>ی</span>
                        </div>

                        <div class="ed-cal-days" id="ed-cal-days"></div>
                    </div>

                    <form method="post" action="#consultation" class="ed-booking-form-box">
                        <?= form_field() ?>
                        <input type="hidden" name="action" value="book_consultation">
                        <input type="hidden" name="consultation_date" id="ed-selected-date" value="<?= date('Y-m-d') ?>">
                        <input type="hidden" name="consultation_slot" id="ed-selected-slot" value="<?= e($firstSlot) ?>">
                        <div class="hp-field" aria-hidden="true">
                            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div>
                            <span class="ed-slots-label">انتخاب بازه زمانی ترجیحی:</span>
                            <div class="ed-slots-grid">
                                <?php foreach ($timeSlots as $sIdx => $slot): ?>
                                    <div class="ed-slot-pill <?= $sIdx === 0 ? 'is-selected' : '' ?>" data-time="<?= e($slot) ?>"><?= e($slot) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="ed-form-field">
                            <label for="book-name">نام و نام خانوادگی *</label>
                            <input type="text" id="book-name" name="name" class="ed-form-input" required placeholder="مثلاً علی رضایی">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-email">آدرس ایمیل *</label>
                            <input type="email" id="book-email" name="email" class="ed-form-input" required placeholder="ali@example.com" dir="ltr">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-phone">شماره تماس / واتساپ</label>
                            <input type="tel" id="book-phone" name="phone" class="ed-form-input" placeholder="+60 ..." dir="ltr">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-goal">هدف اصلی شما</label>
                            <select id="book-goal" name="goal" class="ed-form-select">
                                <option value="Boxing Technique & Fitness">آموزش بوکس و آمادگی جسمانی</option>
                                <option value="Fat Loss & Body Conditioning">چربی‌سوزی و تناسب اندام</option>
                                <option value="Strength & Muscle Building">افزایش قدرت و عضله‌سازی</option>
                                <option value="1-on-1 Personal Training">تمرین خصوصی ۱ به ۱</option>
                                <option value="Online Coaching">کوچینگ و برنامه آنلاین</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-cta ed-booking-submit"><?= e($submitBtnText) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($secKey === 'bio'): 
        $bioImg = hp_field($hp, 'bio', 'image', $lang, 'assets/images/coach-mori.jpg');
        $bioEyebrow = hp_field($hp, 'bio', 'eyebrow', $lang, 'معرفی کوتاه');
        $bioTitle = hp_field($hp, 'bio', 'title', $lang, 'با من بیشتر آشنا شوید.');
        $bioSubtitle = hp_field($hp, 'bio', 'subtitle', $lang, '(موری)');
        $defaultNarrative = "از دوران نوجوانی، تعالی و اوج آمادگی بدنی همواره اشتیاق و انگیزه اصلی من بوده است.\n\nطی سال‌های متمادی، مسیرها و استراتژی‌های گوناگونی را بررسی، مطالعه و تجربه کرده‌ام؛ و آموختم که خودِ این مسیر به اندازه رسیدن به هدف ارزشمند است و داشتن برنامه‌ای متکی بر دانش و اصول علمی نقشی حیاتی در رسیدن به موفقیت دارد.\n\nبه همین دلیل با اطمینان باور دارم که می‌توانم با تلفیق دانش روز و تجربه عملی مستقیم، شما را با برنامه‌هایی سفارشی و متناسب با شرایط، توانمندی‌ها و محدودیت‌های بدنی‌تان سریع‌تر و به دور از هرگونه آسیب به اوج آمادگی برسانم.\n\nبا بیش از ۱۳ سال سابقه تخصصی در ورزش و مربی‌گری، متدی جامع و اثبات‌شده را به کار گرفته‌ام که بازخوردها و تغییرات واقعی شاگردانم گواه اثربخشی آن است.\n\nاجازه دهید بهترین و آماده‌ترین نسخه بدنی‌تان را با هم بسازیم.";
        $bioNarrative = hp_field($hp, 'bio', 'narrative', $lang, $defaultNarrative);
        $bioBtnText = hp_field($hp, 'bio', 'btn_text', $lang, 'اطلاعات بیشتر درباره موری');
        $bioBtnUrl = hp_field($hp, 'bio', 'btn_url', $lang, 'about.php');
        $paragraphs = preg_split('/\r\n\r\n|\n\n/', trim($bioNarrative)) ?: [];
        $firstQuote = $jsBioQuotes[0] ?? [
            'text' => '«رویکردی کاملاً حرفه‌ای و علمی به تناسب اندام و تمرین دارد.»',
            'author' => 'هنری جیمز — مدیر و بوکسور'
        ];
    ?>
    <!-- ======================================================================
         Section F: About or Introduction Section ("Get to know me.")
         ====================================================================== -->
    <section class="ed-bio-section" id="bio">
        <div class="ed-bio-container">
            <div class="ed-bio-visual-col">
                <img src="<?= e(url($bioImg)) ?>" 
                     alt="پرتره مربی موری" 
                     class="ed-bio-artwork"
                     loading="lazy">

                <div class="ed-bio-quote-card">
                    <p class="ed-bio-quote-text" id="ed-bio-quote-text">
                        <?= e($firstQuote['text']) ?>
                    </p>
                    <div class="ed-bio-quote-author" id="ed-bio-quote-author">
                        <?= e($firstQuote['author']) ?>
                    </div>
                    <div class="ed-bio-dots">
                        <?php foreach ($jsBioQuotes as $qIdx => $qItem): ?>
                            <button type="button" class="ed-bio-dot <?= $qIdx === 0 ? 'is-active' : '' ?>" aria-label="نقل قول <?= $qIdx + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="ed-bio-content">
                <span class="ed-bio-eyebrow"><?= e($bioEyebrow) ?></span>
                <h2 class="ed-bio-title">
                    <?= e($bioTitle) ?>
                    <?php if ($bioSubtitle !== ''): ?>
                        <span class="farsi-subtitle"><?= e($bioSubtitle) ?></span>
                    <?php endif; ?>
                </h2>

                <div class="ed-bio-narrative">
                    <?php foreach ($paragraphs as $p): 
                        if (trim($p) === '') continue;
                    ?>
                        <p><?= nl2br(e(trim($p))) ?></p>
                    <?php endforeach; ?>
                </div>

                <?php if ($bioBtnText !== ''): ?>
                    <div class="ed-bio-btn-row">
                        <a href="<?= e(resolve_hp_url($bioBtnUrl, $lang)) ?>" class="btn-outline"><?= e($bioBtnText) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($secKey === 'gallery'): 
        $galTitle = hp_field($hp, 'gallery', 'title', $lang, 'گالری ما');
        $galSubtitle = hp_field($hp, 'gallery', 'subtitle', $lang, 'تصاویر نتایج و تغییرات شاگردان ما را مشاهده کنید');
        $defaultGalItems = [
            [
                'image' => 'assets/images/gallery-sara.jpg',
                'name_fa' => 'سارا',
                'tag_fa' => 'بوکس و آمادگی جسمانی',
                'quote_fa' => 'تمرینات بوکس با موری استقامت، سرعت واکنش و تمرکز ذهنی من را به کلی دگرگون کرد. متدولوژی تکنیک‌محور او باعث می‌شود هر راند پرچالش و لذت‌بخش باشد.'
            ],
            [
                'image' => 'assets/images/gallery-megan.jpg',
                'name_fa' => 'مگان',
                'tag_fa' => 'کاهش چربی و افزایش قدرت',
                'quote_fa' => '۱۱ کیلوگرم وزن کم کردم و عضلات شکم و پشتم تفکیک فوق‌العاده‌ای پیدا کرد. موری یک برنامه قدرتی منظم تنظیم کرد که کاملاً با مشغله کاری من هماهنگ بود. بهترین مربی در کوالالامپور.'
            ]
        ];
        $cmsGalItems = hp_json($hp, 'gallery', 'items', $defaultGalItems);

        // Pull active photos from gallery.php (gallery_items table) into this section
        $dbGalItems = get_gallery_items(true);
        $dbGalCats = [];
        foreach (get_gallery_categories(true) as $gc) {
            $dbGalCats[(int) $gc['id']] = lang_field($gc, 'name', $lang);
        }

        $galItems = [];
        if (!empty($dbGalItems)) {
            foreach ($dbGalItems as $idx => $gRow) {
                $catLabel = ($gRow['category_id'] !== null && isset($dbGalCats[(int) $gRow['category_id']]))
                    ? $dbGalCats[(int) $gRow['category_id']]
                    : 'گالری و نتایج شاگردان';
                $rowTitle = trim(lang_field($gRow, 'title', $lang));
                $cmsFallback = $cmsGalItems[$idx] ?? [];
                $galItems[] = [
                    'image'    => $gRow['image_path'],
                    'name_fa'  => $rowTitle !== '' ? $rowTitle : ($cmsFallback['name_fa'] ?? $cmsFallback['name_en'] ?? ('تصویر شاگرد #' . ($idx + 1))),
                    'tag_fa'   => $catLabel,
                    'quote_fa' => $cmsFallback['quote_fa'] ?? $cmsFallback['quote_en'] ?? ''
                ];
            }
            // Append remaining CMS transformation items if only 1 gallery photo exists so the 2-column showcase stays balanced
            if (count($galItems) === 1 && count($cmsGalItems) > 1) {
                for ($i = 1; $i < count($cmsGalItems); $i++) {
                    $galItems[] = $cmsGalItems[$i];
                }
            }
        } else {
            $galItems = $cmsGalItems;
        }

        $galBtnText = hp_field($hp, 'gallery', 'btn_text', $lang, 'مشاهده گالری کامل');
        $galBtnUrl = hp_field($hp, 'gallery', 'btn_url', $lang, 'gallery.php');
        $galTotalPages = max(1, (int) ceil(count($galItems) / 2));
    ?>
    <!-- ======================================================================
         Section G: Gallery or Visual Showcase ("Our Gallery")
         ====================================================================== -->
    <section class="ed-gallery-section" id="gallery">
        <div class="ed-gallery-header">
            <h2 class="ed-gallery-title"><?= e($galTitle) ?></h2>
            <p class="ed-gallery-subtitle"><?= e($galSubtitle) ?></p>
        </div>

        <div class="ed-gallery-cards-grid">
            <?php foreach ($galItems as $gIdx => $gCard): 
                $gImg = $gCard['image'] ?? 'assets/images/gallery-sara.jpg';
                $gName = $gCard['name_fa'] ?? $gCard['name_en'] ?? '';
                $gTag = $gCard['tag_fa'] ?? $gCard['tag_en'] ?? '';
                $gQuote = $gCard['quote_fa'] ?? $gCard['quote_en'] ?? '';
                $gPage = (int) floor($gIdx / 2);
            ?>
            <div class="ed-gallery-card" data-gal-page="<?= $gPage ?>"<?= $gPage > 0 ? ' style="display:none;"' : '' ?>>
                <div class="ed-gallery-media-wrapper">
                    <a href="<?= e(resolve_hp_url($galBtnUrl, $lang)) ?>" style="display:block;color:inherit;text-decoration:none;">
                        <img src="<?= e(url($gImg)) ?>" 
                             alt="شاگرد <?= e($gName) ?>" 
                             class="ed-gallery-image"
                             loading="lazy">
                    </a>
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name"><?= e($gName) ?></h3>
                        <?php if ($gTag !== ''): ?>
                            <span class="ed-gallery-client-tag"><?= e($gTag) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($gQuote !== ''): ?>
                    <p class="ed-gallery-client-quote">
                        &laquo;<?= e($gQuote) ?>&raquo;
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Controls -->
        <div class="ed-gallery-controls">
            <div class="ed-gallery-dots">
                <?php for ($p = 0; $p < $galTotalPages; $p++): ?>
                    <button type="button" class="ed-gallery-dot<?= $p === 0 ? ' is-active' : '' ?>" data-gal-target="<?= $p ?>" aria-label="صفحه <?= $p + 1 ?>"></button>
                <?php endfor; ?>
            </div>
            <div class="ed-gallery-arrows">
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-prev" aria-label="قبلی">&#10095;</button>
                <?php if ($galBtnText !== ''): ?>
                    <a href="<?= e(resolve_hp_url($galBtnUrl, $lang)) ?>" class="btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.8rem;"><?= e($galBtnText) ?></a>
                <?php endif; ?>
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-next" aria-label="بعدی">&#10094;</button>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php endforeach; ?>
</div>

<script>
window.MORIS_BIO_QUOTES = <?= json_encode($jsBioQuotes, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= e(url('assets/js/editorial-home.js')) ?>"></script>
<?php
require dirname(__DIR__) . '/includes/public_footer.php';
