<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$offers = tt_offers();
$featuredOffer = $offers[0] ?? [];
$whatsappUrl = tt_whatsapp_url($settings);
$phone1Href = tt_phone_href($settings['phone1'] ?? '');
$featuredImage = $featuredOffer ? tt_offer_hero_image($featuredOffer) : 'assets/images/home.webp';
$featuredAlt = trim((string)($featuredOffer['hero_alt'] ?? '')) ?: trim((string)($featuredOffer['poster_alt'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Special Course Offers in Madurai | Talentteno Institute',
        'description' => 'Explore current Talentteno Institute course offers, combo fees, student discounts, internship support, certification and placement guidance.',
        'canonical' => tt_abs_url('offers.php'),
        'image' => $featuredImage,
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Offers', 'url' => 'offers.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260727-unifiednav1">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap">
    </noscript>
    <style>
        .offers-page .page-main{background:#f7f9ff}.offers-hero{position:relative;min-height:calc(82vh - var(--header-height));display:grid;align-items:end;overflow:hidden;background:#07142d;color:#fff}.offers-hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;filter:saturate(1.05)}.offers-hero:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,12,31,.86),rgba(4,12,31,.5) 45%,rgba(4,12,31,.12)),linear-gradient(0deg,rgba(4,12,31,.92),rgba(4,12,31,0) 45%)}.offers-hero .site-container{position:relative;z-index:2;padding:clamp(48px,8vw,96px) clamp(18px,4vw,48px);display:grid;gap:22px}.offers-hero h1{width:min(760px,100%);margin:0;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:clamp(34px,5vw,68px);line-height:1.02;font-weight:900}.offers-hero p{width:min(650px,100%);margin:0;color:rgba(255,255,255,.86);font-size:clamp(15px,1.35vw,19px);line-height:1.7;font-weight:700}.offers-kicker{width:max-content;display:inline-flex;align-items:center;gap:9px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.offers-actions{display:flex;flex-wrap:wrap;gap:12px}.offers-actions a,.offer-card-action,.offers-cta a{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:46px;padding:0 18px;border-radius:10px;text-decoration:none;font-weight:900}.offers-primary,.offer-card-action{color:#fff;background:linear-gradient(135deg,#2563eb,#c026d3);box-shadow:0 18px 36px rgba(79,70,229,.25)}.offers-secondary{color:#fff;border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.09)}.offers-strip{position:relative;z-index:3;margin-top:-42px}.offers-strip-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:18px;border:1px solid rgba(191,219,254,.74);border-radius:14px;background:rgba(255,255,255,.96);box-shadow:0 22px 60px rgba(15,23,42,.14)}.offers-strip-item{display:grid;grid-template-columns:42px minmax(0,1fr);gap:12px;align-items:center;padding:12px;border-radius:10px;background:#f8fbff}.offers-strip-item i{width:42px;height:42px;display:grid;place-items:center;border-radius:10px;color:#fff;background:#0ea5e9}.offers-strip-item strong{display:block;color:#0f172a;font-size:14px}.offers-strip-item span{display:block;color:#64748b;font-size:12px;font-weight:800}.offers-section{padding:72px 0}.offers-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:24px;align-items:end;margin-bottom:28px}.offers-head h2{margin:0;color:#0f172a;font-size:clamp(26px,3vw,42px);line-height:1.08}.offers-head p{max-width:620px;margin:10px 0 0;color:#64748b;font-weight:700;line-height:1.7}.offers-filter{display:flex;gap:8px;flex-wrap:wrap}.offers-filter span{padding:8px 12px;border-radius:999px;background:#eaf2ff;color:#0845b2;font-size:12px;font-weight:900}.offers-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}.offer-card{position:relative;display:grid;grid-template-rows:auto 1fr;overflow:hidden;border:1px solid rgba(37,99,235,.12);border-radius:12px;background:#fff;box-shadow:0 16px 44px rgba(15,23,42,.08);transform:translateZ(0);transition:transform .28s ease,box-shadow .28s ease,border-color .28s ease}.offer-card:hover{transform:translateY(-8px);border-color:rgba(37,99,235,.3);box-shadow:0 28px 70px rgba(15,23,42,.15)}.offer-media{position:relative;aspect-ratio:16/10;overflow:hidden;background:#dbeafe}.offer-media img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .8s cubic-bezier(.18,.72,.22,1)}.offer-card:hover .offer-media img{transform:scale(1.055)}.offer-badge{position:absolute;left:14px;top:14px;display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;color:#fff;background:rgba(15,23,42,.78);backdrop-filter:blur(10px);font-size:12px;font-weight:900}.offer-discount{position:absolute;right:14px;bottom:14px;display:grid;place-items:center;min-width:70px;height:70px;border-radius:50%;color:#fff;background:linear-gradient(135deg,#f97316,#db2777);box-shadow:0 18px 36px rgba(219,39,119,.28);font-size:18px;font-weight:900}.offer-body{display:grid;gap:14px;padding:20px}.offer-meta{display:flex;flex-wrap:wrap;gap:8px}.offer-meta span{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;background:#f1f5f9;color:#334155;font-size:12px;font-weight:900}.offer-card h3{margin:0;color:#0f172a;font-size:21px;line-height:1.2}.offer-card p{margin:0;color:#64748b;line-height:1.65;font-weight:700}.offer-price{display:flex;align-items:end;gap:10px}.offer-price strong{color:#0f172a;font-size:26px;line-height:1}.offer-price del{color:#94a3b8;font-size:14px;font-weight:800}.offer-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}.offer-list li{display:grid;grid-template-columns:16px minmax(0,1fr);gap:8px;color:#334155;font-size:13px;font-weight:800;line-height:1.45}.offer-list li i{color:#16a34a;margin-top:2px}.offer-card-action{justify-self:start;margin-top:4px}.offers-related{padding:0 0 72px}.related-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:22px;align-items:stretch}.related-panel{padding:28px;border-radius:12px;background:#fff;border:1px solid rgba(37,99,235,.12);box-shadow:0 16px 44px rgba(15,23,42,.07)}.related-panel h2{margin:0 0 12px;color:#0f172a;font-size:28px}.related-panel p{margin:0 0 18px;color:#64748b;font-weight:700;line-height:1.7}.related-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.related-checks span{display:flex;align-items:center;gap:9px;padding:12px;border-radius:10px;background:#f8fbff;color:#1e293b;font-weight:900}.related-checks i{color:#2563eb}.terms-list{display:grid;gap:11px;margin:0;padding:0;list-style:none}.terms-list li{display:grid;grid-template-columns:18px minmax(0,1fr);gap:9px;color:#475569;font-size:14px;font-weight:700;line-height:1.55}.terms-list i{color:#f97316;margin-top:3px}.offers-cta{padding:64px 0;background:#07142d;color:#fff;text-align:center}.offers-cta h2{margin:0 0 12px;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:clamp(26px,3vw,40px)}.offers-cta p{width:min(620px,100%);margin:0 auto 24px;color:rgba(255,255,255,.8);font-weight:700;line-height:1.7}.offers-cta div{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}.offers-cta a{color:#fff;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2)}.offers-cta a:first-child{background:#fff;color:#0f172a}@media(max-width:980px){.offers-hero{min-height:68vh}.offers-strip-grid{grid-template-columns:repeat(2,1fr)}.offers-head{grid-template-columns:1fr}.offers-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.related-grid{grid-template-columns:1fr}}@media(max-width:640px){.offers-hero .site-container{padding:40px 16px 72px}.offers-strip{margin-top:-32px}.offers-strip-grid,.offers-grid,.related-checks{grid-template-columns:1fr}.offer-card{border-radius:10px}.offer-body{padding:18px}.offers-section{padding:52px 0}.offers-related{padding-bottom:52px}}
    </style>
    <style>
        .offers-page .offers-grid{align-items:stretch!important}
        .offers-page .offer-card{height:100%!important;min-height:680px!important;grid-template-rows:220px minmax(0,1fr)!important}
        .offers-page .offer-media{height:220px!important;aspect-ratio:auto!important}
        .offers-page .offer-body{height:100%!important;display:grid!important;grid-template-rows:auto auto auto auto minmax(116px,1fr) auto!important;align-content:start!important}
        .offers-page .offer-card h3{min-height:52px!important;display:flex!important;align-items:flex-start!important}
        .offers-page .offer-card p{display:-webkit-box!important;-webkit-line-clamp:3!important;-webkit-box-orient:vertical!important;overflow:hidden!important}
        .offers-page .offer-price{min-height:32px!important}
        .offers-page .offer-list{align-self:start!important}
        .offers-page .offer-card-action{align-self:end!important;justify-self:start!important}
        @media(max-width:980px){.offers-page .offer-card{min-height:650px!important;grid-template-rows:210px minmax(0,1fr)!important}.offers-page .offer-media{height:210px!important}}
        @media(max-width:640px){.offers-page .offer-card{min-height:0!important}.offers-page .offer-body{grid-template-rows:auto!important}.offers-page .offer-card h3{min-height:0!important}}
    </style>
</head>
<body class="static-site offers-page">
<div class="site-shell">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="offers-hero">
            <img src="<?= tt_h($featuredImage) ?>" alt="<?= tt_h($featuredAlt) ?>" fetchpriority="high" decoding="async">
            <div class="site-container reveal">
                <span class="offers-kicker"><i class="fa-solid fa-tags"></i> Current Course Offers</span>
                <h1>Best training offers with practical learning support.</h1>
                <p>Choose course offers with live projects, certificate support, internship guidance and placement preparation from Talentteno Institute.</p>
                <div class="offers-actions">
                    <a class="offers-primary" href="#offer-cards"><i class="fa-solid fa-arrow-down"></i> View Offers</a>
                    <a class="offers-secondary" href="<?= tt_h($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp</a>
                </div>
            </div>
        </section>

        <section class="offers-strip">
            <div class="site-container">
                <div class="offers-strip-grid reveal">
                    <div class="offers-strip-item"><i class="fa-solid fa-laptop-code"></i><div><strong>Live Projects</strong><span>Practice-led learning</span></div></div>
                    <div class="offers-strip-item"><i class="fa-solid fa-certificate"></i><div><strong>Certificate</strong><span>Course completion proof</span></div></div>
                    <div class="offers-strip-item"><i class="fa-solid fa-user-tie"></i><div><strong>Career Support</strong><span>Resume and interview help</span></div></div>
                    <div class="offers-strip-item"><i class="fa-solid fa-clock"></i><div><strong>Flexible Batches</strong><span>Offline, online or hybrid</span></div></div>
                </div>
            </div>
        </section>

        <section class="offers-section" id="offer-cards">
            <div class="site-container">
                <div class="offers-head reveal">
                    <div>
                        <h2>Active Offers</h2>
                        <p>These cards can be updated from the admin panel with poster images, fees, dates, seats, highlights and terms.</p>
                    </div>
                    <div class="offers-filter" aria-label="Offer benefits">
                        <span>Internship</span><span>Placement</span><span>EMI</span><span>Certificate</span>
                    </div>
                </div>
                <div class="offers-grid">
                    <?php foreach ($offers as $index => $offer): ?>
                    <?php
                        $image = tt_offer_image($offer);
                        $title = trim((string)($offer['title'] ?? 'Course Offer'));
                        $courseName = trim((string)($offer['course_name'] ?? ''));
                        $category = trim((string)($offer['category'] ?? ''));
                        $badge = trim((string)($offer['badge_text'] ?? 'Special Offer'));
                        $short = trim((string)($offer['short_description'] ?? ''));
                        $originalFee = tt_offer_money($offer['original_fee'] ?? 0, (string)($offer['currency'] ?? 'INR'));
                        $offerFee = tt_offer_money($offer['offer_fee'] ?? 0, (string)($offer['currency'] ?? 'INR'));
                        $discount = (float)($offer['discount_percentage'] ?? 0);
                        $highlights = tt_offer_lines($offer['highlights'] ?? '', 4);
                        if (!$highlights) {
                            $highlights = ['Practical training', 'Mentor support', 'Career guidance', 'Certificate support'];
                        }
                        $ctaLabel = trim((string)($offer['cta_label'] ?? 'Claim Offer')) ?: 'Claim Offer';
                        $ctaUrl = trim((string)($offer['cta_url'] ?? 'contact.php')) ?: 'contact.php';
                    ?>
                    <article class="offer-card reveal">
                        <div class="offer-media">
                            <img src="<?= tt_h($image) ?>" alt="<?= tt_h(($offer['poster_alt'] ?? '') ?: $title) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" decoding="async" width="480" height="300">
                            <span class="offer-badge"><i class="fa-solid fa-bolt"></i> <?= tt_h($badge) ?></span>
                            <?php if ($discount > 0): ?><span class="offer-discount"><?= (int)round($discount) ?>%<small> OFF</small></span><?php endif; ?>
                        </div>
                        <div class="offer-body">
                            <div class="offer-meta">
                                <?php if ($category !== ''): ?><span><i class="fa-solid fa-layer-group"></i> <?= tt_h($category) ?></span><?php endif; ?>
                                <?php if (($offer['training_mode'] ?? '') !== ''): ?><span><i class="fa-solid fa-chalkboard-user"></i> <?= tt_h($offer['training_mode']) ?></span><?php endif; ?>
                                <?php if (($offer['course_duration'] ?? '') !== ''): ?><span><i class="fa-solid fa-clock"></i> <?= tt_h($offer['course_duration']) ?></span><?php endif; ?>
                            </div>
                            <h3><?= tt_h($title) ?></h3>
                            <?php if ($courseName !== ''): ?><p><strong><?= tt_h($courseName) ?></strong></p><?php endif; ?>
                            <p><?= tt_h($short !== '' ? $short : ($offer['full_description'] ?? 'Contact Talentteno Institute for the latest course offer details.')) ?></p>
                            <?php if ($offerFee !== '' || $originalFee !== ''): ?>
                            <div class="offer-price">
                                <?php if ($offerFee !== ''): ?><strong><?= tt_h($offerFee) ?></strong><?php endif; ?>
                                <?php if ($originalFee !== '' && $originalFee !== $offerFee): ?><del><?= tt_h($originalFee) ?></del><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <ul class="offer-list">
                                <?php foreach ($highlights as $line): ?><li><i class="fa-solid fa-check"></i><span><?= tt_h($line) ?></span></li><?php endforeach; ?>
                            </ul>
                            <a class="offer-card-action" href="<?= tt_h($ctaUrl) ?>"><?= tt_h($ctaLabel) ?> <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="offers-related">
            <div class="site-container related-grid">
                <div class="related-panel reveal">
                    <h2>What You Get With Offers</h2>
                    <p>Offer admission still includes the learning support students expect from Talentteno: trainer guidance, practice work, and career preparation.</p>
                    <div class="related-checks">
                        <span><i class="fa-solid fa-code"></i> Hands-on tasks</span>
                        <span><i class="fa-solid fa-diagram-project"></i> Portfolio projects</span>
                        <span><i class="fa-solid fa-file-lines"></i> Resume guidance</span>
                        <span><i class="fa-solid fa-comments"></i> Interview prep</span>
                    </div>
                </div>
                <div class="related-panel reveal reveal-right">
                    <h2>Terms</h2>
                    <ul class="terms-list">
                        <li><i class="fa-solid fa-circle-info"></i><span>Offers apply only to selected batches and courses.</span></li>
                        <li><i class="fa-solid fa-circle-info"></i><span>Seat confirmation depends on successful registration.</span></li>
                        <li><i class="fa-solid fa-circle-info"></i><span>Discounts cannot be combined unless mentioned by the institute.</span></li>
                        <li><i class="fa-solid fa-circle-info"></i><span>Contact the office for latest fee and validity before payment.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="offers-cta">
            <div class="site-container reveal">
                <h2>Need the current offer fee?</h2>
                <p>Call or WhatsApp Talentteno Institute to confirm today’s available seats, batch timing and course offer validity.</p>
                <div>
                    <a href="<?= tt_h($phone1Href) ?>"><i class="fa-solid fa-phone"></i> Call Now</a>
                    <a href="<?= tt_h($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    <a href="contact.php?topic=Course+Offer"><i class="fa-solid fa-paper-plane"></i> Enquire</a>
                </div>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260727-offersadmin1" defer></script>
</body>
</html>
