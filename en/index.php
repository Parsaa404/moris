<?php
/**
 * English home — Redesigned Editorial Layout based on reference wireframe.
 * Strictly preserves website design system, branding, typography, header, and footer.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

public_session();

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

        if ($name === '') {
            $bookingError = 'Please enter your name.';
        } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $bookingError = 'Please enter a valid email address.';
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

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — Physical Excellence, Boxing & Personal Training';

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
                     alt="Mori Boxing & Physical Excellence" 
                     class="ed-hero-art"
                     loading="eager">

                <!-- Overlaid Circular Badge -->
                <a href="#basics" class="ed-hero-badge" title="Jump to Philosophy">
                    <span class="ed-hero-badge-sub">Mori</span>
                    <span class="ed-hero-badge-title">Back to<br>basics.</span>
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
                        <strong>13+ Years in the Ring</strong>
                        <span>Championship coaching &amp; competitive experience</span>
                    </div>
                </div>

                <div class="ed-strip-item">
                    <div class="ed-strip-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 5v14M18 5v14M2 9h4v6H2zM18 9h4v6h-4zM6 12h12"/>
                        </svg>
                    </div>
                    <div>
                        <strong>Personal Training &amp; Boxing</strong>
                        <span>Tailored 1-on-1 strength &amp; conditioning</span>
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
                        <strong>Kuala Lumpur &amp; Online</strong>
                        <span>Weight training, fat loss &amp; athletic programming</span>
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
                     alt="Boxing Gym & Physical Fundamentals" 
                     class="ed-basics-calligraphy"
                     loading="lazy">
            </div>

            <div class="ed-basics-content">
                <span class="ed-basics-eyebrow">EFT · Philosophy &amp; Foundations</span>
                <h2 class="ed-basics-title">Back to basics.</h2>
                <p class="ed-basics-lead">
                    It's about EFT (Effort, Focus and Technique). Mastery comes from the fundamentals — stripping away the noise and building true strength, coordination, and mental toughness.
                </p>
                <p class="ed-basics-desc">
                    Whether in the ring or on the gym floor, precision and consistency always win. No gimmicks, no cookie-cutter templates. Every movement has purpose, every round builds capability, and every workout is tailored to your real physiological adaptation.
                </p>
                <div class="cta-row">
                    <a href="<?= e(url($lang . '/packages.php')) ?>" class="btn-cta">Explore Packages</a>
                    <a href="#consultation" class="btn-outline">Book Free Consultation</a>
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
                <span class="ed-sparring-eyebrow">Philosophy / Mission</span>
                <h2 class="ed-sparring-title">Where do you want to be?</h2>
                <p class="ed-sparring-text">
                    1-1 Personal Training or Group Training in dedicated sessions. Tailored for your specific physiological needs and athletic aspirations.
                </p>
                <ul class="ed-sparring-features">
                    <li class="ed-sparring-feature-item">Biomechanically sound weight training &amp; posture correction</li>
                    <li class="ed-sparring-feature-item">Authentic boxing fundamentals: footwork, head movement &amp; power</li>
                    <li class="ed-sparring-feature-item">Sustainable body recomposition &amp; accelerated fat loss</li>
                    <li class="ed-sparring-feature-item">Injury prevention with progressive load management</li>
                </ul>
                <a href="#consultation" class="btn-cta">Book a Session</a>
            </div>

            <div class="ed-sparring-artwork">
                <img src="<?= e(url('assets/images/sparring-boxers.jpg')) ?>" 
                     alt="Boxing Sparring & Padwork Coaching" 
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
                <h2 class="ed-chat-title">Start a chat.</h2>
                <div class="ed-chat-options">
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <h3>Phone or Call</h3>
                        <p><?= e($phone) ?></p>
                        <span class="ed-chat-btn-label">Call Now</span>
                    </a>

                    <a href="mailto:<?= e($email) ?>" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <h3>Send an Email</h3>
                        <p><?= e($email) ?></p>
                        <span class="ed-chat-btn-label">Send Email</span>
                    </a>

                    <a href="https://wa.me/<?= e($whatsappText) ?>" target="_blank" rel="noopener" class="ed-chat-card">
                        <div class="ed-chat-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.5-1.3-.7-1.8-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A3 3 0 0 0 6.3 9c0 1.8 1.3 3.5 1.5 3.7s2.5 3.9 6.1 5.2c2.2.8 2.6.6 3.1.6s1.4-.6 1.6-1.1.2-1 .1-1.1-.3-.2-.5-.3z"/>
                            </svg>
                        </div>
                        <h3>WhatsApp Chat</h3>
                        <p>Instant direct reply</p>
                        <span class="ed-chat-btn-label">Message</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Subsection: "Book Your Free Consultation:" with Calendar -->
            <div class="ed-calendar-wrapper">
                <div class="ed-calendar-heading-row">
                    <h3 class="ed-calendar-title">Book Your Free Consultation:</h3>
                    <p class="ed-calendar-subtitle">Select an available date and time slot to discuss your training goals and schedule.</p>
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
                        <input type="hidden" name="consultation_slot" id="ed-selected-slot" value="09:00 AM">

                        <div>
                            <span class="ed-slots-label">Select Preferred Time Slot:</span>
                            <div class="ed-slots-grid">
                                <div class="ed-slot-pill is-selected" data-time="09:00 AM">09:00 AM</div>
                                <div class="ed-slot-pill" data-time="11:00 AM">11:00 AM</div>
                                <div class="ed-slot-pill" data-time="02:00 PM">02:00 PM</div>
                                <div class="ed-slot-pill" data-time="05:00 PM">05:00 PM</div>
                                <div class="ed-slot-pill" data-time="07:00 PM">07:00 PM</div>
                                <div class="ed-slot-pill" data-time="Flexible">Flexible</div>
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

                        <button type="submit" class="btn-cta ed-booking-submit">Confirm Consultation Request</button>
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
                     alt="Coach Mori Portrait" 
                     class="ed-bio-artwork"
                     loading="lazy">

                <!-- Testimonial Quote under coach illustration -->
                <div class="ed-bio-quote-card">
                    <p class="ed-bio-quote-text" id="ed-bio-quote-text">
                        &ldquo;Professional and utilizes a scientific approach to training.&rdquo;
                    </p>
                    <div class="ed-bio-quote-author" id="ed-bio-quote-author">
                        Henry James — Executive &amp; Boxer
                    </div>
                    <div class="ed-bio-dots">
                        <button type="button" class="ed-bio-dot is-active" aria-label="Quote 1"></button>
                        <button type="button" class="ed-bio-dot" aria-label="Quote 2"></button>
                        <button type="button" class="ed-bio-dot" aria-label="Quote 3"></button>
                    </div>
                </div>
            </div>

            <!-- Right: Bio Narrative Copy -->
            <div class="ed-bio-content">
                <span class="ed-bio-eyebrow">A Brief Bio</span>
                <h2 class="ed-bio-title">
                    Get to know me.
                    <span class="farsi-subtitle">(Mori — smile in Persian)</span>
                </h2>

                <div class="ed-bio-narrative">
                    <p>
                        Since my teenage years, physical excellence has always been my passion.
                    </p>
                    <p>
                        Over the years, I have studied and tried many strategies to achieve it, realizing that the journey itself is as valuable as the destination and that having an intellectually sound plan in place is of paramount importance.
                    </p>
                    <p>
                        Therefore, I am confident in stating that with my knowledge and first-hand experience, I will guide you to reach your goals faster and injury-free by creating bespoke programs tailored to your personal needs and current limitations.
                    </p>
                    <p>
                        With over 13 years of training and coaching experience, I have developed a tested, results-focused method. My clients' results and feedback have proven its efficacy.
                    </p>
                    <p>
                        Let me help you unlock the fittest version of yourself.
                    </p>
                </div>

                <div class="ed-bio-btn-row">
                    <a href="<?= e(url($lang . '/about.php')) ?>" class="btn-outline">Learn More About Mori</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         Section G: Gallery or Visual Showcase ("Our Gallery")
         ====================================================================== -->
    <section class="ed-gallery-section" id="gallery">
        <div class="ed-gallery-header">
            <h2 class="ed-gallery-title">Our Gallery</h2>
            <p class="ed-gallery-subtitle">Check out our clients results and transformations</p>
        </div>

        <div class="ed-gallery-cards-grid">
            <!-- Card 1: Sara -->
            <div class="ed-gallery-card">
                <div class="ed-gallery-media-wrapper">
                    <img src="<?= e(url('assets/images/gallery-sara.jpg')) ?>" 
                         alt="Client Sara - Boxing & Agility Results" 
                         class="ed-gallery-image"
                         loading="lazy">
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name">Sara</h3>
                        <span class="ed-gallery-client-tag">Boxing &amp; Conditioning</span>
                    </div>
                    <p class="ed-gallery-client-quote">
                        &ldquo;Boxing training with Mori completely transformed my stamina, reaction speed, and mental focus. His technique-first methodology makes every single round challenging and rewarding.&rdquo;
                    </p>
                </div>
            </div>

            <!-- Card 2: Megan -->
            <div class="ed-gallery-card">
                <div class="ed-gallery-media-wrapper">
                    <img src="<?= e(url('assets/images/gallery-megan.jpg')) ?>" 
                         alt="Client Megan - Strength & Fat Loss Transformation" 
                         class="ed-gallery-image"
                         loading="lazy">
                </div>
                <div class="ed-gallery-card-body">
                    <div class="ed-gallery-client-header">
                        <h3 class="ed-gallery-client-name">Megan</h3>
                        <span class="ed-gallery-client-tag">Fat Loss &amp; Strength</span>
                    </div>
                    <p class="ed-gallery-client-quote">
                        &ldquo;Lost 11kg and gained incredible core and back definition. Mori designed a structured strength regimen that worked seamlessly with my demanding schedule. Best trainer in KL.&rdquo;
                    </p>
                </div>
            </div>
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
                <a href="<?= e(url($lang . '/gallery.php')) ?>" class="btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.8rem;">View Full Gallery</a>
                <button type="button" class="ed-gallery-arrow-btn" id="ed-gallery-next" aria-label="Next Showcase">&#10095;</button>
            </div>
        </div>
    </section>
</div>

<script src="<?= e(url('assets/js/editorial-home.js')) ?>"></script>
<?php
require dirname(__DIR__) . '/includes/public_footer.php';
