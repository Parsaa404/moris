<?php
/**
 * Persian home (RTL) — Redesigned Editorial Layout based on reference wireframe.
 * Strictly preserves website design system, branding, typography, header, and footer.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

public_session();

$lang = 'fa';
$dir = 'rtl';
$activeNav = 'home';
$currentPage = '';

$page = get_page('home');
$title = $page ? lang_field($page, 'title', $lang) : SITE_NAME;
$tagline = setting('tagline_fa', 'برتری بدنی · بوکس و تمرین شخصی');
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
        $bookingError = 'نشست شما منقضی شده است. لطفاً صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } elseif (!rate_limit_allow('booking_' . md5(client_ip()), 5, 600)) {
        $bookingError = 'درخواست‌های بیش از حد. لطفاً دقایقی دیگر تلاش فرمایید.';
    } else {
        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
        $userEmail = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 120);
        $userPhone = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30);
        $userGoal = mb_substr(trim((string) ($_POST['goal'] ?? '')), 0, 100);
        $bookDate = mb_substr(trim((string) ($_POST['consultation_date'] ?? '')), 0, 20);
        $bookSlot = mb_substr(trim((string) ($_POST['consultation_slot'] ?? '')), 0, 20);

        if ($name === '') {
            $bookingError = 'لطفاً نام خود را وارد فرمایید.';
        } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $bookingError = 'لطفاً یک ایمیل معتبر وارد فرمایید.';
        } else {
            $consultationDetails = "درخواست مشاوره رایگان\nتاریخ: " . ($bookDate ?: 'تعیین نشده') . "\nساعت ترجیحی: " . ($bookSlot ?: 'انعطاف‌پذیر') . "\nهدف: " . ($userGoal ?: 'تناسب اندام عمومی');
            $fullGoal = 'مشاوره رایگان (' . ($bookDate ?: 'در انتظار تاریخ') . ' ' . ($bookSlot ?: '') . ')';

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
                $bookingError = 'خطایی در ثبت درخواست رخ داد. لطفاً مستقیماً در واتساپ پیام بگذارید.';
            }
        }
    }
}

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — برتری بدنی، بوکس و تمرین شخصی';

require dirname(__DIR__) . '/includes/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/editorial-home.css')) ?>">

<div class="editorial-home">
    <!-- ======================================================================
         Section B: Large Hero Section
         ====================================================================== -->
    <section class="ed-hero-section" id="hero">
        <div class="ed-hero-stage">
            <div class="ed-hero-visual">
                <img src="<?= e(url('assets/images/hero-mori.jpg')) ?>" 
                     alt="موریس بوکس و تمرین شخصی" 
                     class="ed-hero-art"
                     loading="eager">

                <!-- Overlaid Circular Badge -->
                <a href="#basics" class="ed-hero-badge" title="دیدن فلسفه تمرین">
                    <span class="ed-hero-badge-sub">موریس</span>
                    <span class="ed-hero-badge-title">بازگشت به<br>اصول.</span>
                </a>
            </div>
        </div>

        <!-- Hero Bottom Info Strip (3 Supporting Information Badges) -->
        <div class="ed-hero-strip">
            <div class="ed-hero-strip-inner">
                <div class="ed-strip-item">
                    <div class="ed-strip-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
                        </svg>
                    </div>
                    <div>
                        <strong>بیش از ۱۳ سال در رینگ</strong>
                        <span>تجربه مربیگری قهرمانی و تمرینات حرفه‌ای</span>
                    </div>
                </div>

                <div class="ed-strip-item">
                    <div class="ed-strip-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 5v14M18 5v14M2 9h4v6H2zM18 9h4v6h-4zM6 12h12"/>
                        </svg>
                    </div>
                    <div>
                        <strong>تمرین شخصی و بوکس</strong>
                        <span>برنامه‌ریزی قدرتی و بدنسازی اختصاصی ۱-به-۱</span>
                    </div>
                </div>

                <div class="ed-strip-item">
                    <div class="ed-strip-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <strong>کوالالامپور و آنلاین</strong>
                        <span>تمرین وزنه، چربی‌سوزی و ارتقای عملکرد ورزشی</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section C: Introductory Content Section ("Back to Basics")
         ====================================================================== -->
    <section class="ed-basics-section" id="basics">
        <div class="ed-basics-grid">
            <div class="ed-basics-artwork">
                <img src="<?= e(url('assets/images/basics-gym.jpg')) ?>" 
                     alt="اصول تمرین و بدنسازی بوکس" 
                     class="ed-basics-calligraphy"
                     loading="lazy">
            </div>

            <div class="ed-basics-content">
                <span class="ed-basics-eyebrow">EFT · فلسفه و اصول بنیادین</span>
                <h2 class="ed-basics-title">بازگشت به اصول.</h2>
                <p class="ed-basics-lead">
                    همه چیز بر پایه تلاش، تمرکز و تکنیک استوار است. تسلط حقیقی از اصول بنیادین حاصل می‌شود — کنار زدن هیاهو و پرورش قدرت پایدار، هماهنگی عصبی عضلانی و اراده پولادین.
                </p>
                <p class="ed-basics-desc">
                    چه در رینگ بوکس و چه در سالن تمرین، دقت بیومکانیکی و استمرار همیشه پیروز است. هیچ ترفند بیهوده‌ای وجود ندارد؛ هر حرکت با هدف طراحی شده و هر جلسه توانایی‌های شما را گسترش می‌دهد.
                </p>
                <div class="cta-row">
                    <a href="<?= e(url($lang . '/packages.php')) ?>" class="btn-cta">مشاهده پکیج‌ها</a>
                    <a href="#consultation" class="btn-outline">رزرو مشاوره رایگان</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section D: Large Illustrated Content Section ("Where do you want to be?")
         ====================================================================== -->
    <section class="ed-sparring-section" id="sparring">
        <div class="ed-sparring-container">
            <div class="ed-sparring-content">
                <span class="ed-sparring-eyebrow">ماموریت و استراتژی</span>
                <h2 class="ed-sparring-title">کجا می‌خواهید باشید؟</h2>
                <p class="ed-sparring-text">
                    تمرین شخصی یک‌به‌یک یا دوره‌های تخصصی گروهی، منحصراً منطبق بر ساختار فیزیولوژیک و آرمان‌های بدنی شما.
                </p>
                <ul class="ed-sparring-features">
                    <li class="ed-sparring-feature-item">تمرینات با وزنه دقیق بر اساس بیومکانیک و اصلاح ساختار بدنی</li>
                    <li class="ed-sparring-feature-item">اصول کاربردی و واقعی بوکس: جاپایی، دفاع، واکنش و قدرت ضربه</li>
                    <li class="ed-sparring-feature-item">ترکیب بدنی پایدار، چربی‌سوزی علمی و فرم‌دهی عضلانی</li>
                    <li class="ed-sparring-feature-item">پیشگیری از آسیب‌دیدگی مفاصل با مدیریت اصولی بار تمرینی</li>
                </ul>
                <a href="#consultation" class="btn-cta">رزرو جلسه تمرینی</a>
            </div>

            <div class="ed-sparring-artwork">
                <img src="<?= e(url('assets/images/sparring-boxers.jpg')) ?>" 
                     alt="تمرین بوکس و میت‌گیری حرفه‌ای" 
                     class="ed-sparring-art"
                     loading="lazy">
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section E: Consultation or Primary Action Section
         ("Start a chat" + "Book Your Free Consultation")
         ====================================================================== -->
    <section class="ed-booking-section" id="consultation">
        <div class="ed-booking-inner">
            <!-- Top Subsection: "Start a chat." -->
            <div class="ed-chat-bar">
                <h2 class="ed-chat-title">شروع گفتگو</h2>
                <div class="ed-chat-options">
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <h3>تماس تلفنی</h3>
                        <p><?= e($phone) ?></p>
                        <span class="ed-chat-btn-label">تماس بگیرید</span>
                    </a>

                    <a href="mailto:<?= e($email) ?>" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <h3>ارسال ایمیل</h3>
                        <p><?= e($email) ?></p>
                        <span class="ed-chat-btn-label">ارسال ایمیل</span>
                    </a>

                    <a href="https://wa.me/<?= e($whatsappText) ?>" target="_blank" rel="noopener" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.5-1.3-.7-1.8-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A3 3 0 0 0 6.3 9c0 1.8 1.3 3.5 1.5 3.7s2.5 3.9 6.1 5.2c2.2.8 2.6.6 3.1.6s1.4-.6 1.6-1.1.2-1 .1-1.1-.3-.2-.5-.3z"/>
                            </svg>
                        </div>
                        <h3>پیام در واتساپ</h3>
                        <p>پاسخ‌گویی مستقیم و سریع</p>
                        <span class="ed-chat-btn-label">ارسال پیام</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Subsection: "Book Your Free Consultation:" with Calendar -->
            <div class="ed-calendar-wrapper">
                <div class="ed-calendar-heading-row">
                    <h3 class="ed-calendar-title">رزرو مشاوره رایگان:</h3>
                    <p class="ed-calendar-subtitle">روز و ساعت دلخواه خود را برای بررسی نیازهای بدنی و اهداف تمرینی انتخاب فرمایید.</p>
                </div>

                <?php if ($bookingSuccess): ?>
                    <div class="ed-booking-feedback is-success">
                        <strong>درخواست شما با موفقیت ثبت شد!</strong> مربی موری به زودی برای هماهنگی نهایی با شما تماس خواهد گرفت.
                    </div>
                <?php elseif ($bookingError !== ''): ?>
                    <div class="ed-booking-feedback is-error">
                        <?= e($bookingError) ?>
                    </div>
                <?php endif; ?>

                <div class="ed-booking-grid-layout">
                    <!-- Interactive Calendar View -->
                    <div class="ed-calendar-box">
                        <div class="ed-cal-header">
                            <div class="ed-cal-month-title" id="ed-cal-month-title">اکتبر ۲۰۲۶</div>
                            <div class="ed-cal-nav">
                                <button type="button" class="ed-cal-arrow" id="ed-cal-prev" aria-label="ماه قبل">&rsaquo;</button>
                                <button type="button" class="ed-cal-arrow" id="ed-cal-next" aria-label="ماه بعد">&lsaquo;</button>
                            </div>
                        </div>

                        <div class="ed-cal-weekdays">
                            <span>دو</span><span>سه‌</span><span>چهار</span><span>پنج</span><span>جمعه</span><span>شنبه</span><span>یک</span>
                        </div>

                        <div class="ed-cal-days" id="ed-cal-days">
                            <!-- Populated dynamically via editorial-home.js -->
                        </div>
                    </div>

                    <!-- Consultation Booking Form -->
                    <form method="post" action="#consultation" class="ed-booking-form-box">
                        <?= form_field() ?>
                        <input type="hidden" name="action" value="book_consultation">
                        <input type="hidden" name="consultation_date" id="ed-selected-date" value="<?= date('Y-m-d') ?>">
                        <input type="hidden" name="consultation_slot" id="ed-selected-slot" value="۰۹:۰۰ صبح">

                        <div>
                            <span class="ed-slots-label">انتخاب ساعت ترجیحی:</span>
                            <div class="ed-slots-grid">
                                <div class="ed-slot-pill is-selected" data-time="۰۹:۰۰ صبح">۰۹:۰۰ صبح</div>
                                <div class="ed-slot-pill" data-time="۱۱:۰۰ صبح">۱۱:۰۰ صبح</div>
                                <div class="ed-slot-pill" data-time="۰۲:۰۰ بعد از ظهر">۰۲:۰۰ بعد از ظهر</div>
                                <div class="ed-slot-pill" data-time="۰۵:۰۰ عصر">۰۵:۰۰ عصر</div>
                                <div class="ed-slot-pill" data-time="۰۷:۰۰ شب">۰۷:۰۰ شب</div>
                                <div class="ed-slot-pill" data-time="انعطاف‌پذیر">انعطاف‌پذیر</div>
                            </div>
                        </div>

                        <div class="ed-form-field">
                            <label for="book-name">نام و نام خانوادگی *</label>
                            <input type="text" id="book-name" name="name" class="ed-form-input" required placeholder="مثال: علی رضایی">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-email">ایمیل *</label>
                            <input type="email" id="book-email" name="email" class="ed-form-input" required placeholder="name@example.com">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-phone">شماره تلفن / واتساپ</label>
                            <input type="tel" id="book-phone" name="phone" class="ed-form-input" placeholder="+98 یا +60 ...">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-goal">هدف اصلی تمرینی</label>
                            <select id="book-goal" name="goal" class="ed-form-select">
                                <option value="تکنیک و آمادگی بوکس">تکنیک و آمادگی بوکس</option>
                                <option value="کاهش چربی و تناسب اندام">کاهش چربی و تناسب اندام</option>
                                <option value="عضله‌سازی و افزایش قدرت">عضله‌سازی و افزایش قدرت</option>
                                <option value="تمرین شخصی یک‌به‌یک">تمرین شخصی یک‌به‌یک</option>
                                <option value="مربیگری و برنامه آنلاین">مربیگری و برنامه آنلاین</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-cta ed-booking-submit">تایید درخواست مشاوره رایگان</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section F: About or Introduction Section ("Get to know me.")
         ====================================================================== -->
    <section class="ed-bio-section" id="bio">
        <div class="ed-bio-container">
            <!-- Left: Visual Illustration + Testimonial Badge -->
            <div class="ed-bio-visual-col">
                <img src="<?= e(url('assets/images/coach-mori.jpg')) ?>" 
                     alt="طرح پرتره مربی موریس" 
                     class="ed-bio-artwork"
                     loading="lazy">

                <!-- Testimonial Quote under coach illustration -->
                <div class="ed-bio-quote-card">
                    <p class="ed-bio-quote-text" id="ed-bio-quote-text">
                        «بسیار حرفه‌ای و با رویکردی کاملاً علمی و منطقی نسبت به ورزش و تمرینات بدنی.»
                    </p>
                    <div class="ed-bio-quote-author" id="ed-bio-quote-author">
                        هنری جیمز — مدیر و ورزشکار بوکس
                    </div>
                    <div class="ed-bio-dots">
                        <button type="button" class="ed-bio-dot is-active" aria-label="نظر ۱"></button>
                        <button type="button" class="ed-bio-dot" aria-label="نظر ۲"></button>
                        <button type="button" class="ed-bio-dot" aria-label="نظر ۳"></button>
                    </div>
                </div>
            </div>

            <!-- Right: Bio Narrative Copy -->
            <div class="ed-bio-content">
                <span class="ed-bio-eyebrow">بیوگرافی کوتاه</span>
                <h2 class="ed-bio-title">
                    درباره من بدانید.
                    <span class="farsi-subtitle">(موری — به معنی لبخند در زبان فارسی)</span>
                </h2>

                <div class="ed-bio-narrative">
                    <p>
                        از دوران نوجوانی، دستیابی به اوج آمادگی بدنی همواره اشتیاق و انگیزه قلبی من بوده است.
                    </p>
                    <p>
                        طی سال‌ها، استراتژی‌های گوناگونی را مطالعه و آزموده‌ام و دریافتم که خود مسیر، به اندازه مقصد ارزشمند است و داشتن یک برنامه علمی و فکورانه از اهمیتی بنیادین برخوردار است.
                    </p>
                    <p>
                        از این رو، با اطمینان باور دارم که با دانش و تجارب دست‌اول خود، شما را راهنمایی خواهم کرد تا سریع‌تر و بدون خطر آسیب‌دیدگی، از طریق برنامه‌های اختصاصی منطبق با توانایی‌ها و نیازهای فردی‌تان به اهداف خود برسید.
                    </p>
                    <p>
                        با بیش از ۱۳ سال سابقه تمرین و مربیگری، متدی اثبات‌شده و نتیجه‌محور پدید آورده‌ام. نتایج شاگردانم و بازخوردهای آنان، درستی این روش را به اثبات رسانده است.
                    </p>
                    <p>
                        به من اجازه دهید تا در دستیابی به آماده‌ترین و قدرتمندترین نسخه بدنتان در کنارتان باشم.
                    </p>
                </div>

                <div class="ed-bio-btn-row">
                    <a href="<?= e(url($lang . '/about.php')) ?>" class="btn-outline">اطلاعات بیشتر درباره موریس</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section G: Gallery or Visual Showcase ("Our Gallery")
         ====================================================================== -->
    <section class="ed-gallery-section" id="gallery">
        <div class="ed-gallery-header">
            <h2 class="ed-gallery-title">گالری نتایج</h2>
            <p class="ed-gallery-subtitle">مشاهده تغییرات و دستاوردهای شاگردان موری</p>
        </div>

        <div class="ed-gallery-cards-grid">
            <!-- Card 1: Sara -->
            <div class="ed-gallery-card">
                <div class="ed-gallery-media-wrapper">
                    <img src="<?= e(url('assets/images/gallery-sara.jpg')) ?>" 
                         alt="سارا - تغییرات بوکس و استقامت" 
                         class="ed-gallery-image"
                         loading="lazy">
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name">سارا</h3>
                        <span class="ed-gallery-client-tag">بوکس و چابکی</span>
                    </div>
                    <p class="ed-gallery-client-quote">
                        «تمرین بوکس با موری استقامت، سرعت عمل و تمرکز ذهنی من را به کلی متحول کرد. تأکید روی اجرای درست تکنیک‌ها باعث می‌شود در هر راند حس قدرت و پیشرفت داشته باشم.»
                    </p>
                </div>
            </div>

            <!-- Card 2: Megan -->
            <div class="ed-gallery-card">
                <div class="ed-gallery-media-wrapper">
                    <img src="<?= e(url('assets/images/gallery-megan.jpg')) ?>" 
                         alt="مگان - کاهش چربی و تناسب اندام" 
                         class="ed-gallery-image"
                         loading="lazy">
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name">مگان</h3>
                        <span class="ed-gallery-client-tag">کاهش چربی و افزایش قدرت</span>
                    </div>
                    <p class="ed-gallery-client-quote">
                        «۱۱ کیلوگرم کاهش وزن و ساخت فرم عضلانی پایدار برای پشت و شکم. موری برنامه‌ای دقیق متناسب با مشغله‌های روزمره من تنظیم کرد. بهترین مربی در کوالالامپور.»
                    </p>
                </div>
            </div>
        </div>

        <!-- Gallery Controls -->
        <div class="ed-gallery-controls">
            <div class="ed-gallery-dots">
                <button type="button" class="ed-gallery-dot is-active" aria-label="صفحه ۱"></button>
                <button type="button" class="ed-gallery-dot" aria-label="صفحه ۲"></button>
                <button type="button" class="ed-gallery-dot" aria-label="صفحه ۳"></button>
            </div>
            <div class="ed-gallery-arrows">
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-prev" aria-label="مورد قبلی">&#10095;</button>
                <a href="<?= e(url($lang . '/gallery.php')) ?>" class="btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.8rem;">مشاهده کل تصاویر</a>
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-next" aria-label="مورد بعدی">&#10094;</button>
            </div>
        </div>
    </section>
</div>

<script src="<?= e(url('assets/js/editorial-home.js')) ?>"></script>
<?php
require dirname(__DIR__) . '/includes/public_footer.php';
