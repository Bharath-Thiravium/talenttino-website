<?php
if (!isset($settings) || !is_array($settings)) {
    $settings = tt_settings();
}

$phone1Link = tt_phone_digits($settings['phone1'] ?? '');
$phone2Link = tt_phone_digits($settings['phone2'] ?? '');
$phone1Href = tt_phone_href($settings['phone1'] ?? '');
$phone2Href = tt_phone_href($settings['phone2'] ?? '');
$whatsappUrl = tt_whatsapp_url($settings);
$mapUrl = function_exists('tt_google_maps_url')
    ? tt_google_maps_url($settings)
    : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(trim('Talentteno Institute, ' . (string)($settings['address'] ?? '')));
$facebookUrl = tt_social_url($settings['facebook_url'] ?? '', 'facebook');
$instagramUrl = tt_social_url($settings['instagram_url'] ?? '', 'instagram');
$linkedinUrl = tt_social_url($settings['linkedin_url'] ?? '', 'linkedin');
$youtubeUrl = tt_social_url($settings['youtube_url'] ?? '', 'youtube');
$ttActivePage = basename($_SERVER['PHP_SELF'] ?? '');
$ttRenderSharedEnrollModal = $ttActivePage !== 'index.php';
$ttEnrollFormResult = $homeFormResult ?? null;

if ($ttRenderSharedEnrollModal && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_source'] ?? '') === 'home_signup') {
    $ttEnrollFormResult = tt_submit_enquiry($_POST, 'enquiry');
}

$ttEnrollCourses = isset($allCourses) && is_array($allCourses) ? $allCourses : tt_courses();
if ($ttEnrollCourses === []) {
    $ttEnrollCourses = [
        ['title' => 'Full Stack Development'],
        ['title' => 'Data Science & AI'],
        ['title' => 'Cyber Security'],
        ['title' => 'Digital Marketing'],
        ['title' => 'UI / UX Design'],
        ['title' => 'Cloud Computing'],
    ];
}
$ttCurrentAction = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php') ?: 'index.php';
?>
<style>
html body .site-footer .footer-logo,html body.home-page .site-footer .footer-logo,html body.static-site .site-footer .footer-logo{grid-template-columns:112px minmax(0,1fr)!important;gap:20px!important;align-items:center!important}
html body .site-footer .footer-logo-mark,html body.home-page .site-footer .footer-logo-mark,html body.static-site .site-footer .footer-logo-mark{width:112px!important;height:112px!important;min-width:112px!important;padding:4px!important;border-radius:50%!important;background:#fff!important;box-shadow:0 22px 50px rgba(0,9,24,.28)!important;overflow:hidden!important;clip-path:circle(50% at 50% 50%)!important}
html body .site-footer .footer-logo-mark img,html body.home-page .site-footer .footer-logo-mark img,html body.static-site .site-footer .footer-logo-mark img{width:104px!important;height:104px!important;object-fit:contain!important;border-radius:50%!important;background:#fff!important}
html body .site-footer .footer-logo strong,html body.home-page .site-footer .footer-logo strong,html body.static-site .site-footer .footer-logo strong{font-size:clamp(34px,2.7vw,44px)!important;line-height:1!important}
html body .site-footer .footer-social .social-call{width:48px!important;min-width:48px!important;padding:0!important}
html body .site-footer .footer-social .social-call-number{display:none!important}
@media(max-width:760px){html body .site-footer .footer-logo,html body.home-page .site-footer .footer-logo,html body.static-site .site-footer .footer-logo{grid-template-columns:92px minmax(0,1fr)!important;gap:16px!important}html body .site-footer .footer-logo-mark,html body.home-page .site-footer .footer-logo-mark,html body.static-site .site-footer .footer-logo-mark{width:92px!important;height:92px!important;min-width:92px!important;padding:4px!important;border-radius:50%!important;background:#fff!important}html body .site-footer .footer-logo-mark img,html body.home-page .site-footer .footer-logo-mark img,html body.static-site .site-footer .footer-logo-mark img{width:84px!important;height:84px!important;background:#fff!important}html body .site-footer .footer-logo strong,html body.home-page .site-footer .footer-logo strong,html body.static-site .site-footer .footer-logo strong{font-size:clamp(27px,7vw,34px)!important}}
@media(max-width:420px){html body .site-footer .footer-logo,html body.home-page .site-footer .footer-logo,html body.static-site .site-footer .footer-logo{grid-template-columns:84px minmax(0,1fr)!important;gap:14px!important}html body .site-footer .footer-logo-mark,html body.home-page .site-footer .footer-logo-mark,html body.static-site .site-footer .footer-logo-mark{width:84px!important;height:84px!important;min-width:84px!important;padding:4px!important;background:#fff!important}html body .site-footer .footer-logo-mark img,html body.home-page .site-footer .footer-logo-mark img,html body.static-site .site-footer .footer-logo-mark img{width:76px!important;height:76px!important;background:#fff!important}}
</style>
<footer class="site-footer">
    <div class="site-container footer-grid">
        <div class="footer-brand-block">
            <a class="footer-logo" href="index.php">
                <span class="brand-mark footer-logo-mark"><img src="<?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w128.webp')) ?>" alt="<?= tt_h($settings['site_name']) ?> logo" loading="lazy" decoding="async" width="104" height="104"></span>
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
                <li><a href="<?= tt_h($phone1Href) ?>"><i class="fa-solid fa-phone"></i> <?= tt_h($settings['phone1']) ?></a></li>
                <?php if (($settings['phone2'] ?? '') !== ''): ?><li><a href="<?= tt_h($phone2Href) ?>"><i class="fa-solid fa-phone"></i> <?= tt_h($settings['phone2']) ?></a></li><?php endif; ?>
                <li><a href="mailto:<?= tt_h($settings['email']) ?>"><i class="fa-solid fa-envelope"></i> <?= tt_h($settings['email']) ?></a></li>
            </ul>
            <p class="footer-social-title">Follow us</p>
            <div class="footer-social">
                <a class="social-facebook" href="<?= tt_h($facebookUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                <a class="social-instagram" href="<?= tt_h($instagramUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                <a class="social-linkedin" href="<?= tt_h($linkedinUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
                <a class="social-call" href="<?= tt_h($phone1Href) ?>" aria-label="Call Talentteno at <?= tt_h($settings['phone1']) ?>" title="<?= tt_h($settings['phone1']) ?>" data-copy-phone="<?= tt_h($settings['phone1']) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>
                <a class="social-youtube" href="<?= tt_h($youtubeUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
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
<?php if ($ttRenderSharedEnrollModal): ?>
<div class="site-enroll-modal home-enroll-modal" id="home-signup" role="dialog" aria-modal="true" aria-labelledby="home-signup-title" aria-hidden="true">
    <button class="home-enroll-close" type="button" data-enroll-close aria-label="Close enrolment form"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    <h2 id="home-signup-title">Sign Up for Free Counselling</h2>
    <p>Share your details. Our admission counsellor will contact you.</p>
    <?php if ($ttEnrollFormResult): ?>
    <div class="form-alert <?= $ttEnrollFormResult['ok'] ? 'success' : 'error' ?>" role="<?= $ttEnrollFormResult['ok'] ? 'status' : 'alert' ?>"><?= tt_h($ttEnrollFormResult['message']) ?></div>
    <?php endif; ?>
    <form class="home-counselling-form" method="POST" action="<?= tt_h($ttCurrentAction) ?>#home-signup">
        <input type="hidden" name="form_source" value="home_signup">
        <input type="hidden" name="message" value="Site enrolment form - free course counselling request.">
        <label class="sr-only" for="site-enroll-name">Your full name</label>
        <input id="site-enroll-name" type="text" name="name" placeholder="Your Full Name" autocomplete="name" minlength="2" maxlength="80" required>
        <label class="sr-only" for="site-enroll-phone">Phone number</label>
        <input id="site-enroll-phone" type="tel" name="phone" placeholder="10 Digit Mobile Number" autocomplete="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" required>
        <label class="sr-only" for="site-enroll-email">Email address</label>
        <input id="site-enroll-email" type="email" name="email" placeholder="Email Address" autocomplete="email" maxlength="190" required>
        <label class="sr-only" for="site-enroll-course">Course of interest</label>
        <select id="site-enroll-course" name="course" required>
            <option value="">Select Course</option>
            <?php foreach ($ttEnrollCourses as $course): ?>
            <option><?= tt_h($course['title'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        <button type="submit">Sign Up Now</button>
    </form>
</div>
<?php endif; ?>
