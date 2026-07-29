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
$ttFooterDescription = 'Practical IT training in Madurai with free internships, spoken English support, live projects, certification and placement assistance.';
?>
<footer class="site-footer talent-footer" data-talent-footer>
    <div class="talent-footer__inner">
        <div class="talent-footer__grid" aria-label="Footer navigation">
            <section class="talent-footer__column talent-footer__brand" aria-label="Talentteno Institute">
                <a class="talent-footer__logo" href="index.php">
                    <span class="talent-footer__logo-mark"><img src="<?= tt_h(tt_asset_url('assets/images/logot-transparent.png')) ?>" alt="<?= tt_h($settings['site_name']) ?> logo" loading="lazy" decoding="async" width="96" height="96"></span>
                    <span class="talent-footer__logo-copy"><strong><?= tt_h($settings['site_name']) ?></strong><span>IT TRAINING INSTITUTE</span></span>
                </a>
                <p class="talent-footer__description"><?= tt_h($ttFooterDescription) ?></p>
                <div class="talent-footer__badges" aria-label="Training highlights">
                    <span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Job Assistance</span>
                    <span><i class="fa-solid fa-laptop-code" aria-hidden="true"></i> Live Projects</span>
                    <span><i class="fa-solid fa-certificate" aria-hidden="true"></i> Certification</span>
                </div>
                <div class="talent-footer__social" aria-label="Talentteno social media">
                    <a class="talent-footer__social-link" href="<?= tt_h($facebookUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                    <a class="talent-footer__social-link" href="<?= tt_h($instagramUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                    <a class="talent-footer__social-link" href="<?= tt_h($linkedinUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
                    <a class="talent-footer__social-link" href="<?= tt_h($youtubeUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow Talentteno on YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
                </div>
            </section>
            <nav class="talent-footer__column" aria-label="Explore">
                <h3>Explore</h3>
                <ul class="talent-footer__links">
                    <li><a class="talent-footer__link" href="index.php">Home</a></li>
                    <li><a class="talent-footer__link" href="about.php">About</a></li>
                    <li><a class="talent-footer__link" href="course.php">Courses</a></li>
                    <li><a class="talent-footer__link" href="services.php">Services</a></li>
                    <li><a class="talent-footer__link" href="career.php">Career</a></li>
                    <li><a class="talent-footer__link" href="project.php">Projects</a></li>
                    <li><a class="talent-footer__link" href="blog.php">Blog</a></li>
                    <li><a class="talent-footer__link" href="gallery.php">Gallery</a></li>
                    <li><a class="talent-footer__link" href="contact.php">Contact</a></li>
                </ul>
            </nav>
            <nav class="talent-footer__column" aria-label="Popular Courses">
                <h3>Popular Courses</h3>
                <ul class="talent-footer__links">
                    <li><a class="talent-footer__link" href="course.php?course=Data%20Science">Data Science</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Full%20Stack%20with%20AI">Full Stack with AI</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Digital%20Marketing">Digital Marketing</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Cyber%20Security">Cyber Security</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=UI%20%2F%20UX%20Design">UI / UX Design</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Python%20Programming">Python Programming</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Software%20Testing">Software Testing</a></li>
                    <li><a class="talent-footer__link" href="course.php?course=Web%20Development">Web Development</a></li>
                </ul>
            </nav>
            <nav class="talent-footer__column" aria-label="Quick Links">
                <h3>Quick Links</h3>
                <ul class="talent-footer__links">
                    <li><a class="talent-footer__link" href="offers.php">Offers</a></li>
                    <li><a class="talent-footer__link" href="#home-signup" data-enroll-trigger>Enroll Now</a></li>
                    <li><a class="talent-footer__link" href="download.php">Downloads</a></li>
                    <li><a class="talent-footer__link" href="contact.php?topic=Free%20Counselling">Free Counselling</a></li>
                    <li><a class="talent-footer__link" href="contact.php?topic=FAQs">FAQs</a></li>
                    <li><a class="talent-footer__link" href="contact.php?topic=Privacy%20Policy">Privacy Policy</a></li>
                    <li><a class="talent-footer__link" href="contact.php?topic=Terms%20%26%20Conditions">Terms &amp; Conditions</a></li>
                </ul>
            </nav>
            <section class="talent-footer__column talent-footer__contact" aria-label="Contact Talentteno">
                <h3>Contact Us</h3>
                <address class="talent-footer__address">
                    <a class="talent-footer__contact-link" href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?= tt_h($settings['address']) ?></span></a>
                    <a class="talent-footer__contact-link" href="<?= tt_h($phone1Href) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><?= tt_h($settings['phone1']) ?></span></a>
                    <?php if (($settings['phone2'] ?? '') !== ''): ?>
                    <a class="talent-footer__contact-link" href="<?= tt_h($phone2Href) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><?= tt_h($settings['phone2']) ?></span></a>
                    <?php endif; ?>
                    <a class="talent-footer__contact-link" href="mailto:<?= tt_h($settings['email']) ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><?= tt_h($settings['email']) ?></span></a>
                </address>
                <p class="talent-footer__follow-title">Follow Us</p>
                <div class="talent-footer__quick-social" aria-label="Quick contact actions">
                    <a href="<?= tt_h($instagramUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                    <a href="<?= tt_h($phone1Href) ?>" aria-label="Call Talentteno"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>
                    <a href="<?= tt_h($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
                </div>
            </section>
        </div>
        <div class="talent-footer__features" aria-label="Talentteno training benefits">
            <div class="talent-footer__feature"><i class="fa-solid fa-user-tie" aria-hidden="true"></i><span><strong>Industry Expert Trainers</strong><small>Learn from real-world experts</small></span></div>
            <div class="talent-footer__feature"><i class="fa-solid fa-handshake-angle" aria-hidden="true"></i><span><strong>100% Job Assistance</strong><small>Placement support guaranteed</small></span></div>
            <div class="talent-footer__feature"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span><strong>Live Projects</strong><small>Work on real-time projects</small></span></div>
            <div class="talent-footer__feature"><i class="fa-solid fa-award" aria-hidden="true"></i><span><strong>Certificates</strong><small>Industry recognized certificates</small></span></div>
        </div>
    </div>
    <div class="talent-footer__bottom">
        <div class="talent-footer__bottom-inner">
            <p>© 2026 Talentteno Institute | All Rights Reserved</p>
            <nav class="talent-footer__legal" aria-label="Footer legal links">
                <a href="contact.php?topic=Privacy%20Policy">Privacy Policy</a>
                <a href="contact.php?topic=Terms%20%26%20Conditions">Terms &amp; Conditions</a>
                <a href="contact.php?topic=Refund%20Policy">Refund Policy</a>
            </nav>
        </div>
    </div>
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
