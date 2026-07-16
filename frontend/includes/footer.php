<?php
if (!isset($settings) || !is_array($settings)) {
    $settings = tt_settings();
}

$phone1Link = preg_replace('/\D+/', '', (string)($settings['phone1'] ?? ''));
$phone2Link = preg_replace('/\D+/', '', (string)($settings['phone2'] ?? ''));
$mapQuery = rawurlencode((string)($settings['address'] ?? ''));
?>
<style>
    html body .site-footer {
        position: relative !important;
        overflow: hidden !important;
        isolation: isolate !important;
        color: #fff !important;
        background:
            radial-gradient(circle at 96% 0%, rgba(239, 0, 151, .78) 0 9%, rgba(164, 30, 174, .38) 18%, transparent 42%),
            radial-gradient(circle at 28% 18%, rgba(0, 126, 255, .32), transparent 34%),
            linear-gradient(130deg, #003f9f 0%, #02256f 34%, #02195c 62%, #08194d 100%) !important;
        background-size: 100% 100% !important;
        font-family: "Plus Jakarta Sans", Arial, sans-serif !important;
    }

    html body .site-footer::before {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        pointer-events: none !important;
        z-index: 0 !important;
        opacity: .62 !important;
        background:
            radial-gradient(circle, rgba(0, 168, 255, .48) 1.3px, transparent 1.7px) left 0 top 0 / 16px 16px,
            radial-gradient(circle, rgba(217, 28, 246, .24) 1.2px, transparent 1.7px) right 0 top 0 / 16px 16px,
            repeating-radial-gradient(ellipse at 100% 100%, transparent 0 42px, rgba(255, 0, 180, .34) 43px 44px, transparent 45px 58px),
            repeating-radial-gradient(ellipse at 0% 100%, transparent 0 48px, rgba(0, 168, 255, .30) 49px 50px, transparent 51px 66px) !important;
        animation: ttFooterPattern 24s linear infinite !important;
    }

    html body .site-footer::after {
        content: "" !important;
        position: absolute !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 76px !important;
        height: 1px !important;
        pointer-events: none !important;
        z-index: 0 !important;
        opacity: .66 !important;
        background: linear-gradient(90deg, rgba(0, 168, 255, .56), rgba(255, 255, 255, .34), rgba(239, 0, 151, .58)) !important;
        box-shadow: 0 0 18px rgba(0, 168, 255, .36), 0 0 24px rgba(239, 0, 151, .28) !important;
    }

    html body .site-footer > * {
        position: relative !important;
        z-index: 1 !important;
    }

    html body .site-footer .footer-grid {
        width: min(1260px, calc(100% - 40px)) !important;
        display: grid !important;
        grid-template-columns: minmax(330px, 1.25fr) minmax(170px, .72fr) minmax(240px, .92fr) minmax(340px, 1.2fr) !important;
        align-items: start !important;
        gap: clamp(24px, 2.6vw, 42px) !important;
        padding: 58px 0 82px !important;
        margin-inline: auto !important;
    }

    html body .site-footer .footer-brand-block,
    html body .site-footer .footer-panel {
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        animation: ttFooterSlideFromLeft .82s cubic-bezier(.16,1,.3,1) both !important;
    }

    html body .site-footer .footer-panel:nth-child(2) {
        animation-name: ttFooterSlideFromRight !important;
        animation-delay: .08s !important;
    }

    html body .site-footer .footer-panel:nth-child(3) {
        animation-name: ttFooterSlideFromLeft !important;
        animation-delay: .16s !important;
    }

    html body .site-footer .footer-panel:nth-child(4) {
        animation-name: ttFooterSlideFromRight !important;
        animation-delay: .24s !important;
    }

    html body .site-footer .footer-logo,
    html body .site-footer .footer-brand-block p,
    html body .site-footer .footer-badges span,
    html body .site-footer .footer-panel h3,
    html body .site-footer .footer-links a,
    html body .site-footer .footer-contact-list a,
    html body .site-footer .footer-social-title,
    html body .site-footer .footer-social a {
        animation: ttFooterItemIn .64s cubic-bezier(.16,1,.3,1) both !important;
    }

    html body .site-footer .footer-links li:nth-child(2) a,
    html body .site-footer .footer-contact-list li:nth-child(2) a,
    html body .site-footer .footer-badges span:nth-child(2),
    html body .site-footer .footer-social a:nth-child(2) {
        animation-delay: .08s !important;
    }

    html body .site-footer .footer-links li:nth-child(3) a,
    html body .site-footer .footer-contact-list li:nth-child(3) a,
    html body .site-footer .footer-badges span:nth-child(3),
    html body .site-footer .footer-social a:nth-child(3) {
        animation-delay: .16s !important;
    }

    html body .site-footer .footer-links li:nth-child(n+4) a,
    html body .site-footer .footer-contact-list li:nth-child(n+4) a,
    html body .site-footer .footer-social a:nth-child(n+4) {
        animation-delay: .24s !important;
    }

    html body .site-footer .footer-panel {
        min-height: 315px !important;
        padding-left: clamp(22px, 2vw, 34px) !important;
        border-left: 1px solid rgba(255,255,255,.22) !important;
    }

    html body .site-footer .footer-logo {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        margin: 0 0 30px !important;
        text-decoration: none !important;
    }

    html body .site-footer .footer-logo-mark {
        width: 62px !important;
        height: 62px !important;
        flex: 0 0 62px !important;
        border-radius: 12px !important;
        background: #fff !important;
        border: 1px solid rgba(255,255,255,.8) !important;
        box-shadow: 0 18px 38px rgba(0, 13, 78, .32), 0 0 24px rgba(0, 168, 255, .18) !important;
    }

    html body .site-footer .footer-logo-mark img {
        width: 86% !important;
        height: 86% !important;
        object-fit: contain !important;
        filter: none !important;
    }

    html body .site-footer .footer-logo strong,
    html body .site-footer .footer-panel h3,
    html body .site-footer .footer-social-title {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }

    html body .site-footer .footer-logo strong {
        display: block !important;
        max-width: 210px !important;
        font-size: 27px !important;
        line-height: 1.03 !important;
        font-weight: 900 !important;
        letter-spacing: 0 !important;
        text-shadow: 0 14px 36px rgba(0, 0, 0, .28) !important;
    }

    html body .site-footer .footer-logo > span > span {
        display: block !important;
        margin-top: 8px !important;
        color: rgba(255,255,255,.88) !important;
        -webkit-text-fill-color: rgba(255,255,255,.88) !important;
        font-size: 10px !important;
        line-height: 1 !important;
        font-weight: 900 !important;
        letter-spacing: 4px !important;
        white-space: nowrap !important;
    }

    html body .site-footer .footer-brand-block p,
    html body .site-footer .footer-links a,
    html body .site-footer .footer-contact-list a,
    html body .site-footer .footer-logo > span > span {
        color: rgba(255,255,255,.88) !important;
        -webkit-text-fill-color: rgba(255,255,255,.88) !important;
    }

    html body .site-footer .footer-brand-block p {
        max-width: 315px !important;
        margin: 0 0 28px !important;
        font-size: 14px !important;
        line-height: 1.75 !important;
        font-weight: 500 !important;
    }

    html body .site-footer .footer-panel h3 {
        position: relative !important;
        margin: 0 0 24px !important;
        font-size: 17px !important;
        line-height: 1.1 !important;
        font-weight: 900 !important;
        letter-spacing: 0 !important;
    }

    html body .site-footer .footer-panel h3::after {
        content: "" !important;
        display: block !important;
        width: 86px !important;
        height: 4px !important;
        margin-top: 10px !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, #009dff 0%, #6b5cff 52%, #ff1fbd 100%) !important;
        box-shadow: 0 0 20px rgba(0, 168, 255, .32), 0 0 18px rgba(255, 31, 189, .28) !important;
    }

    html body .site-footer .footer-links,
    html body .site-footer .footer-contact-list {
        display: grid !important;
        gap: 14px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    html body .site-footer .footer-links a {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-height: 24px !important;
        color: rgba(255,255,255,.88) !important;
        -webkit-text-fill-color: rgba(255,255,255,.88) !important;
        font-size: 14px !important;
        line-height: 1.25 !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        white-space: nowrap !important;
        transition: color .24s ease, transform .24s ease !important;
    }

    html body .site-footer .footer-links a::before {
        content: "\203A" !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 12px !important;
        min-width: 12px !important;
        color: #ff31c7 !important;
        -webkit-text-fill-color: #ff31c7 !important;
        font-size: 25px !important;
        line-height: 1 !important;
        font-weight: 900 !important;
        text-shadow: -5px 0 0 #168fff !important;
    }

    html body .site-footer .footer-links a:hover,
    html body .site-footer .footer-contact-list a:hover {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        transform: translateX(8px) !important;
    }

    html body .site-footer .footer-contact-list {
        gap: 14px !important;
    }

    html body .site-footer .footer-contact-list a {
        display: grid !important;
        grid-template-columns: 42px minmax(0, 1fr) !important;
        align-items: center !important;
        gap: 14px !important;
        color: rgba(255,255,255,.92) !important;
        -webkit-text-fill-color: rgba(255,255,255,.92) !important;
        font-size: 13px !important;
        line-height: 1.55 !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        word-break: normal !important;
        overflow-wrap: break-word !important;
        padding-bottom: 14px !important;
        border-bottom: 1px solid rgba(255,255,255,.08) !important;
    }

    html body .site-footer .footer-contact-list li:last-child a {
        border-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    html body .site-footer .footer-contact-list i {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: linear-gradient(135deg, #0a8cff 0%, #6653ff 52%, #e82ed5 100%) !important;
        box-shadow: 0 14px 34px rgba(0, 0, 0, .22), inset 0 1px 0 rgba(255,255,255,.32) !important;
    }

    html body .site-footer .footer-badges span,
    html body .site-footer .footer-social a {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        background: rgba(0, 92, 214, .18) !important;
        border: 1px solid rgba(0, 168, 255, .76) !important;
        box-shadow: inset 0 0 0 1px rgba(255, 31, 189, .36), 0 12px 28px rgba(0, 0, 0, .14) !important;
    }

    html body .site-footer .footer-badges {
        width: min(100%, 330px) !important;
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 12px !important;
    }

    html body .site-footer .footer-badges span {
        width: 100% !important;
        min-width: 0 !important;
        min-height: 86px !important;
        padding: 11px 8px !important;
        border-radius: 12px !important;
        gap: 7px !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        line-height: 1.12 !important;
        white-space: normal !important;
        text-align: center !important;
        overflow-wrap: normal !important;
        word-break: normal !important;
    }

    html body .site-footer .footer-badges span i {
        font-size: 19px !important;
        margin-bottom: 2px !important;
    }

    html body .site-footer .footer-badges span:hover,
    html body .site-footer .footer-social a:hover,
    html body .site-footer .footer-social a:focus-visible {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: linear-gradient(135deg, #0a8cff, #e82ed5) !important;
        transform: translateY(-5px) !important;
    }

    html body .site-footer .footer-social-title {
        margin: 30px 0 16px !important;
        font-size: 15px !important;
        font-weight: 900 !important;
    }

    html body .site-footer .footer-social {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    html body .site-footer .footer-social a {
        width: 48px !important;
        height: 48px !important;
        min-width: 48px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        font-size: 18px !important;
        text-decoration: none !important;
        background: rgba(4, 30, 100, .42) !important;
        border: 2px solid #168fff !important;
        box-shadow: inset 0 0 0 1px rgba(255, 31, 189, .72), 0 16px 36px rgba(0, 0, 0, .22) !important;
    }

    html body .site-footer .footer-bottom {
        position: relative !important;
        z-index: 1 !important;
        background: rgba(0, 24, 92, .18) !important;
        border-top: 0 !important;
    }

    html body .site-footer .footer-bottom .site-container {
        min-height: 88px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }

    html body .site-footer .footer-bottom p {
        margin: 0 !important;
        color: rgba(255,255,255,.9) !important;
        -webkit-text-fill-color: rgba(255,255,255,.9) !important;
        font-size: 14px !important;
        font-weight: 500 !important;
    }

    @keyframes ttFooterGradient {
        from { background-position: 0% 50%; }
        to { background-position: 100% 50%; }
    }

    @keyframes ttFooterPattern {
        from { background-position: 0 0, 0 0, right bottom, left bottom; }
        to { background-position: 0 32px, 0 -32px, right bottom, left bottom; }
    }

    @keyframes ttFooterSlideFromLeft {
        from { opacity: 0; transform: translateX(-48px) scale(.985); }
        to { opacity: 1; transform: translateX(0) scale(1); }
    }

    @keyframes ttFooterSlideFromRight {
        from { opacity: 0; transform: translateX(48px) scale(.985); }
        to { opacity: 1; transform: translateX(0) scale(1); }
    }

    @keyframes ttFooterItemIn {
        from { opacity: 0; transform: translateX(-18px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @media (max-width: 1100px) {
        html body .site-footer .footer-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 42px !important;
        }

        html body .site-footer .footer-panel {
            min-height: 0 !important;
            padding-left: 0 !important;
            border-left: 0 !important;
        }
    }

    @media (max-width: 700px) {
        html body .site-footer .footer-grid {
            width: min(100% - 30px, 520px) !important;
            grid-template-columns: 1fr !important;
            gap: 42px !important;
            padding: 56px 0 46px !important;
        }

        html body .site-footer .footer-logo {
            align-items: flex-start !important;
            gap: 14px !important;
        }

        html body .site-footer .footer-logo-mark {
            width: 70px !important;
            height: 70px !important;
            flex-basis: 70px !important;
        }

        html body .site-footer .footer-logo strong {
            font-size: 28px !important;
        }

        html body .site-footer .footer-logo > span > span {
            font-size: 10px !important;
            letter-spacing: 4px !important;
            white-space: normal !important;
        }

        html body .site-footer .footer-brand-block p,
        html body .site-footer .footer-links a,
        html body .site-footer .footer-contact-list a {
            font-size: 16px !important;
        }

        html body .site-footer .footer-links a {
            white-space: normal !important;
        }

        html body .site-footer .footer-panel h3 {
            font-size: 22px !important;
            margin-bottom: 24px !important;
        }

        html body .site-footer .footer-contact-list a {
            grid-template-columns: 48px minmax(0, 1fr) !important;
            gap: 14px !important;
        }

        html body .site-footer .footer-contact-list i {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
        }

        html body .site-footer .footer-badges {
            display: grid !important;
            width: min(100%, 330px) !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        html body .site-footer .footer-badges span {
            min-height: 82px !important;
            padding: 10px 7px !important;
            font-size: 10px !important;
            line-height: 1.12 !important;
        }

        html body .site-footer .footer-social {
            gap: 14px !important;
            flex-wrap: wrap !important;
        }

        html body .site-footer .footer-social a {
            width: 54px !important;
            height: 54px !important;
            min-width: 54px !important;
            font-size: 21px !important;
        }

        html body .site-footer .footer-bottom p {
            font-size: 15px !important;
        }
    }
</style>
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
                <li><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= tt_h($mapQuery) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i> <?= tt_h($settings['address']) ?></a></li>
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
    <div class="whatsapp-enquiry-panel" id="whatsappEnquiryPanel" aria-hidden="true">
        <div class="whatsapp-enquiry-head">
            <span><i class="fa-brands fa-whatsapp"></i> Quick Enquiry</span>
            <button type="button" data-close-whatsapp aria-label="Close WhatsApp enquiry"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p>Share your details and continue on WhatsApp.</p>
        <form class="whatsapp-enquiry-form" data-whatsapp-number="<?= tt_h($phone1Link) ?>">
            <label><span>Name</span><input type="text" name="name" placeholder="Your name" maxlength="80" required></label>
            <label><span>Phone</span><input type="tel" name="phone" placeholder="10 digit mobile number" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" required></label>
            <label><span>Course</span><input type="text" name="course" placeholder="Course interested in" maxlength="120" required></label>
            <label><span>Message</span><textarea name="message" placeholder="Your learning goal" maxlength="500"></textarea></label>
            <button type="submit"><i class="fa-brands fa-whatsapp"></i> Continue on WhatsApp</button>
        </form>
    </div>
    <a class="whatsapp-float" href="https://wa.me/<?= tt_h($phone1Link) ?>?text=<?= rawurlencode('Hello Talentteno, I would like course information.') ?>" target="_blank" rel="noopener noreferrer" aria-label="Open WhatsApp chat">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
    <button class="scroll-top" type="button" aria-label="Scroll back to top" title="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>
</div>
