<?php
/**
 * Public site footer (shared by EN + FA pages).
 * Expects: $lang, $dir.
 */

declare(strict_types=1);

$lang = $lang ?? DEFAULT_LANG;
$isFa = ($lang === 'fa');
$brand = setting('site_name', SITE_NAME);
$tagline = setting($isFa ? 'tagline_fa' : 'tagline_en', '');
$phone = setting('contact_phone', '');
$whatsapp = setting('contact_whatsapp', '');
$email = setting('contact_email', '');
$instagram = setting('instagram_url', '');
$youtube = setting('youtube_url', '');
$year = date('Y');

// Hide placeholder/seed values.
$showPhone = $phone !== '' && $phone !== '+60';
$showWhatsapp = $whatsapp !== '' && $whatsapp !== '+60';
$showEmail = $email !== '' && !str_ends_with($email, '@moris.example');

$menu = [
    '' => $isFa ? 'خانه' : 'Home',
    'about.php' => $isFa ? 'درباره' : 'About',
    'services.php' => $isFa ? 'خدمات' : 'Services',
    'packages.php' => $isFa ? 'پکیج‌ها' : 'Packages',
    'gallery.php' => $isFa ? 'گالری' : 'Gallery',
    'blog.php' => $isFa ? 'وبلاگ' : 'Blog',
    'contact.php' => $isFa ? 'تماس' : 'Contact',
];

$exploreLabel = $isFa ? 'پیوندها' : 'Explore';
$contactLabel = $isFa ? 'ارتباط' : 'Get in touch';
$whatsappText = preg_replace('/[^0-9]/', '', $whatsapp);
?>
</main>
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= e(url($lang . '/')) ?>"><?= e($brand) ?><span class="accent">.</span></a>
            <?php if ($tagline !== ''): ?>
                <p class="footer-tagline"><?= e($tagline) ?></p>
            <?php endif; ?>
            <div class="social">
                <?php if ($instagram !== ''): ?>
                    <a href="<?= e($instagram) ?>" class="social-link" target="_blank" rel="noopener" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($youtube !== ''): ?>
                    <a href="<?= e($youtube) ?>" class="social-link" target="_blank" rel="noopener" aria-label="YouTube">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.8-1.8C19.3 5 12 5 12 5s-7.3 0-8.8.5A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.8 1.8C4.7 19 12 19 12 19s7.3 0 8.8-.5a2.5 2.5 0 0 0 1.8-1.8C23 15.2 23 12 23 12zM9.8 15.3V8.7l6.2 3.3-6.2 3.3z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ($showWhatsapp): ?>
                    <a href="https://wa.me/<?= e($whatsappText) ?>" class="social-link" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.5-1.3-.7-1.8-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A3 3 0 0 0 6.3 9c0 1.8 1.3 3.5 1.5 3.7s2.5 3.9 6.1 5.2c2.2.8 2.6.6 3.1.6s1.4-.6 1.6-1.1.2-1 .1-1.1-.3-.2-.5-.3z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-col">
            <h4><?= e($exploreLabel) ?></h4>
            <ul>
                <?php foreach ($menu as $file => $label): ?>
                    <li><a href="<?= e(url($lang . '/' . $file)) ?>"><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4><?= e($contactLabel) ?></h4>
            <ul>
                <?php if ($showPhone): ?><li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a></li><?php endif; ?>
                <?php if ($showWhatsapp): ?><li><a href="https://wa.me/<?= e($whatsappText) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
                <?php if ($showEmail): ?><li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
                <?php if (!$showPhone && !$showWhatsapp && !$showEmail): ?>
                    <li><a href="<?= e(url($lang . '/contact.php')) ?>"><?= $isFa ? 'فرم تماس' : 'Contact form' ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <span>&copy; <?= e($year) ?> <?= e($brand) ?><?= $tagline !== '' ? ' — ' . e($tagline) : '' ?></span>
    </div>
</footer>
</body>
</html>
