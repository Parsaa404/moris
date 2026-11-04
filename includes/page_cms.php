<?php
/**
 * Subpage CMS Helper Library (About, Services, Packages, Gallery, Blog, Contact)
 * Connects all public subpages to the Admin Page Builder with bilingual content,
 * hero banners, repeaters (stats, pillars, services, process steps, FAQs), and CTAs.
 */

declare(strict_types=1);

require_once __DIR__ . '/homepage_cms.php';

/**
 * Default structured content for all 6 subpages (EN & FA).
 */
function get_subpage_defaults(): array
{
    $aboutStats = [
        [
            'num_en' => '13+',
            'num_fa' => '+۱۳',
            'label_en' => 'Years Coaching & Ring Experience',
            'label_fa' => 'سال سابقه مربی‌گری و حضور در رینگ',
        ],
        [
            'num_en' => '500+',
            'num_fa' => '+۵۰۰',
            'label_en' => 'Client Transformations',
            'label_fa' => 'تغییر بدنی و موفقیت شاگردان',
        ],
        [
            'num_en' => '100%',
            'num_fa' => '۱۰۰٪',
            'label_en' => 'Tailored 1-on-1 Programs',
            'label_fa' => 'برنامه‌ریزی کاملاً شخصی‌سازی‌شده',
        ],
        [
            'num_en' => 'KL & Global',
            'num_fa' => 'کوالالامپور و آنلاین',
            'label_en' => 'In-Person & Online Coaching',
            'label_fa' => 'آموزش حضوری و کوچینگ آنلاین جهانی',
        ],
    ];

    $aboutPillars = [
        [
            'icon' => 'award',
            'title_en' => 'Effort — Relentless Consistency',
            'title_fa' => 'تلاش (Effort) — استمرار هدفمند',
            'desc_en' => 'True physical transformation is built session by session. We establish sustainable habits and progressive overload without burnout.',
            'desc_fa' => 'تغییر واقعی بدن جلسه به جلسه ساخته می‌شود. ما عادت‌های پایدار و افزایش تدریجی فشار تمرینی را بدون فرسودگی ایجاد می‌کنیم.',
        ],
        [
            'icon' => 'star',
            'title_en' => 'Focus — Mind-Muscle & Ring IQ',
            'title_fa' => 'تمرکز (Focus) — هوشمندی در رینگ و باشگاه',
            'desc_en' => 'Every punch and every lift demands mental presence. Training sharpens your reaction speed, composure under pressure, and daily clarity.',
            'desc_fa' => 'هر ضربه بوکس و هر حرکت وزنه نیازمند حضور ذهن است. تمرینات ما سرعت واکنش، آرامش تحت فشار و تمرکز روزانه شما را تقویت می‌کند.',
        ],
        [
            'icon' => 'dumbbell',
            'title_en' => 'Technique — Biomechanical Precision',
            'title_fa' => 'تکنیک (Technique) — دقت بیومکانیکی',
            'desc_en' => 'Proper form protects your joints and maximizes force output. Learn authentic boxing footwork and structurally balanced strength training.',
            'desc_fa' => 'فرم صحیح اجرای حرکات از مفاصل شما محافظت کرده و بازدهی عضلات را به حداکثر می‌رساند؛ از فوت‌ورک اصولی بوکس تا تمرینات وزنه استاندارد.',
        ],
    ];

    $servicesList = [
        [
            'icon' => 'award',
            'image' => 'assets/images/sparring-boxers.jpg',
            'tag_en' => '01 · COMBAT & CONDITIONING',
            'tag_fa' => '۰۱ · بوکس و آمادگی جسمانی',
            'title_en' => '1-on-1 Boxing Coaching',
            'title_fa' => 'آموزش خصوصی و تخصصی بوکس',
            'desc_en' => 'Master authentic boxing from the ground up. Whether you are a complete beginner looking for an incredible workout or an experienced striker refining your craft, every round is tailored to your pace.',
            'desc_fa' => 'آموزش اصولی و گام‌به‌گام بوکس از پایه تا پیشرفته. چه برای تناسب اندام و تخلیه انرژی و چه برای یادگیری تکنیک‌های حرفه‌ای مبارزه، هر راند متناسب با سطح شما طراحی می‌شود.',
            'bullets_en' => "Stance, balance & dynamic footwork\nPad work, heavy bag & defensive head movement\nHigh-output cardiovascular conditioning\nStress relief & mental sharpness",
            'bullets_fa' => "گارد، تعادل و حرکت پای اصولی (Footwork)\nمیت‌گیری تخصصی، کیسه و دفاع و جاخالی\nافزایش فوق‌العاده استقامت قلبی-عروقی\nتخلیه استرس و افزایش تمرکز و سرعت واکنش",
        ],
        [
            'icon' => 'dumbbell',
            'image' => 'assets/images/basics-gym.jpg',
            'tag_en' => '02 · STRENGTH & HYPERTROPHY',
            'tag_fa' => '۰۲ · تمرینات قدرتی و عضله‌سازی',
            'title_en' => 'Personal Weight Training',
            'title_fa' => 'تمرینات خصوصی وزنه و فرم‌دهی',
            'desc_en' => 'Build lean muscle, functional power, and resilient joints with scientifically structured resistance training. Every movement is selected for your anatomy and injury history.',
            'desc_fa' => 'ساخت عضلات باکیفیت، افزایش قدرت کاربردی و تقویت مفاصل با برنامه‌ریزی علمی تمرینات مقاومتی. تمام حرکات بر اساس آناتومی و شرایط بدنی شما انتخاب می‌شوند.',
            'bullets_en' => "Biomechanically sound compound & isolation lifts\nPosture correction & core stabilization\nProgressive overload tracking every week\nInjury-free execution & mobility integration",
            'bullets_fa' => "اجرای صحیح حرکات چندمفصلی و تفکیکی با بیومکانیک استاندارد\nاصلاح ساختار قامت و تقویت عضلات میان‌تنه (Core)\nثبت و پایش پیشرفت هفتگی رکوردها\nپیشگیری از آسیب‌دیدگی و بهبود دامنه حرکتی",
        ],
        [
            'icon' => 'star',
            'image' => 'assets/images/gallery-megan.jpg',
            'tag_en' => '03 · BODY RECOMPOSITION',
            'tag_fa' => '۰۳ · کاهش چربی و تغییر ترکیب بدنی',
            'title_en' => 'Fat Loss & Athletic Toning',
            'title_fa' => 'چربی‌سوزی اصولی و تناسب اندام',
            'desc_en' => 'Drop stubborn body fat while keeping your strength and energy high. We combine metabolic conditioning, strength training, and practical nutrition habits that fit your lifestyle.',
            'desc_fa' => 'کاهش چربی‌های مقاوم بدن همراه با حفظ عضلات و سطح انرژی بالا. ترکیب تمرینات متابولیک، کار با وزنه و راهنمایی تغذیه کاربردی بدون رژیم‌های افراطی.',
            'bullets_en' => "Sustainable calorie & macronutrient guidance\nMetabolic conditioning & boxing HIIT rounds\nBody composition & measurement tracking\nDesigned for busy executives & professionals",
            'bullets_fa' => "راهنمایی تغذیه و ماکروها به صورت پایدار و کاربردی\nترکیب تمرینات متابولیک و راندهای چربی‌سوز بوکس\nارزیابی منظم سایز و ترکیب بدنی\nمناسب برای افراد پرمشغله با برنامه‌ریزی منعطف",
        ],
        [
            'icon' => 'map-pin',
            'image' => 'assets/images/gallery-sara.jpg',
            'tag_en' => '04 · WORLDWIDE COACHING',
            'tag_fa' => '۰۴ · کوچینگ آنلاین از سراسر جهان',
            'title_en' => 'Online Coaching & Custom Programming',
            'title_fa' => 'برنامه‌ریزی و مربی‌گری آنلاین',
            'desc_en' => 'Train with Coach Mori from anywhere in the world. Receive a fully customized training block, video form checks, nutrition targets, and direct WhatsApp accountability.',
            'desc_fa' => 'در هر جای دنیا که هستید زیر نظر مستقیم مربی موری تمرین کنید. دریافت برنامه تمرینی اختصاصی، بررسی ویدیویی فرم حرکات، تنظیم تغذیه و پشتیبانی مستقیم در واتساپ.',
            'bullets_en' => "Bespoke monthly training blocks for your gym or home\nVideo technique reviews & form feedback\nWeekly check-ins & program adjustments\nDirect WhatsApp support with Coach Mori",
            'bullets_fa' => "برنامه تمرینی ماهانه اختصاصی (مناسب باشگاه یا منزل)\nبررسی ویدیویی اجرای حرکات و اصلاح تکنیک\nچک‌این هفتگی و به‌روزرسانی برنامه بر اساس پیشرفت\nارتباط و پشتیبانی مستقیم با مربی در واتساپ",
        ],
    ];

    $servicesSteps = [
        [
            'step' => '01',
            'title_en' => 'Free Consultation & Assessment',
            'title_fa' => 'مشاوره رایگان و ارزیابی اولیه',
            'desc_en' => 'We discuss your goals, training history, schedule, and any past injuries to establish a clear starting baseline.',
            'desc_fa' => 'اهداف، سابقه ورزشی، برنامه زمانی و آسیب‌دیدگی‌های احتمالی شما را بررسی می‌کنیم تا نقطه شروع دقیق مشخص شود.',
        ],
        [
            'step' => '02',
            'title_en' => 'Bespoke Program Architecture',
            'title_fa' => 'طراحی برنامه اختصاصی شما',
            'desc_en' => 'Your training split, boxing rounds, progression targets, and nutritional guidelines are built specifically for your physiology.',
            'desc_fa' => 'تقسیم‌بندی جلسات وزنه، راندهای بوکس، اهداف پیشرفت و چارچوب تغذیه دقیقاً متناسب با فیزیولوژی بدن شما طراحی می‌شود.',
        ],
        [
            'step' => '03',
            'title_en' => 'Focused Execution & Adaptation',
            'title_fa' => 'اجرای دقیق و پایش مستمر نتایج',
            'desc_en' => 'Every session is coached with precision. We track your strength, stamina, and body composition, adapting the plan as you level up.',
            'desc_fa' => 'هر جلسه با نظارت کامل بر تکنیک اجرا می‌شود و با ثبت تغییرات قدرت و ترکیب بدنی، برنامه شما گام‌به‌گام ارتقا می‌یابد.',
        ],
    ];

    $packagesFaq = [
        [
            'q_en' => 'Can I combine Boxing and Weight Training in my sessions?',
            'q_fa' => 'آیا می‌توانم تمرینات بوکس و وزنه را با هم ترکیب کنم؟',
            'a_en' => 'Yes! Many clients choose the combined Weight + Boxing program for maximum fat loss, strength, and athletic conditioning.',
            'a_fa' => 'بله! بسیاری از شاگردان پکیج ترکیبی «وزنه + بوکس» را انتخاب می‌کنند تا هم‌زمان به عضله‌سازی، چربی‌سوزی بالا و مهارت بوکس برسند.',
        ],
        [
            'q_en' => 'Where do in-person training sessions take place?',
            'q_fa' => 'جلسات حضوری در کجا برگزار می‌شوند؟',
            'a_en' => 'In-person 1-on-1 sessions are held in Kuala Lumpur at a fully equipped gym and boxing facility. Contact us for exact location details.',
            'a_fa' => 'جلسات خصوصی حضوری در کوالالامپور در محیطی کاملاً مجهز برای تمرینات وزنه و بوکس برگزار می‌شوند.',
        ],
        [
            'q_en' => 'What if I am a complete beginner?',
            'q_fa' => 'اگر کاملاً مبتدی باشم هم می‌توانم شروع کنم؟',
            'a_en' => 'Absolutely. Over 70% of clients start with zero boxing or lifting experience. Every exercise and round is scaled to your current fitness level.',
            'a_fa' => 'قطعا! بیش از ۷۰٪ شاگردان بدون سابقه قبلی بوکس یا بدنسازی شروع کرده‌اند. تمام تمرینات از سطح فعلی شما و با آموزش کامل تکنیک آغاز می‌شود.',
        ],
    ];

    return [
        'about' => [
            'hero_eyebrow'    => ['en' => 'COACH PROFILE · KUALA LUMPUR & ONLINE', 'fa' => 'معرفی مربی · کوالالامپور و آنلاین'],
            'hero_title'      => ['en' => 'ABOUT MORI.', 'fa' => 'درباره موری.'],
            'hero_subtitle'   => ['en' => '13+ years of competitive boxing, strength coaching, and science-backed physical transformation.', 'fa' => 'بیش از ۱۳ سال تجربه در رینگ بوکس، مربی‌گری قدرت و تغییرات بدنی مبتنی بر علم تمرین.'],
            'hero_bg'         => ['en' => 'assets/images/hero-earn-it.webp', 'fa' => 'assets/images/hero-earn-it-rtl.webp'],
            'hero_chips'      => ['en' => "13+ Years Ring & Gym Experience\nEFT Coaching Methodology\n1-on-1 & Worldwide Online", 'fa' => "+۱۳ سال سابقه در رینگ و باشگاه\nمتد اختصاصی EFT (تلاش، تمرکز، تکنیک)\nآموزش حضوری و کوچینگ آنلاین"],
            'hero_badge_num'  => ['en' => '13+ Years', 'fa' => '+۱۳ سال'],
            'hero_badge_text' => ['en' => 'Pro Boxing & Strength Coaching Experience', 'fa' => 'سابقه حرفه‌ای در رینگ بوکس و مربی‌گری بدنسازی'],
            'hero_corner_tag' => ['en' => 'KUALA LUMPUR & ONLINE', 'fa' => 'کوالالامپور و آنلاین'],
            'hero_btn1_text'  => ['en' => 'Explore Packages', 'fa' => 'مشاهده پکیج‌ها'],
            'hero_btn1_url'   => ['en' => 'packages.php', 'fa' => 'packages.php'],
            'hero_btn2_text'  => ['en' => 'Book Consultation', 'fa' => 'رزرو مشاوره رایگان'],
            'hero_btn2_url'   => ['en' => 'contact.php', 'fa' => 'contact.php'],
            'hero_index'      => ['en' => '01 // COACH PROFILE', 'fa' => '۰۱ // پروفایل مربی'],
            'hero_watermark'  => ['en' => 'COACH MORI', 'fa' => 'COACH MORI'],
            'story_eyebrow'   => ['en' => 'THE JOURNEY & PHILOSOPHY', 'fa' => 'مسیر حرفه‌ای و فلسفه تمرینی'],
            'story_title'     => ['en' => 'Built on discipline, refined by science.', 'fa' => 'بنا شده بر پایه نظم، تکامل‌یافته با دانش ورزشی.'],
            'story_image'     => ['en' => 'assets/images/coach-mori.jpg', 'fa' => 'assets/images/coach-mori.jpg'],
            'story_lead'      => ['en' => "Since my teenage years, physical excellence has always been my passion. Over the past 13+ years inside the boxing ring and on the gym floor, I have dedicated my career to helping everyday professionals and athletes unlock their strongest, leanest, most capable selves.", 'fa' => "از دوران نوجوانی، دستیابی به اوج آمادگی جسمانی همواره اشتیاق اصلی من بوده است. طی بیش از ۱۳ سال حضور مستمر در رینگ بوکس و باشگاه، مسیر حرفه‌ای خود را وقف کمک به افراد و ورزشکاران کرده‌ام تا به قوی‌ترین، آماده‌ترین و سالم‌ترین نسخه خود تبدیل شوند."],
            'story_body'      => ['en' => "Over the years, I have studied and tested numerous training methodologies, realizing that the journey itself is as valuable as the destination — and that having an intellectually sound, injury-free plan is paramount.\n\nNo gimmicks, no cookie-cutter templates. Whether your goal is learning authentic boxing skill, shedding stubborn body fat, or building functional muscle and posture, every session is engineered around your unique biomechanics, schedule, and recovery capacity.", 'fa' => "در طول سال‌ها با مطالعه و تجربه متدهای گوناگون آموختم که داشتن یک برنامه علمی، هدفمند و بدون آسیب‌دیدگی مهم‌ترین اصل در رسیدن به نتیجه ماندگار است.\n\nبدون تمرینات نمایشی و بدون برنامه‌های کپی‌شده. چه هدف شما یادگیری اصولی بوکس باشد، چه کاهش چربی و چه عضله‌سازی و اصلاح استایل بدنی، هر جلسه دقیقاً بر اساس بیومکانیک بدن، برنامه زندگی و ظرفیت ریکاوری شما طراحی می‌شود."],
            'stats_json'      => ['en' => json_encode($aboutStats, JSON_UNESCAPED_UNICODE), 'fa' => json_encode($aboutStats, JSON_UNESCAPED_UNICODE)],
            'pillars_title'   => ['en' => 'The EFT Methodology', 'fa' => 'متدولوژی سه‌گانه EFT'],
            'pillars_sub'     => ['en' => 'Effort, Focus, and Technique — the three foundations behind every successful transformation.', 'fa' => 'تلاش، تمرکز و تکنیک — سه ستون اصلی که ضامن نتیجه‌گیری واقعی در هر جلسه تمرینی هستند.'],
            'pillars_json'    => ['en' => json_encode($aboutPillars, JSON_UNESCAPED_UNICODE), 'fa' => json_encode($aboutPillars, JSON_UNESCAPED_UNICODE)],
            'cta_title'       => ['en' => 'Ready to train with purpose?', 'fa' => 'آماده شروع تمرینات هدفمند هستید؟'],
            'cta_sub'         => ['en' => 'Book a free consultation or explore training packages tailored to your goals.', 'fa' => 'همین حالا یک جلسه مشاوره رایگان رزرو کنید یا پکیج‌های تمرینی را ببینید.'],
            'cta_btn_text'    => ['en' => 'Explore Packages', 'fa' => 'مشاهده پکیج‌ها'],
            'cta_btn_url'     => ['en' => 'packages.php', 'fa' => 'packages.php'],
        ],
        'services' => [
            'hero_eyebrow'    => ['en' => 'TRAINING PROGRAMS & METHODOLOGY', 'fa' => 'خدمات تخصصی و برنامه‌های تمرینی'],
            'hero_title'      => ['en' => 'SERVICES.', 'fa' => 'خدمات مربی‌گری.'],
            'hero_subtitle'   => ['en' => 'Bespoke 1-on-1 personal training, authentic boxing coaching, and online programming.', 'fa' => 'تمرین خصوصی ۱ به ۱، آموزش تخصصی بوکس و برنامه‌ریزی آنلاین متناسب با هدف شما.'],
            'hero_bg'         => ['en' => 'assets/images/sparring-boxers.jpg', 'fa' => 'assets/images/sparring-boxers.jpg'],
            'hero_chips'      => ['en' => "1-on-1 Boxing Coaching\nPersonal Weight Training\nBody Recomposition\nOnline Programming", 'fa' => "آموزش خصوصی بوکس\nتمرینات تخصصی وزنه\nچربی‌سوزی و فرم‌دهی\nکوچینگ آنلاین جهانی"],
            'hero_badge_num'  => ['en' => '4 Core Programs', 'fa' => '۴ دوره تخصصی'],
            'hero_badge_text' => ['en' => '100% Tailored to Your Physiology & Goals', 'fa' => 'برنامه‌ریزی ۱۰۰٪ شخصی‌سازی‌شده متناسب با هدف شما'],
            'hero_corner_tag' => ['en' => 'BOXING · STRENGTH · CONDITIONING', 'fa' => 'بوکس · وزنه · چربی‌سوزی'],
            'hero_btn1_text'  => ['en' => 'View Pricing & Packages', 'fa' => 'مشاهده تعرفه و پکیج‌ها'],
            'hero_btn1_url'   => ['en' => 'packages.php', 'fa' => 'packages.php'],
            'hero_btn2_text'  => ['en' => 'Free Assessment', 'fa' => 'دریافت مشاوره رایگان'],
            'hero_btn2_url'   => ['en' => 'contact.php', 'fa' => 'contact.php'],
            'hero_index'      => ['en' => '02 // TRAINING PROGRAMS', 'fa' => '۰۲ // خدمات و دوره‌های تمرینی'],
            'hero_watermark'  => ['en' => 'DISCIPLINE', 'fa' => 'DISCIPLINE'],
            'intro_eyebrow'   => ['en' => 'WHAT WE OFFER', 'fa' => 'حوزه‌های تخصصی تمرین'],
            'intro_title'     => ['en' => 'Engineered for real physiological adaptation.', 'fa' => 'طراحی‌شده برای تغییرات واقعی و ماندگار بدنی.'],
            'intro_sub'       => ['en' => 'Every program combines technical mastery, progressive strength training, and sustainable conditioning.', 'fa' => 'تمامی دوره‌ها ترکیبی از تکنیک استاندارد، افزایش علمی قدرت و بهبود استقامت بدنی هستند.'],
            'services_json'   => ['en' => json_encode($servicesList, JSON_UNESCAPED_UNICODE), 'fa' => json_encode($servicesList, JSON_UNESCAPED_UNICODE)],
            'steps_title'     => ['en' => 'How We Work Together', 'fa' => 'مراحل شروع همکاری و تمرین'],
            'steps_sub'       => ['en' => 'A simple, structured 3-step process from your first consultation to measurable results.', 'fa' => 'یک مسیر شفاف ۳ مرحله‌ای از اولین جلسه مشاوره تا رسیدن به نتیجه دلخواه شما.'],
            'steps_json'      => ['en' => json_encode($servicesSteps, JSON_UNESCAPED_UNICODE), 'fa' => json_encode($servicesSteps, JSON_UNESCAPED_UNICODE)],
            'cta_title'       => ['en' => 'Find the right training package for you.', 'fa' => 'پکیج تمرینی مناسب خودتان را انتخاب کنید.'],
            'cta_sub'         => ['en' => 'View transparent pricing for 1-on-1 Weight Training, Boxing, and Combined programs.', 'fa' => 'مشاهده جزئیات و تعرفه پکیج‌های تمرین وزنه، بوکس و دوره‌های ترکیبی.'],
            'cta_btn_text'    => ['en' => 'View Pricing & Packages', 'fa' => 'مشاهده قیمت‌ها و پکیج‌ها'],
            'cta_btn_url'     => ['en' => 'packages.php', 'fa' => 'packages.php'],
        ],
        'packages' => [
            'hero_eyebrow'    => ['en' => 'TRANSPARENT PRICING · KUALA LUMPUR & ONLINE', 'fa' => 'تعرفه شفاف دوره‌ها · کوالالامپور و آنلاین'],
            'hero_title'      => ['en' => 'PACKAGES.', 'fa' => 'پکیج‌های تمرینی.'],
            'hero_subtitle'   => ['en' => 'Invest in structured 1-on-1 coaching. Select a package below to start your transformation.', 'fa' => 'سرمایه‌گذاری روی سلامتی، قدرت و تناسب اندام. پکیج مناسب خود را برای شروع انتخاب کنید.'],
            'hero_bg'         => ['en' => 'assets/images/basics-gym.jpg', 'fa' => 'assets/images/basics-gym.jpg'],
            'hero_chips'      => ['en' => "Transparent Pricing\nWeight + Boxing Combos\nFree Initial Consultation", 'fa' => "تعرفه‌های شفاف و مشخص\nپکیج‌های ترکیبی وزنه + بوکس\nجلسه مشاوره اولیه رایگان"],
            'hero_badge_num'  => ['en' => '100% Bespoke', 'fa' => '۱۰۰٪ اختصاصی'],
            'hero_badge_text' => ['en' => 'Flexible 1-on-1 Weight, Boxing & Combo Packs', 'fa' => 'پکیج‌های خصوصی وزنه، بوکس و دوره‌های ترکیبی'],
            'hero_corner_tag' => ['en' => 'LIMITED 1-ON-1 SLOTS', 'fa' => 'ظرفیت محدود ماهانه'],
            'hero_btn1_text'  => ['en' => 'Ask Coach Mori', 'fa' => 'مشاوره انتخاب پکیج'],
            'hero_btn1_url'   => ['en' => 'contact.php', 'fa' => 'contact.php'],
            'hero_btn2_text'  => ['en' => 'Explore Services', 'fa' => 'بررسی جزئیات خدمات'],
            'hero_btn2_url'   => ['en' => 'services.php', 'fa' => 'services.php'],
            'hero_index'      => ['en' => '03 // MEMBERSHIP & PRICING', 'fa' => '۰۳ // تعرفه و پکیج‌های تمرینی'],
            'hero_watermark'  => ['en' => 'PACKAGES', 'fa' => 'PACKAGES'],
            'faq_title'       => ['en' => 'Frequently Asked Questions', 'fa' => 'سوالات متداول درباره پکیج‌ها'],
            'faq_sub'         => ['en' => 'Everything you need to know before booking your first session.', 'fa' => 'پاسخ به پرسش‌های پرتکرار پیش از شروع جلسات تمرینی.'],
            'faq_json'        => ['en' => json_encode($packagesFaq, JSON_UNESCAPED_UNICODE), 'fa' => json_encode($packagesFaq, JSON_UNESCAPED_UNICODE)],
            'cta_title'       => ['en' => 'Not sure which package fits your goal?', 'fa' => 'نمی‌دانید کدام پکیج برای هدف شما مناسب‌تر است؟'],
            'cta_sub'         => ['en' => 'Message Coach Mori directly for a free 15-minute consultation and personalized recommendation.', 'fa' => 'برای دریافت مشاوره رایگان و انتخاب بهترین دوره متناسب با شرایطتان پیام بگذارید.'],
            'cta_btn_text'    => ['en' => 'Book Free Consultation', 'fa' => 'درخواست مشاوره رایگان'],
            'cta_btn_url'     => ['en' => 'contact.php', 'fa' => 'contact.php'],
        ],
        'gallery' => [
            'hero_eyebrow'     => ['en' => 'CLIENT RESULTS & GYM ACTION', 'fa' => 'نتایج شاگردان و فضای تمرین'],
            'hero_title'       => ['en' => 'GALLERY.', 'fa' => 'گالری تصاویر.'],
            'hero_subtitle'    => ['en' => 'Real clients, real rounds, and real transformations inside the ring and on the gym floor.', 'fa' => 'تصاویر واقعی از جلسات بوکس، تمرینات قدرتی و تغییرات بدنی شاگردان.'],
            'hero_bg'          => ['en' => 'assets/images/hero-earn-it.webp', 'fa' => 'assets/images/hero-earn-it-rtl.webp'],
            'hero_chips'       => ['en' => "Client Transformations\nBoxing & Pad Work\nStrength & Conditioning", 'fa' => "تغییرات بدنی شاگردان\nجلسات میت‌گیری و بوکس\nتمرینات قدرتی و باشگاه"],
            'hero_badge_num'   => ['en' => '500+ Clients', 'fa' => '+۵۰۰ شاگرد'],
            'hero_badge_text'  => ['en' => 'Real Client Transformations & Ring Sessions', 'fa' => 'تغییرات واقعی بدنی و جلسات تمرینی در باشگاه و رینگ'],
            'hero_corner_tag'  => ['en' => 'VERIFIED CLIENT RESULTS', 'fa' => 'نتایج واقعی شاگردان'],
            'hero_btn1_text'   => ['en' => 'Start Your Transformation', 'fa' => 'شروع تغییر بدنی شما'],
            'hero_btn1_url'    => ['en' => 'contact.php', 'fa' => 'contact.php'],
            'hero_btn2_text'   => ['en' => 'Training Packages', 'fa' => 'مشاهده پکیج‌ها'],
            'hero_btn2_url'    => ['en' => 'packages.php', 'fa' => 'packages.php'],
            'hero_index'       => ['en' => '04 // VISUAL ARCHIVE', 'fa' => '۰۴ // آرشیو تصاویر و نتایج'],
            'hero_watermark'   => ['en' => 'RESULTS', 'fa' => 'RESULTS'],
            'archive_eyebrow'  => ['en' => 'TRAINING & RESULTS ARCHIVE', 'fa' => 'آرشیو تصاویر و نتایج'],
            'archive_title'    => ['en' => 'Our Gallery', 'fa' => 'گالری ما'],
            'archive_sub'      => ['en' => 'Check out our clients results and transformations', 'fa' => 'تصاویر نتایج و تغییرات شاگردان ما را مشاهده کنید'],
            'stories_eyebrow'  => ['en' => 'CLIENT STORIES', 'fa' => 'داستان موفقیت شاگردان'],
            'stories_title'    => ['en' => 'Featured Client Transformations', 'fa' => 'تجربه و تغییرات شاگردان'],
            'stories_sub'      => ['en' => '', 'fa' => ''],
            'stories_json'     => ['en' => json_encode([
                [
                    'image'    => 'assets/images/gallery-sara.jpg',
                    'name_en'  => 'Sara',
                    'name_fa'  => 'سارا',
                    'tag_en'   => 'Boxing & Conditioning',
                    'tag_fa'   => 'بوکس و آمادگی جسمانی',
                    'quote_en' => 'Training boxing with Mori completely transformed my stamina, footwork, and confidence. His technical approach makes every round challenging and rewarding.',
                    'quote_fa' => 'تمرینات بوکس با موری استقامت، سرعت واکنش و تمرکز ذهنی من را به کلی دگرگون کرد. متدولوژی تکنیک‌محور او باعث می‌شود هر راند پرچالش و لذت‌بخش باشد.',
                ],
                [
                    'image'    => 'assets/images/gallery-megan.jpg',
                    'name_en'  => 'Megan',
                    'name_fa'  => 'مگان',
                    'tag_en'   => 'Strength & Fat Loss',
                    'tag_fa'   => 'کاهش چربی و افزایش قدرت',
                    'quote_en' => 'Down 11kg and stronger than ever. Mori built a structured weight training routine that fit my busy schedule and kept me accountable every single week. Best trainer in KL.',
                    'quote_fa' => '۱۱ کیلوگرم وزن کم کردم و عضلات شکم و پشتم تفکیک فوق‌العاده‌ای پیدا کرد. موری یک برنامه قدرتی منظم تنظیم کرد که کاملاً با مشغله کاری من هماهنگ بود. بهترین مربی در کوالالامپور.',
                ],
            ], JSON_UNESCAPED_UNICODE), 'fa' => ''],
            'cta_title'        => ['en' => 'Your transformation is next.', 'fa' => 'تغییر بعدی متعلق به شماست.'],
            'cta_sub'          => ['en' => 'Start your tailored boxing or strength program with Coach Mori today.', 'fa' => 'همین امروز برنامه اختصاصی بوکس یا بدنسازی خود را با مربی موری شروع کنید.'],
            'cta_btn_text'     => ['en' => 'Start Training Now', 'fa' => 'شروع تمرین'],
            'cta_btn_url'      => ['en' => 'contact.php', 'fa' => 'contact.php'],
        ],
        'blog' => [
            'hero_eyebrow'    => ['en' => 'KNOWLEDGE · SCIENCE · CLIENT STORIES', 'fa' => 'مقالات آموزشی · علم تمرین · تجربه شاگردان'],
            'hero_title'      => ['en' => 'THE BLOG.', 'fa' => 'وبلاگ آموزشی.'],
            'hero_subtitle'   => ['en' => 'Practical insights on boxing technique, strength programming, fat loss, and athletic longevity.', 'fa' => 'نکات کاربردی درباره تکنیک‌های بوکس، اصول عضله‌سازی، کاهش چربی و تغذیه ورزشی.'],
            'hero_bg'         => ['en' => 'assets/images/sparring-boxers.jpg', 'fa' => 'assets/images/sparring-boxers.jpg'],
            'hero_chips'      => ['en' => "Boxing Technique\nStrength & Hypertrophy\nNutrition & Fat Loss", 'fa' => "تکنیک‌های بوکس\nاصول عضله‌سازی و قدرت\nتغذیه و کاهش چربی"],
            'hero_badge_num'  => ['en' => 'Evidence-Based', 'fa' => 'دانش تمرینی'],
            'hero_badge_text' => ['en' => 'Guides on Boxing IQ, Hypertrophy & Nutrition', 'fa' => 'مقالات علمی تمرین، تکنیک بوکس، تغذیه و ریکاوری'],
            'hero_corner_tag' => ['en' => 'ARTICLES & GUIDES', 'fa' => 'مقالات و آموزش‌ها'],
            'hero_btn1_text'  => ['en' => 'Train With Coach Mori', 'fa' => 'شروع تمرین با مربی'],
            'hero_btn1_url'   => ['en' => 'packages.php', 'fa' => 'packages.php'],
            'hero_btn2_text'  => ['en' => 'Ask a Question', 'fa' => 'ارتباط با مربی'],
            'hero_btn2_url'   => ['en' => 'contact.php', 'fa' => 'contact.php'],
            'hero_index'      => ['en' => '05 // PERFORMANCE JOURNAL', 'fa' => '۰۵ // مجله تخصصی و مقالات'],
            'hero_watermark'  => ['en' => 'JOURNAL', 'fa' => 'JOURNAL'],
            'cta_title'       => ['en' => 'Put knowledge into action.', 'fa' => 'دانش تمرینی را به عمل تبدیل کنید.'],
            'cta_sub'         => ['en' => 'Work 1-on-1 with Coach Mori in Kuala Lumpur or online from anywhere.', 'fa' => 'به صورت حضوری در کوالالامپور یا آنلاین از هر جای دنیا زیر نظر مربی تمرین کنید.'],
            'cta_btn_text'    => ['en' => 'View Training Packages', 'fa' => 'مشاهده پکیج‌های تمرینی'],
            'cta_btn_url'     => ['en' => 'packages.php', 'fa' => 'packages.php'],
        ],
        'contact' => [
            'hero_eyebrow'    => ['en' => 'DIRECT CONNECTION · KUALA LUMPUR & ONLINE', 'fa' => 'ارتباط مستقیم · کوالالامپور و آنلاین'],
            'hero_title'      => ['en' => 'START TRAINING.', 'fa' => 'تماس و شروع تمرین.'],
            'hero_subtitle'   => ['en' => 'Send a message to book your consultation, select a package, or ask any question.', 'fa' => 'برای رزرو جلسه مشاوره، انتخاب پکیج یا پرسیدن هرگونه سوال پیام بگذارید.'],
            'hero_bg'         => ['en' => 'assets/images/hero-earn-it.webp', 'fa' => 'assets/images/hero-earn-it-rtl.webp'],
            'hero_chips'      => ['en' => "Instant WhatsApp Chat\nKuala Lumpur Facility\nWorldwide Online Coaching", 'fa' => "گفتگوی مستقیم در واتساپ\nمجموعه مجهز در کوالالامپور\nکوچینگ آنلاین از سراسر جهان"],
            'hero_badge_num'  => ['en' => 'Direct Access', 'fa' => 'پاسخگویی سریع'],
            'hero_badge_text' => ['en' => 'Personal Response via WhatsApp, Phone or Email', 'fa' => 'هماهنگی مستقیم با مربی موری از طریق واتساپ یا فرم'],
            'hero_corner_tag' => ['en' => 'FREE 15-MIN CONSULTATION', 'fa' => 'مشاوره اولیه رایگان'],
            'hero_btn1_text'  => ['en' => 'View Training Packages', 'fa' => 'مشاهده پکیج‌های تمرینی'],
            'hero_btn1_url'   => ['en' => 'packages.php', 'fa' => 'packages.php'],
            'hero_btn2_text'  => ['en' => 'About Coach Mori', 'fa' => 'آشنایی با مربی'],
            'hero_btn2_url'   => ['en' => 'about.php', 'fa' => 'about.php'],
            'hero_index'      => ['en' => '06 // DIRECT CONNECTION', 'fa' => '۰۶ // ارتباط مستقیم و رزرو'],
            'hero_watermark'  => ['en' => 'CONTACT', 'fa' => 'CONTACT'],
            'info_title'      => ['en' => 'Direct Contact Channels', 'fa' => 'راه‌های ارتباط مستقیم'],
            'info_sub'        => ['en' => 'Prefer an instant chat? Reach out directly via WhatsApp, phone, or email. I respond personally to all enquiries.', 'fa' => 'ترجیح می‌دهید سریع‌تر گفتگو کنیم؟ از طریق واتساپ، تماس تلفنی یا ایمیل مستقیماً در ارتباط باشید.'],
            'location_en'     => ['en' => 'Kuala Lumpur, Malaysia (In-Person & Worldwide Online)', 'fa' => 'کوالالامپور، مالزی (حضوری و کوچینگ آنلاین سراسر جهان)'],
            'hours_en'        => ['en' => 'Mon – Sat · 7:00 AM – 9:00 PM (By Appointment)', 'fa' => 'دوشنبه تا شنبه · ۷:۰۰ صبح تا ۹:۰۰ شب (با هماهنگی قبلی)'],
        ],
    ];
}

/**
 * Ensure all 6 subpages exist in `pages` and their default fields exist in `homepage_content`.
 */
function ensure_subpages_seeded(): void
{
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    ensure_homepage_cms_tables();

    try {
        $pdo = db();
        $pageRows = [
            ['about',    'About Moris',       'درباره موریس',      '13+ years of boxing & personal training experience in Kuala Lumpur and online.', 'بیش از ۱۳ سال تجربه مربی‌گری بوکس و تمرین خصوصی در کوالالامپور و آنلاین.'],
            ['services', 'Services',          'خدمات',             '1-on-1 personal training, boxing coaching, fat loss, and online programming.', 'تمرین خصوصی یک به یک، مربی‌گری بوکس، کاهش چربی و برنامه‌ریزی آنلاین.'],
            ['packages', 'Training Packages', 'پکیج‌های تمرینی',    'Training packages and transparent pricing for weight training and boxing.', 'پکیج‌های تمرینی و تعرفه جلسات وزنه، بوکس و دوره‌های ترکیبی.'],
            ['gallery',  'Gallery',           'گالری تصاویر',       'Client transformations, boxing sessions, and gym photos.', 'تصاویر تغییرات شاگردان، جلسات بوکس و تمرینات باشگاه.'],
            ['blog',     'Training Blog',     'وبلاگ آموزشی',      'Articles, training tips, and client stories by Coach Mori.', 'مقالات آموزشی، نکات تمرینی و داستان موفقیت شاگردان.'],
            ['contact',  'Contact',           'تماس با ما',        'Get in touch with Coach Mori to start training or book a consultation.', 'تماس با مربی موری برای شروع تمرینات یا رزرو مشاوره رایگان.'],
        ];

        $stmtPage = $pdo->prepare("
            INSERT IGNORE INTO pages (slug, title_en, title_fa, meta_description_en, meta_description_fa, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        foreach ($pageRows as $pr) {
            $stmtPage->execute($pr);
        }

        $defaults = get_subpage_defaults();
        $stmtContent = $pdo->prepare("
            INSERT IGNORE INTO homepage_content
                (section_key, content_key, value_en, value_fa, draft_value_en, draft_value_fa, content_type, label)
            VALUES (?, ?, ?, ?, ?, ?, 'text', ?)
        ");

        foreach ($defaults as $slug => $fields) {
            $secKey = 'page_' . $slug;
            foreach ($fields as $cKey => $langs) {
                $stmtContent->execute([
                    $secKey,
                    $cKey,
                    $langs['en'],
                    $langs['fa'],
                    $langs['en'],
                    $langs['fa'],
                    $cKey,
                ]);
            }
        }
    } catch (Throwable) {
        // Ignore if DB unavailable
    }
}

/**
 * Retrieve all CMS fields for a given subpage slug (e.g. 'about', 'services', 'packages', 'gallery', 'blog', 'contact').
 */
function get_subpage_fields(string $slug): array
{
    ensure_subpages_seeded();

    $defaults = get_subpage_defaults()[$slug] ?? [];
    $result = [];
    foreach ($defaults as $k => $v) {
        $result[$k] = [
            'en' => $v['en'] ?? '',
            'fa' => $v['fa'] ?? '',
        ];
    }

    try {
        $stmt = db()->prepare("SELECT content_key, value_en, value_fa FROM homepage_content WHERE section_key = ?");
        $stmt->execute(['page_' . $slug]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $k = $row['content_key'];
            $result[$k] = [
                'en' => $row['value_en'] ?? ($result[$k]['en'] ?? ''),
                'fa' => $row['value_fa'] ?? ($result[$k]['fa'] ?? ''),
            ];
        }
    } catch (Throwable) {
        // Use defaults
    }

    return $result;
}

/**
 * Get a single localized field from subpage fields array.
 * If the field exists in DB (`$fields[$key]`), respects empty values when cleared in Admin
 * instead of reverting to hardcoded defaults.
 */
function sp_field(array $fields, string $key, string $lang, string $default = ''): string
{
    if (array_key_exists($key, $fields)) {
        $val = trim((string) ($fields[$key][$lang] ?? ''));
        if ($val !== '') {
            return $val;
        }
        $en = trim((string) ($fields[$key]['en'] ?? ''));
        if ($en !== '') {
            return $en;
        }
        return '';
    }
    return $default;
}

/**
 * Decode a JSON repeater field from subpage fields array.
 */
function sp_json(array $fields, string $key, array $default = []): array
{
    if (array_key_exists($key, $fields)) {
        $raw = $fields[$key]['en'] ?? '';
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
    return $default;
}

/**
 * Save subpage fields (updates both live and draft columns in `homepage_content`).
 */
function save_subpage_fields(string $slug, array $fieldsEn, array $fieldsFa): void
{
    ensure_subpages_seeded();

    $pdo = db();
    $secKey = 'page_' . $slug;
    $stmt = $pdo->prepare("
        INSERT INTO homepage_content (section_key, content_key, value_en, value_fa, draft_value_en, draft_value_fa, content_type, label)
        VALUES (?, ?, ?, ?, ?, ?, 'text', ?)
        ON DUPLICATE KEY UPDATE
            value_en = VALUES(value_en),
            value_fa = VALUES(value_fa),
            draft_value_en = VALUES(draft_value_en),
            draft_value_fa = VALUES(draft_value_fa)
    ");

    foreach ($fieldsEn as $key => $valEn) {
        $valFa = $fieldsFa[$key] ?? $valEn;
        $stmt->execute([$secKey, $key, $valEn, $valFa, $valEn, $valFa, $key]);
    }
}

/**
 * Build SEO variables ($metaDescription, $metaKeywords, $ogImage, $robotsMeta) for a subpage.
 */
function get_subpage_seo(?array $pageRow, array $fields, string $lang): array
{
    $desc = $pageRow ? trim((string) lang_field($pageRow, 'meta_description', $lang)) : '';
    if ($desc === '') {
        $desc = sp_field($fields, 'hero_subtitle', $lang);
    }
    $kw = sp_field($fields, 'seo_keywords', $lang);
    $og = sp_field($fields, 'seo_og_image', 'en');
    if ($og === '') {
        $og = sp_field($fields, 'hero_bg', 'en', 'assets/images/hero-earn-it.webp');
    }
    $noindex = sp_field($fields, 'seo_noindex', 'en', '0') === '1';

    return [
        'description' => $desc,
        'keywords'    => $kw,
        'og_image'    => $og,
        'robots'      => $noindex ? 'noindex, nofollow' : 'index, follow',
    ];
}

/**
 * Render the Architectural Editorial Subpage Hero Section.
 * All texts, chips, floating badge, corner tag, and buttons are editable in Page Builder (`admin/page-edit.php`).
 */
function render_subpage_hero(string $lang, array $fields, string $slug = ''): void
{
    $isFa     = ($lang === 'fa');
    $pageSlug = $slug !== '' ? $slug : (string) ($GLOBALS['activeNav'] ?? $GLOBALS['slug'] ?? 'about');

    $eyebrow    = sp_field($fields, 'hero_eyebrow', $lang);
    $title      = sp_field($fields, 'hero_title', $lang);
    $subtitle   = sp_field($fields, 'hero_subtitle', $lang);
    $bgImg      = sp_field($fields, 'hero_bg', $lang, 'assets/images/hero-earn-it.webp');

    $chipsRaw   = sp_field($fields, 'hero_chips', $lang);
    $chips      = parse_features($chipsRaw);

    $badgeNum   = sp_field($fields, 'hero_badge_num', $lang);
    $badgeTxt   = sp_field($fields, 'hero_badge_text', $lang);
    $cornerTag  = sp_field($fields, 'hero_corner_tag', $lang);

    $btn1Text   = sp_field($fields, 'hero_btn1_text', $lang);
    $btn1Url    = sp_field($fields, 'hero_btn1_url', 'en', 'packages.php');
    $btn2Text   = sp_field($fields, 'hero_btn2_text', $lang);
    $btn2Url    = sp_field($fields, 'hero_btn2_url', 'en', 'contact.php');

    $indexLabel = sp_field($fields, 'hero_index', $lang);
    $watermark  = sp_field($fields, 'hero_watermark', 'en');
    $homeLabel  = $isFa ? 'صفحه اصلی' : 'Home';
    ?>
    <section class="sp-hero" data-watermark="<?= e($watermark) ?>">
        <div class="sp-hero-ambient" aria-hidden="true"></div>
        <div class="sp-container">
            <!-- Top Editorial Bar: Breadcrumb + Section Index -->
            <div class="sp-hero-topbar">
                <nav class="sp-hero-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?= e(url($lang . '/')) ?>"><?= e($homeLabel) ?></a>
                    <?php if ($title !== ''): ?>
                        <span class="sp-crumb-sep">/</span>
                        <span class="sp-crumb-current"><?= e(rtrim($title, '.')) ?></span>
                    <?php endif; ?>
                </nav>
                <?php if ($indexLabel !== ''): ?>
                    <div class="sp-hero-index"><?= e($indexLabel) ?></div>
                <?php endif; ?>
            </div>

            <!-- Main 2-Column Showcase Grid -->
            <div class="sp-hero-grid">
                <div class="sp-hero-copy">
                    <?php if ($eyebrow !== ''): ?>
                        <div class="sp-eyebrow-wrap">
                            <span class="sp-eyebrow">
                                <span class="sp-eyebrow-dot" aria-hidden="true"></span>
                                <?= e($eyebrow) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($title !== ''): ?>
                        <h1 class="sp-hero-title"><?= e($title) ?></h1>
                    <?php endif; ?>

                    <?php if ($subtitle !== ''): ?>
                        <p class="sp-hero-sub"><?= e($subtitle) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($chips)): ?>
                        <div class="sp-hero-chips">
                            <?php foreach ($chips as $chip): ?>
                                <span class="sp-hero-chip">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                                    <?= e($chip) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($btn1Text !== '' || $btn2Text !== ''): ?>
                        <div class="sp-hero-actions">
                            <?php if ($btn1Text !== ''): ?>
                                <a href="<?= e(resolve_hp_url($btn1Url, $lang)) ?>" class="sp-btn sp-btn-primary">
                                    <?= e($btn1Text) ?>
                                    <span aria-hidden="true"><?= $isFa ? '←' : '→' ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($btn2Text !== ''): ?>
                                <a href="<?= e(resolve_hp_url($btn2Url, $lang)) ?>" class="sp-btn sp-btn-outline">
                                    <?= e($btn2Text) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Showcase Visual with Offset Accent Frame & Floating Badge -->
                <div class="sp-hero-visual">
                    <div class="sp-hero-media-frame">
                        <?php if ($cornerTag !== ''): ?>
                            <span class="sp-hero-corner-tag"><?= e($cornerTag) ?></span>
                        <?php endif; ?>
                        <img src="<?= e(url($bgImg)) ?>" alt="<?= e($title) ?>" loading="eager">
                    </div>
                    <?php if ($badgeNum !== '' || $badgeTxt !== ''): ?>
                        <div class="sp-hero-float-card">
                            <div class="sp-hero-float-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15l-3.5 2 1-4L6 10l4-.5L12 6l2 3.5 4 .5-3.5 3 1 4z"/></svg>
                            </div>
                            <div class="sp-hero-float-text">
                                <?php if ($badgeNum !== ''): ?>
                                    <strong><?= e($badgeNum) ?></strong>
                                <?php endif; ?>
                                <?php if ($badgeTxt !== ''): ?>
                                    <span><?= e($badgeTxt) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Render the clean, light Bottom Call-to-Action Banner on subpages.
 */
function render_subpage_cta(string $lang, array $fields): void
{
    $title   = sp_field($fields, 'cta_title', $lang);
    $sub     = sp_field($fields, 'cta_sub', $lang);
    $btnText = sp_field($fields, 'cta_btn_text', $lang);
    $btnUrl  = sp_field($fields, 'cta_btn_url', 'en', 'contact.php');
    if ($title === '') {
        return;
    }
    ?>
    <section class="sp-cta-section">
        <div class="sp-container">
            <div class="sp-cta-box">
                <div class="sp-cta-copy">
                    <h2><?= e($title) ?></h2>
                    <?php if ($sub !== ''): ?>
                        <p><?= e($sub) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($btnText !== ''): ?>
                    <div class="sp-cta-action">
                        <a href="<?= e(resolve_hp_url($btnUrl, $lang)) ?>" class="sp-btn sp-btn-primary"><?= e($btnText) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

