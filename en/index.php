<?php
/**
 * English home — Redesigned Editorial Layout based on reference wireframe.
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

$lang = 'en';
$dir = 'ltr';
$activeNav = 'home';
$currentPage = '';

$page = get_page('home');
$title = $page ? lang_field($page, 'title', $lang) : SITE_NAME;
$tagline = setting('tagline_en', 'Physical Excellence · Boxing & Personal Training');
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
        $bookingError = 'Your session expired. Please refresh and try again.';
    } elseif (!rate_limit_allow('booking_' . md5(client_ip()), 5, 600)) {
        $bookingError = 'Too many requests. Please wait a few minutes and try again.';
    } else {
        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
        $userEmail = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 120);
        $userPhone = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30);
        $userGoal = mb_substr(trim((string) ($_POST['goal'] ?? '')), 0, 100);
        $bookDate = mb_substr(trim((string) ($_POST['consultation_date'] ?? '')), 0, 20);
        $bookSlot = mb_substr(trim((string) ($_POST['consultation_slot'] ?? '')), 0, 20);
        $honeypot = trim((string) ($_POST['website'] ?? ''));

        if ($name === '') {
            $bookingError = 'Please enter your name.';
        } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $bookingError = 'Please enter a valid email address.';
        } elseif ($honeypot !== '') {
            $bookingSuccess = true;
        } else {
            $consultationDetails = "Free Consultation Booking\nDate: " . ($bookDate ?: 'Flexible') . "\nPreferred Time: " . ($bookSlot ?: 'Flexible') . "\nGoal: " . ($userGoal ?: 'General Fitness');
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
                $bookingError = 'An error occurred while saving your request. Please message directly on WhatsApp.';
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
        'quote_en' => 'Professional and utilizes a scientific approach to training.',
        'author_en' => 'Henry James — Executive & Boxer'
    ],
    [
        'quote_en' => 'In 6 months my power and agility skyrocketed without any joint pain.',
        'author_en' => 'Farhad K. — Software Architect'
    ],
    [
        'quote_en' => 'Clear instructions, dedicated attention, and actual results.',
        'author_en' => 'Sarah M. — Triathlete'
    ]
];
$rawBioQuotes = hp_json($hp, 'bio', 'quotes', $defaultBioQuotes);
$jsBioQuotes = [];
foreach ($rawBioQuotes as $q) {
    $qText = $q['quote_en'] ?? '';
    $qAuth = $q['author_en'] ?? '';
    if ($qText !== '') {
        $jsBioQuotes[] = [
            'text' => '“' . $qText . '”',
            'author' => $qAuth,
        ];
    }
}

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — Physical Excellence, Boxing & Personal Training';

require dirname(__DIR__) . '/includes/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/editorial-home.css')) ?>">

<div class="editorial-home">
<?php foreach ($sections as $sec): 
    $secKey = $sec['section_key'] ?? '';
?>

    <?php if ($secKey === 'hero'): 
        $heroHeadline = hp_field($hp, 'hero', 'headline', $lang, 'EARN IT.');
        $tag1 = hp_field($hp, 'hero', 'tagline_1', $lang, 'Your Confidence,');
        $tag2 = hp_field($hp, 'hero', 'tagline_2', $lang, 'Your Strength,');
        $tag3 = hp_field($hp, 'hero', 'tagline_3', $lang, 'Your Walk.');
        $bgLtr = hp_field($hp, 'hero', 'bg_image', 'en', 'assets/images/hero-earn-it.webp');
        $bgRtl = hp_field($hp, 'hero', 'bg_image_rtl', 'en', 'assets/images/hero-earn-it-rtl.webp');

        $defaultHighlights = [
            [
                'icon' => 'award',
                'title_en' => '13+ Years in the Ring',
                'desc_en' => 'Championship coaching & competitive experience',
                'link' => '#bio'
            ],
            [
                'icon' => 'dumbbell',
                'title_en' => 'Personal Training & Boxing',
                'desc_en' => 'Tailored 1-on-1 strength & conditioning',
                'link' => '#sparring'
            ],
            [
                'icon' => 'map-pin',
                'title_en' => 'Kuala Lumpur & Online',
                'desc_en' => 'Weight training, fat loss & athletic programming',
                'link' => '#consultation'
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
         Section B: Cinematic Full-Width Hero Section (Reference: EARN IT.)
         ====================================================================== -->
    <section class="ed-hero-section" id="hero" style="<?= $heroStyle ?>">
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
                    $hlTitle = $hl['title_en'] ?? '';
                    $hlDesc = $hl['desc_en'] ?? '';
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
        $basicsEyebrow = hp_field($hp, 'basics', 'eyebrow', $lang, 'EFT · Philosophy & Foundations');
        $basicsTitle = hp_field($hp, 'basics', 'title', $lang, 'Back to basics.');
        $basicsLead = hp_field($hp, 'basics', 'lead', $lang, "It's about EFT (Effort, Focus and Technique). Mastery comes from the fundamentals — stripping away the noise and building true strength, coordination, and mental toughness.");
        $basicsDesc = hp_field($hp, 'basics', 'desc', $lang, "Whether in the ring or on the gym floor, precision and consistency always win. No gimmicks, no cookie-cutter templates. Every movement has purpose, every round builds capability, and every workout is tailored to your real physiological adaptation.");
        $btn1Text = hp_field($hp, 'basics', 'btn1_text', $lang, 'Explore Packages');
        $btn1Url = hp_field($hp, 'basics', 'btn1_url', $lang, 'packages.php');
        $btn2Text = hp_field($hp, 'basics', 'btn2_text', $lang, 'Book Free Consultation');
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
        $sparringEyebrow = hp_field($hp, 'sparring', 'eyebrow', $lang, 'Philosophy / Mission');
        $sparringTitle = hp_field($hp, 'sparring', 'title', $lang, 'Where do you want to be?');
        $sparringText = hp_field($hp, 'sparring', 'text', $lang, '1-1 Personal Training or Group Training in dedicated sessions. Tailored for your specific physiological needs and athletic aspirations.');
        $sparringImg = hp_field($hp, 'sparring', 'image', $lang, 'assets/images/sparring-boxers.jpg');
        $defaultFeaturesStr = "Biomechanically sound weight training & posture correction\nAuthentic boxing fundamentals: footwork, head movement & power\nSustainable body recomposition & accelerated fat loss\nInjury prevention with progressive load management";
        $sparringFeatures = parse_features(hp_field($hp, 'sparring', 'features', $lang, $defaultFeaturesStr));
        $sparringBtnText = hp_field($hp, 'sparring', 'btn_text', $lang, 'Book a Session');
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
        $chatTitle = hp_field($hp, 'booking', 'chat_title', $lang, 'Start a chat.');
        $defaultChatCards = [
            [
                'icon' => 'phone',
                'title_en' => 'Phone or Call',
                'val_en' => $phone,
                'btn_en' => 'Call Now',
                'link' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone)
            ],
            [
                'icon' => 'mail',
                'title_en' => 'Send an Email',
                'val_en' => $email,
                'btn_en' => 'Send Email',
                'link' => 'mailto:' . $email
            ],
            [
                'icon' => 'whatsapp',
                'title_en' => 'WhatsApp Chat',
                'val_en' => 'Instant direct reply',
                'btn_en' => 'Message',
                'link' => 'https://wa.me/' . $whatsappText
            ]
        ];
        $chatCards = hp_json($hp, 'booking', 'chat_cards', $defaultChatCards);
        $calTitle = hp_field($hp, 'booking', 'calendar_title', $lang, 'Book Your Free Consultation:');
        $calSubtitle = hp_field($hp, 'booking', 'calendar_subtitle', $lang, 'Select an available date and time slot to discuss your training goals and schedule.');
        $timeSlots = parse_features(hp_field($hp, 'booking', 'time_slots', $lang, "09:00 AM\n11:00 AM\n02:00 PM\n05:00 PM\n07:00 PM\nFlexible"));
        $submitBtnText = hp_field($hp, 'booking', 'submit_btn_text', $lang, 'Confirm Consultation Request');
        $firstSlot = $timeSlots[0] ?? '09:00 AM';
    ?>
    <!-- ======================================================================
         Section E: Consultation or Primary Action Section
         ("Start a chat" + "Book Your Free Consultation")
         ====================================================================== -->
    <section class="ed-booking-section" id="consultation">
        <div class="ed-booking-inner">
            <!-- Top Subsection: "Start a chat." -->
            <div class="ed-chat-bar">
                <h2 class="ed-chat-title"><?= e($chatTitle) ?></h2>
                <div class="ed-chat-options">
                    <?php foreach ($chatCards as $card): 
                        $cTitle = $card['title_en'] ?? '';
                        $cVal = $card['val_en'] ?? '';
                        $cBtn = $card['btn_en'] ?? '';
                        $cLink = sanitize_cms_url((string) ($card['link'] ?? '#')) ?: '#';
                        $cIcon = $card['icon'] ?? 'phone';
                        $isExt = str_starts_with($cLink, 'http');
                    ?>
                    <a href="<?= e($cLink) ?>" <?= $isExt ? 'target="_blank" rel="noopener"' : '' ?> class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <?= render_chat_icon($cIcon) ?>
                        </div>
                        <h3><?= e($cTitle) ?></h3>
                        <p><?= e($cVal) ?></p>
                        <span class="ed-chat-btn-label"><?= e($cBtn) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Bottom Subsection: "Book Your Free Consultation:" with Calendar -->
            <div class="ed-calendar-wrapper">
                <div class="ed-calendar-heading-row">
                    <h3 class="ed-calendar-title"><?= e($calTitle) ?></h3>
                    <p class="ed-calendar-subtitle"><?= e($calSubtitle) ?></p>
                </div>

                <?php if ($bookingSuccess): ?>
                    <div class="ed-booking-feedback is-success">
                        <strong>Thank you!</strong> Your consultation request has been submitted. Coach Mori will confirm your session shortly.
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
                            <div class="ed-cal-month-title" id="ed-cal-month-title">October 2026</div>
                            <div class="ed-cal-nav">
                                <button type="button" class="ed-cal-arrow" id="ed-cal-prev" aria-label="Previous Month">&lsaquo;</button>
                                <button type="button" class="ed-cal-arrow" id="ed-cal-next" aria-label="Next Month">&rsaquo;</button>
                            </div>
                        </div>

                        <div class="ed-cal-weekdays">
                            <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
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
                        <input type="hidden" name="consultation_slot" id="ed-selected-slot" value="<?= e($firstSlot) ?>">
                        <div class="hp-field" aria-hidden="true">
                            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div>
                            <span class="ed-slots-label">Select Preferred Time Slot:</span>
                            <div class="ed-slots-grid">
                                <?php foreach ($timeSlots as $sIdx => $slot): ?>
                                    <div class="ed-slot-pill <?= $sIdx === 0 ? 'is-selected' : '' ?>" data-time="<?= e($slot) ?>"><?= e($slot) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="ed-form-field">
                            <label for="book-name">Your Name *</label>
                            <input type="text" id="book-name" name="name" class="ed-form-input" required placeholder="e.g. Alex Morgan">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-email">Email Address *</label>
                            <input type="email" id="book-email" name="email" class="ed-form-input" required placeholder="alex@example.com">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-phone">Phone / WhatsApp</label>
                            <input type="tel" id="book-phone" name="phone" class="ed-form-input" placeholder="+60 ...">
                        </div>

                        <div class="ed-form-field">
                            <label for="book-goal">Primary Goal</label>
                            <select id="book-goal" name="goal" class="ed-form-select">
                                <option value="Boxing Technique & Fitness">Boxing Technique &amp; Fitness</option>
                                <option value="Fat Loss & Body Conditioning">Fat Loss &amp; Body Conditioning</option>
                                <option value="Strength & Muscle Building">Strength &amp; Muscle Building</option>
                                <option value="1-on-1 Personal Training">1-on-1 Personal Training</option>
                                <option value="Online Coaching">Online Coaching</option>
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
        $bioEyebrow = hp_field($hp, 'bio', 'eyebrow', $lang, 'A Brief Bio');
        $bioTitle = hp_field($hp, 'bio', 'title', $lang, 'Get to know me.');
        $bioSubtitle = hp_field($hp, 'bio', 'subtitle', $lang, '(Mori — smile in Persian)');
        $defaultNarrative = "Since my teenage years, physical excellence has always been my passion.\n\nOver the years, I have studied and tried many strategies to achieve it, realizing that the journey itself is as valuable as the destination and that having an intellectually sound plan in place is of paramount importance.\n\nTherefore, I am confident in stating that with my knowledge and first-hand experience, I will guide you to reach your goals faster and injury-free by creating bespoke programs tailored to your personal needs and current limitations.\n\nWith over 13 years of training and coaching experience, I have developed a tested, results-focused method. My clients' results and feedback have proven its efficacy.\n\nLet me help you unlock the fittest version of yourself.";
        $bioNarrative = hp_field($hp, 'bio', 'narrative', $lang, $defaultNarrative);
        $bioBtnText = hp_field($hp, 'bio', 'btn_text', $lang, 'Learn More About Mori');
        $bioBtnUrl = hp_field($hp, 'bio', 'btn_url', $lang, 'about.php');
        $paragraphs = preg_split('/\r\n\r\n|\n\n/', trim($bioNarrative)) ?: [];
        $firstQuote = $jsBioQuotes[0] ?? [
            'text' => '“Professional and utilizes a scientific approach to training.”',
            'author' => 'Henry James — Executive & Boxer'
        ];
    ?>
    <!-- ======================================================================
         Section F: About or Introduction Section ("Get to know me.")
         ====================================================================== -->
    <section class="ed-bio-section" id="bio">
        <div class="ed-bio-container">
            <!-- Left: Visual Illustration + Testimonial Badge -->
            <div class="ed-bio-visual-col">
                <img src="<?= e(url($bioImg)) ?>" 
                     alt="Coach Mori Portrait" 
                     class="ed-bio-artwork"
                     loading="lazy">

                <!-- Testimonial Quote under coach illustration -->
                <div class="ed-bio-quote-card">
                    <p class="ed-bio-quote-text" id="ed-bio-quote-text">
                        <?= e($firstQuote['text']) ?>
                    </p>
                    <div class="ed-bio-quote-author" id="ed-bio-quote-author">
                        <?= e($firstQuote['author']) ?>
                    </div>
                    <div class="ed-bio-dots">
                        <?php foreach ($jsBioQuotes as $qIdx => $qItem): ?>
                            <button type="button" class="ed-bio-dot <?= $qIdx === 0 ? 'is-active' : '' ?>" aria-label="Quote <?= $qIdx + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Bio Narrative Copy -->
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
        $galTitle = hp_field($hp, 'gallery', 'title', $lang, 'Our Gallery');
        $galSubtitle = hp_field($hp, 'gallery', 'subtitle', $lang, 'Check out our clients results and transformations');
        $defaultGalItems = [
            [
                'image' => 'assets/images/gallery-sara.jpg',
                'name_en' => 'Sara',
                'tag_en' => 'Boxing & Conditioning',
                'quote_en' => 'Boxing training with Mori completely transformed my stamina, reaction speed, and mental focus. His technique-first methodology makes every single round challenging and rewarding.'
            ],
            [
                'image' => 'assets/images/gallery-megan.jpg',
                'name_en' => 'Megan',
                'tag_en' => 'Fat Loss & Strength',
                'quote_en' => 'Lost 11kg and gained incredible core and back definition. Mori designed a structured strength regimen that worked seamlessly with my demanding schedule. Best trainer in KL.'
            ]
        ];
        $galItems = hp_json($hp, 'gallery', 'items', $defaultGalItems);
        $galBtnText = hp_field($hp, 'gallery', 'btn_text', $lang, 'View Full Gallery');
        $galBtnUrl = hp_field($hp, 'gallery', 'btn_url', $lang, 'gallery.php');
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
            <?php foreach ($galItems as $gCard): 
                $gImg = $gCard['image'] ?? 'assets/images/gallery-sara.jpg';
                $gName = $gCard['name_en'] ?? '';
                $gTag = $gCard['tag_en'] ?? '';
                $gQuote = $gCard['quote_en'] ?? '';
            ?>
            <div class="ed-gallery-card">
                <div class="ed-gallery-media-wrapper">
                    <img src="<?= e(url($gImg)) ?>" 
                         alt="Client <?= e($gName) ?>" 
                         class="ed-gallery-image"
                         loading="lazy">
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name"><?= e($gName) ?></h3>
                        <span class="ed-gallery-client-tag"><?= e($gTag) ?></span>
                    </div>
                    <p class="ed-gallery-client-quote">
                        &ldquo;<?= e($gQuote) ?>&rdquo;
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Controls -->
        <div class="ed-gallery-controls">
            <div class="ed-gallery-dots">
                <button type="button" class="ed-gallery-dot is-active" aria-label="Page 1"></button>
                <button type="button" class="ed-gallery-dot" aria-label="Page 2"></button>
                <button type="button" class="ed-gallery-dot" aria-label="Page 3"></button>
            </div>
            <div class="ed-gallery-arrows">
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-prev" aria-label="Previous Showcase">&#10094;</button>
                <?php if ($galBtnText !== ''): ?>
                    <a href="<?= e(resolve_hp_url($galBtnUrl, $lang)) ?>" class="btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.8rem;"><?= e($galBtnText) ?></a>
                <?php endif; ?>
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-next" aria-label="Next Showcase">&#10095;</button>
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
