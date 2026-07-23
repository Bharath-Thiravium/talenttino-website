<?php
require_once __DIR__ . '/includes/site-data.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_source'] ?? '') === 'franchise_modal') {
    header('Content-Type: application/json; charset=UTF-8');

    $title = trim((string)($_POST['item_title'] ?? 'Franchise Enquiry'));
    $city = trim((string)($_POST['city'] ?? ''));
    $background = trim((string)($_POST['business_background'] ?? ''));
    $interest = trim((string)($_POST['interest'] ?? ''));
    $messageParts = [
        'Franchise item: ' . ($title !== '' ? $title : 'Franchise Enquiry'),
        'City / Location: ' . ($city !== '' ? $city : '-'),
        'Business Background: ' . ($background !== '' ? $background : '-'),
        'Interest: ' . ($interest !== '' ? $interest : '-'),
    ];

    $payload = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'course' => 'Franchise - ' . ($title !== '' ? $title : 'General Enquiry'),
        'message' => implode("\n", $messageParts),
        'website' => $_POST['website'] ?? '',
    ];

    $result = tt_submit_enquiry($payload, 'enquiry');
    http_response_code($result['ok'] ? 201 : 422);
    echo json_encode([
        'ok' => (bool)$result['ok'],
        'message' => $result['message'] ?? ($result['ok'] ? 'Enquiry submitted successfully.' : 'Unable to submit enquiry.'),
    ]);
    exit;
}

$items = tt_franchise_items();
$items = $items ?: [
    ['icon' => 'fa-handshake', 'title' => 'Institute Partnership', 'short_desc' => 'Discuss Talentteno training centre partnership.', 'description' => 'Share your city, space and plan. Our team will explain the partnership workflow and next steps.', 'image' => 'assets/images/contact-counsellor-hero.png'],
    ['icon' => 'fa-chalkboard-teacher', 'title' => 'Training Model', 'short_desc' => 'Course structure, counselling and student support.', 'description' => 'Understand how practical course content, counselling, trainer coordination and student support are handled.', 'image' => 'assets/images/home.webp'],
    ['icon' => 'fa-bullhorn', 'title' => 'Brand Support', 'short_desc' => 'Guidance for local admissions and promotion.', 'description' => 'Get clarity on brand usage, enquiry handling, admission process and local centre operations.', 'image' => 'assets/images/home1.webp'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Franchise Enquiry | Talentteno Institute', 'description' => 'Talentteno Institute franchise and training centre partnership enquiry.', 'canonical' => tt_abs_url('franchise.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260723-franchisealign1">
    <style>
        .franchise-enquiry-overlay{position:fixed;inset:0;z-index:30000;display:none;align-items:center;justify-content:center;width:100%;height:100vh;height:100dvh;padding:calc(var(--header-height,90px) + 18px) clamp(12px,3vw,28px) clamp(14px,3vh,28px);background:linear-gradient(135deg,rgba(8,19,43,.82),rgba(20,20,70,.78));backdrop-filter:blur(13px);overflow:hidden}
        .franchise-enquiry-overlay.is-open{display:flex}
        .franchise-enquiry-modal{position:relative;width:min(520px,calc(100vw - 24px));max-height:calc(100vh - var(--header-height,90px) - 44px);max-height:calc(100dvh - var(--header-height,90px) - 44px);display:grid;grid-template-rows:auto minmax(0,1fr);overflow:hidden;border:1px solid rgba(147,197,253,.6);border-radius:18px;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%);box-shadow:0 30px 90px rgba(2,8,23,.36)}
        .franchise-enquiry-modal:before{content:"";position:absolute;inset:0 0 auto;height:5px;background:linear-gradient(90deg,#2563eb,#d31ff2);border-radius:18px 18px 0 0}
        .franchise-enquiry-head{position:relative;display:flex;align-items:center;justify-content:space-between;gap:16px;min-width:0;padding:20px 24px 16px;border-bottom:1px solid rgba(37,99,235,.12);background:linear-gradient(135deg,rgba(239,246,255,.98),rgba(255,255,255,.98))}
        .franchise-enquiry-head>div{min-width:0}
        .franchise-enquiry-kicker{display:inline-flex;align-items:center;gap:8px;margin-bottom:9px;color:#075eea;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.09em}
        .franchise-enquiry-head h2{margin:0;color:#101827;font-size:clamp(22px,2.1vw,25px);line-height:1.18;font-weight:900}
        .franchise-enquiry-close{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border:3px solid #fff;border-radius:50%;background:#0f172a;color:#fff;font-size:18px;cursor:pointer;box-shadow:0 14px 32px rgba(15,23,42,.28);transition:transform .18s ease,background .18s ease}
        .franchise-enquiry-close:hover{transform:translateY(-1px);background:#1d4ed8}
        .franchise-enquiry-close:focus-visible{outline:3px solid rgba(37,99,235,.28);outline-offset:3px}
        .franchise-modal-form{display:grid;gap:12px;min-height:0;margin:0;padding:18px 24px 20px;overflow-y:auto;overscroll-behavior:contain}
        .franchise-modal-form .fm-field{position:relative;display:grid;grid-template-columns:46px minmax(0,1fr);align-items:center;min-height:48px;border:1px solid #c6d9f5;border-radius:10px;background:#fff;overflow:hidden;box-shadow:0 10px 28px rgba(37,99,235,.06)}
        .franchise-modal-form .fm-field i{display:grid;place-items:center;width:46px;height:100%;border-right:1px solid #dbe7fb;background:#f3f8ff;color:#1d4ed8;font-size:16px}
        .franchise-modal-form input,.franchise-modal-form textarea{width:100%;min-width:0;border:0;outline:0;background:transparent;padding:12px 14px;font:inherit;font-size:14px;font-weight:800;color:#172033}
        .franchise-modal-form input::placeholder,.franchise-modal-form textarea::placeholder{color:#7b8790;opacity:1}
        .franchise-modal-form textarea{min-height:82px;resize:vertical;line-height:1.5}
        .franchise-modal-form .fm-field:focus-within{border-color:#2563eb;background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,.10),0 14px 32px rgba(37,99,235,.10)}
        .franchise-modal-form .fm-field:focus-within i{background:#eaf2ff;color:#0f3ea8}
        .franchise-modal-form .fm-actions{display:grid;grid-template-columns:1fr;gap:10px;margin-top:2px}
        .franchise-modal-form button{min-height:46px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#2563eb 0%,#d31ff2 100%);font:inherit;font-weight:900;cursor:pointer;box-shadow:0 16px 34px rgba(124,58,237,.24);transition:transform .18s ease,box-shadow .18s ease}
        .franchise-modal-form button:hover{transform:translateY(-1px);box-shadow:0 20px 40px rgba(124,58,237,.3)}
        .franchise-modal-form button:disabled{opacity:.65;cursor:not-allowed}
        .franchise-modal-form .fm-status{min-height:18px;margin:0;font-size:13px;font-weight:800}
        .franchise-modal-form .fm-status.ok{color:#16a34a}
        .franchise-modal-form .fm-status.fail{color:#dc2626}
        body.franchise-enquiry-open{overflow:hidden}
        body.franchise-enquiry-open .service-modal-overlay{display:none!important}
        @media(min-width:900px) and (max-height:760px){.franchise-enquiry-overlay{padding-top:calc(var(--header-height,90px) + 10px);padding-bottom:14px}.franchise-enquiry-modal{width:min(500px,calc(100vw - 28px));max-height:calc(100vh - var(--header-height,90px) - 24px);max-height:calc(100dvh - var(--header-height,90px) - 24px)}.franchise-enquiry-head{padding:16px 24px 12px}.franchise-enquiry-kicker{margin-bottom:6px}.franchise-modal-form{gap:9px;padding:14px 24px 16px}.franchise-modal-form .fm-field{min-height:44px}.franchise-modal-form input,.franchise-modal-form textarea{padding-top:10px;padding-bottom:10px}.franchise-modal-form textarea{min-height:66px}.franchise-modal-form button{min-height:42px}}
        @media(max-width:760px){.franchise-enquiry-overlay{align-items:flex-start;padding:calc(var(--header-height,78px) + 10px) 12px 14px}.franchise-enquiry-modal{width:100%;max-height:calc(100vh - var(--header-height,78px) - 24px);max-height:calc(100dvh - var(--header-height,78px) - 24px);border-radius:16px}.franchise-enquiry-head{padding:18px 16px 14px}.franchise-modal-form{gap:11px;padding:16px}.franchise-modal-form .fm-field{grid-template-columns:42px minmax(0,1fr);min-height:46px}.franchise-modal-form .fm-field i{width:42px}.franchise-modal-form input,.franchise-modal-form textarea{font-size:13px;padding:12px}.franchise-modal-form textarea{min-height:76px}.franchise-modal-form button{min-height:46px}}
        @media(max-width:420px){.franchise-enquiry-overlay{padding-left:10px;padding-right:10px;padding-bottom:10px}.franchise-enquiry-head{gap:10px;padding:16px 14px 12px}.franchise-enquiry-kicker{font-size:10px;margin-bottom:6px}.franchise-enquiry-head h2{font-size:20px}.franchise-enquiry-close{flex-basis:38px;width:38px;height:38px}.franchise-modal-form{gap:10px;padding:14px}.franchise-modal-form .fm-field{grid-template-columns:40px minmax(0,1fr);min-height:44px}.franchise-modal-form .fm-field i{width:40px}.franchise-modal-form textarea{min-height:70px}}
        @media(max-height:620px){.franchise-enquiry-overlay{align-items:flex-start;padding-top:calc(var(--header-height,72px) + 8px);padding-bottom:8px}.franchise-enquiry-modal{max-height:calc(100vh - var(--header-height,72px) - 16px);max-height:calc(100dvh - var(--header-height,72px) - 16px)}.franchise-enquiry-head{padding-top:12px;padding-bottom:10px}.franchise-enquiry-kicker{margin-bottom:4px}.franchise-modal-form{gap:8px;padding-top:12px;padding-bottom:12px}.franchise-modal-form .fm-field{min-height:42px}.franchise-modal-form textarea{min-height:58px}.franchise-modal-form button{min-height:40px}}
    </style>
</head>
<body class="static-site franchise-page">
<div class="site-shell">
    <header class="site-header"><div class="site-container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png?v=20260722-logo2" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a><nav class="site-nav">
        <a href="index.php">Home</a><a href="about.php">About</a><div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div><a href="gallery.php">Gallery</a><a href="contact.php">Contact</a><div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
    </nav><button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button></div></header>
    <main class="page-main">
        <section class="page-hero has-page-hero-image"><img class="page-hero-bg" src="assets/images/franchise enquiry.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high"><span class="page-hero-overlay" aria-hidden="true"></span><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-handshake"></i> Franchise Enquiry</span><h1>Partner with Talentteno Institute</h1><p>Start a practical IT training centre conversation with our team and understand the course, brand and counselling model.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($items as $item): ?><?php $image = tt_item_image($item, 'franchise'); ?>
            <article class="detail-tile rich-detail-card reveal"><div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($item['title']) ?>" loading="lazy" decoding="async"></div><div class="rich-detail-body"><i class="fa-solid <?= tt_h($item['icon']) ?>"></i><h3><?= tt_h($item['title']) ?></h3><p class="rich-detail-short"><?= tt_h($item['short_desc']) ?></p><p class="rich-detail-more"><?= tt_h($item['description']) ?></p><button type="button" class="rich-detail-link" data-smd-trigger data-smd-title="<?= tt_h($item['title']) ?>" data-smd-category="Franchise" data-smd-description="<?= tt_h($item['description']) ?>" data-smd-image="<?= tt_h($image) ?>" data-smd-features="<?= tt_h($item['short_desc'] . "\n" . $item['description']) ?>" data-smd-enquire="contact.php?topic=franchise">Enquire Now <i class="fa-solid fa-arrow-right"></i></button></div></article>
            <?php endforeach; ?>
        </div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <div class="franchise-enquiry-overlay" id="franchiseEnquiryModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="franchiseEnquiryTitle">
        <div class="franchise-enquiry-modal">
            <div class="franchise-enquiry-head">
                <div>
                    <span class="franchise-enquiry-kicker"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Franchise Enquiry</span>
                    <h2 id="franchiseEnquiryTitle">Enquire Now</h2>
                </div>
                <button class="franchise-enquiry-close" type="button" aria-label="Close enquiry form" data-franchise-enquiry-close><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <form class="franchise-modal-form" method="POST" data-franchise-enquiry-form>
                <input type="hidden" name="form_source" value="franchise_modal">
                <input type="hidden" name="item_title" value="Franchise Enquiry">
                <label class="fm-field"><i class="fa-solid fa-user" aria-hidden="true"></i><input type="text" name="name" placeholder="Full Name" minlength="2" maxlength="80" autocomplete="name" required></label>
                <label class="fm-field"><i class="fa-solid fa-phone" aria-hidden="true"></i><input type="tel" name="phone" placeholder="Phone Number" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" autocomplete="tel" required></label>
                <label class="fm-field"><i class="fa-solid fa-envelope" aria-hidden="true"></i><input type="email" name="email" placeholder="Email Address" maxlength="190" autocomplete="email"></label>
                <label class="fm-field"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><input type="text" name="city" placeholder="City / Location" maxlength="120" required></label>
                <label class="fm-field"><i class="fa-solid fa-building" aria-hidden="true"></i><input type="text" name="business_background" placeholder="Business Background" maxlength="160"></label>
                <label class="fm-field"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i><textarea name="interest" placeholder="Tell us about your interest" maxlength="1200"></textarea></label>
                <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                <div class="fm-actions"><button type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Enquiry</button><p class="fm-status" role="status"></p></div>
            </form>
        </div>
    </div>
</div><script src="assets/js/site-pages.min.js?v=20260721-navbarfix1" defer></script>
<script>
(function(){
    var overlay = document.getElementById('franchiseEnquiryModal');
    var form = overlay ? overlay.querySelector('[data-franchise-enquiry-form]') : null;
    var titleField = form ? form.querySelector('input[name="item_title"]') : null;
    var titleText = document.getElementById('franchiseEnquiryTitle');
    var lastTrigger = null;

    function currentModalTitle(){
        var title = document.getElementById('smdTitle');
        return title && title.textContent.trim() ? title.textContent.trim() : 'Franchise Enquiry';
    }

    function openForm(itemTitle){
        if(!overlay || !form) return;
        if(titleField) titleField.value = itemTitle || 'Franchise Enquiry';
        if(titleText) titleText.textContent = itemTitle ? itemTitle + ' Enquiry' : 'Enquire Now';
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('franchise-enquiry-open');
        setTimeout(function(){
            form.querySelector('input[name="name"]')?.focus();
        }, 60);
    }

    function closeForm(){
        if(!overlay || !form) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('franchise-enquiry-open');
        var status = form.querySelector('.fm-status');
        var button = form.querySelector('button[type="submit"]');
        if(status){
            status.className = 'fm-status';
            status.textContent = '';
        }
        if(button){
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Enquiry';
        }
        if(lastTrigger) lastTrigger.focus();
    }

    async function submitForm(event){
        event.preventDefault();
        var form = event.currentTarget;
        var button = form.querySelector('button[type="submit"]');
        var status = form.querySelector('.fm-status');
        status.className = 'fm-status';
        status.textContent = '';
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Submitting...';
        try {
            var response = await fetch(window.location.href.split('#')[0], {
                method: 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            var data = await response.json().catch(function(){ return {ok:false,message:'Invalid server response.'}; });
            status.classList.add(response.ok && data.ok ? 'ok' : 'fail');
            status.textContent = data.message || (response.ok ? 'Enquiry submitted successfully.' : 'Unable to submit enquiry.');
            if(response.ok && data.ok){
                form.reset();
                button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Submitted';
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Enquiry';
            }
        } catch(error) {
            status.classList.add('fail');
            status.textContent = 'Network error. Please try again.';
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Enquiry';
        }
    }

    if(form) form.addEventListener('submit', submitForm);

    document.addEventListener('click', function(event){
        var cta = event.target.closest('#smdCta');
        if(cta && document.body.classList.contains('franchise-page')){
            event.preventDefault();
            lastTrigger = cta;
            var itemTitle = currentModalTitle();
            document.getElementById('smdCloseBtn')?.click();
            setTimeout(function(){ openForm(itemTitle); }, 80);
            return;
        }
        if(event.target.closest('[data-franchise-enquiry-close]') || event.target === overlay){
            closeForm();
        }
    });

    document.addEventListener('keydown', function(event){
        if(event.key !== 'Escape' || !overlay || !overlay.classList.contains('is-open')) return;
        event.preventDefault();
        closeForm();
    });
})();
</script>
</body></html>
