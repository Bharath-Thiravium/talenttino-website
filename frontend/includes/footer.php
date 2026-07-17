<?php
if (!isset($settings) || !is_array($settings)) {
    $settings = tt_settings();
}

$phone1Link = preg_replace('/\D+/', '', (string)($settings['phone1'] ?? ''));
$phone2Link = preg_replace('/\D+/', '', (string)($settings['phone2'] ?? ''));
$whatsappUrl = 'https://web.whatsapp.com/send?phone=' . $phone1Link . '&text=' . rawurlencode('Hello Talentteno, I would like course information.');
$mapUrl = function_exists('tt_google_maps_url')
    ? tt_google_maps_url($settings)
    : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(trim('Talentteno Institute, ' . (string)($settings['address'] ?? '')));
?>
<footer class="site-footer">
    <div class="site-container footer-grid">
        <div class="footer-brand-block">
            <a class="footer-logo" href="index.php">
                <span class="brand-mark footer-logo-mark"><img src="assets/images/logot-transparent.png" alt="<?= tt_h($settings['site_name']) ?> logo" loading="lazy" decoding="async"></span>
                <span><strong><?= tt_h($settings['site_name']) ?></strong><span>IT TRAINING INSTITUTE</span></span>
            </a>
            <p><?= tt_h($settings['footer_description']) ?></p>
            <div class="footer-badges"><span><i class="fa-solid fa-briefcase"></i> Job Assistance</span><span><i class="fa-solid fa-laptop-code"></i> Live Projects</span><span><i class="fa-solid fa-certificate"></i> Certification</span></div>
        </div>
        <div class="footer-panel">
            <h3>Explore</h3>
            <ul class="footer-links"><li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="course.php">Courses</a></li><li><a href="services.php">Services</a></li><li><a href="career.php">Career</a></li><li><a href="project.php">Projects</a></li><li><a href="blog.php">Blog</a></li><li><a href="gallery.php">Gallery</a></li></ul>
        </div>
        <div class="footer-panel">
            <h3>Popular Courses</h3>
            <ul class="footer-links"><li><a href="course.php">Data Science</a></li><li><a href="course.php">Full Stack with AI</a></li><li><a href="course.php">Digital Marketing</a></li><li><a href="course.php">Cyber Security</a></li><li><a href="course.php">UI / UX Design</a></li></ul>
        </div>
        <div class="footer-panel">
            <h3>Contact</h3>
            <ul class="footer-contact-list">
                <li><a href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener noreferrer" title="Open Talentteno Institute location in Google Maps"><i class="fa-solid fa-location-dot"></i> <?= tt_h($settings['address']) ?></a></li>
                <li><a href="tel:+<?= tt_h($phone1Link) ?>"><i class="fa-solid fa-phone"></i> <?= tt_h($settings['phone1']) ?></a></li>
                <?php if (($settings['phone2'] ?? '') !== ''): ?><li><a href="tel:+<?= tt_h($phone2Link) ?>"><i class="fa-solid fa-phone"></i> <?= tt_h($settings['phone2']) ?></a></li><?php endif; ?>
                <li><a href="mailto:<?= tt_h($settings['email']) ?>"><i class="fa-solid fa-envelope"></i> <?= tt_h($settings['email']) ?></a></li>
            </ul>
            <p class="footer-social-title">Follow us</p>
            <div class="footer-social">
                <a class="social-facebook" href="<?= tt_h(!empty($settings['facebook_url']) && $settings['facebook_url'] !== '#' ? $settings['facebook_url'] : 'https://www.facebook.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                <a class="social-instagram" href="<?= tt_h(!empty($settings['instagram_url']) && $settings['instagram_url'] !== '#' ? $settings['instagram_url'] : 'https://www.instagram.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                <a class="social-linkedin" href="<?= tt_h(!empty($settings['linkedin_url']) && $settings['linkedin_url'] !== '#' ? $settings['linkedin_url'] : 'https://www.linkedin.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
                <a class="social-youtube" href="<?= tt_h(!empty($settings['youtube_url']) && $settings['youtube_url'] !== '#' ? $settings['youtube_url'] : 'https://www.youtube.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom"><div class="site-container"><p><?= tt_h($settings['footer_copyright']) ?></p></div></div>
</footer>
<div class="floating-actions" aria-label="Quick actions">
    <a class="whatsapp-float" href="<?= tt_h($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open WhatsApp chat">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
    <button class="scroll-top" type="button" aria-label="Scroll back to top" title="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>
</div>
