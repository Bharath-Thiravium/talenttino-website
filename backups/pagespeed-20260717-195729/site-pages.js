const header = document.querySelector('.site-header');
const nav = document.querySelector('.site-nav');
const menuButton = document.querySelector('.menu-button');
const dropdownItems = document.querySelectorAll('.nav-item.has-menu');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
let headerTicking = false;

if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

if (!window.location.hash) {
    window.addEventListener('load', () => {
        requestAnimationFrame(() => window.scrollTo({ top: 0, left: 0, behavior: 'auto' }));
    }, { once: true });
}

// Soft ambient lighting follows the pointer on desktop without affecting layout.
if (canHover && !reduceMotion) {
    let pointerFrame = 0;
    window.addEventListener('pointermove', event => {
        if (pointerFrame) return;
        pointerFrame = requestAnimationFrame(() => {
            document.body.style.setProperty('--pointer-x', `${event.clientX}px`);
            document.body.style.setProperty('--pointer-y', `${event.clientY}px`);
            pointerFrame = 0;
        });
    }, { passive: true });
}

// Add shared navigation semantics and active state without duplicating page logic.
if (nav) {
    nav.setAttribute('aria-label', 'Primary navigation');
    if (!nav.querySelector('.nav-enroll-cta')) {
        const enrollCta = document.createElement('a');
        enrollCta.className = 'nav-enroll-cta';
        enrollCta.href = 'contact.php';
        enrollCta.textContent = 'Enroll Now';
        nav.appendChild(enrollCta);
    }
    const courseMenu = nav.querySelector('.nav-item.has-menu:not(.more-menu) .nav-menu');
    if (courseMenu && !courseMenu.querySelector('a[data-course-menu-all]')) {
        const allCoursesLink = document.createElement('a');
        allCoursesLink.href = 'course.php';
        allCoursesLink.textContent = 'All Courses';
        allCoursesLink.dataset.courseMenuAll = 'true';
        courseMenu.prepend(allCoursesLink);
    }
    if (courseMenu && !courseMenu.querySelector('a[href="designingcourse.php"]')) {
        const designCourseLink = document.createElement('a');
        designCourseLink.href = 'designingcourse.php';
        designCourseLink.textContent = 'Designing Course';
        courseMenu.appendChild(designCourseLink);
    }
    if (courseMenu && !courseMenu.querySelector('a[data-course-menu-cyber]')) {
        const cyberCourseLink = document.createElement('a');
        cyberCourseLink.href = 'cybersecuritycourse.php';
        cyberCourseLink.textContent = 'Cyber Security';
        cyberCourseLink.dataset.courseMenuCyber = 'true';
        courseMenu.appendChild(cyberCourseLink);
    }
    const moreMenu = nav.querySelector('.nav-item.has-menu.more-menu');
    const moreTrigger = moreMenu?.querySelector(':scope > a');
    const morePanel = moreMenu?.querySelector(':scope > .nav-menu');
    if (moreTrigger) {
        moreTrigger.innerHTML = 'Others <i class="fa-solid fa-chevron-down"></i>';
    }
    if (morePanel) {
        const otherLinks = [
            ['review.php', 'Student Reviews', 'fa-star'],
            ['why-talentteno.php', 'Why Talentteno', 'fa-graduation-cap'],
            ['hiring.php', 'Hiring', 'fa-user-plus'],
            ['franchise.php', 'Franchise Enquiry', 'fa-handshake'],
        ];
        otherLinks.forEach(([href, label, icon]) => {
            if (morePanel.querySelector(`a[href="${href}"]`)) return;
            const link = document.createElement('a');
            link.href = href;
            link.className = 'nav-menu-rich-link';
            link.innerHTML = `<i class="fa-solid ${icon}" aria-hidden="true"></i><span>${label}</span>`;
            morePanel.appendChild(link);
        });
    }
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const morePages = ['services.php', 'career.php', 'blog.php', 'project.php', 'review.php', 'why-talentteno.php', 'hiring.php', 'franchise.php'];
    nav.querySelectorAll('a[href]').forEach(link => {
        if (link.classList.contains('nav-enroll-cta')) return;
        const targetHref = link.getAttribute('href');
        const targetPage = targetHref.split('?')[0].split('#')[0] || currentPage;
        const targetHash = targetHref.includes('#') ? targetHref.slice(targetHref.indexOf('#')) : '';
        const isSameHash = targetHash && targetPage === currentPage && window.location.hash === targetHash;
        const hasSectionHash = Boolean(window.location.hash);
        const isCoursePage = ['course.php', 'course-catalog.php', 'shorttermcourse.php', 'popularcourse.php', 'advancecourse.php', 'designingcourse.php', 'cybersecuritycourse.php', 'download.php'].includes(currentPage);
        const isMoreTrigger = link.closest('.more-menu') && link.getAttribute('href') === '#';
        const isCurrent = (!targetHash && targetPage === currentPage && !hasSectionHash) || isSameHash || (targetPage === 'course.php' && isCoursePage) || (isMoreTrigger && morePages.includes(currentPage));
        link.classList.toggle('active', isCurrent);
        if (isCurrent) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
        if (isCurrent) link.closest('.nav-item')?.classList.add('active');
    });
}

const main = document.querySelector('main');
if (main) {
    if (!main.id) main.id = 'main-content';
    const skipLink = document.createElement('a');
    skipLink.className = 'skip-link';
    skipLink.href = `#${main.id}`;
    skipLink.textContent = 'Skip to main content';
    document.body.prepend(skipLink);
}

function updateHeader() {
    header?.classList.toggle('is-scrolled', window.scrollY > 20);
}

updateHeader();
window.addEventListener('scroll', () => {
    if (headerTicking) return;
    headerTicking = true;
    requestAnimationFrame(() => {
        updateHeader();
        headerTicking = false;
    });
}, { passive: true });

function closeMobileNav() {
    nav?.classList.remove('open');
    dropdownItems.forEach(item => {
        item.classList.remove('open');
        item.querySelector(':scope > a')?.setAttribute('aria-expanded', 'false');
    });
    menuButton?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('nav-open');
    document.documentElement.classList.remove('nav-open');
    if (menuButton) menuButton.innerHTML = '<i class="fa-solid fa-bars"></i>';
}

window.addEventListener('pageshow', closeMobileNav);

let menuPointerHandled = false;
function toggleMobileNav(event) {
    event?.preventDefault();
    event?.stopPropagation();
    const isOpen = nav?.classList.toggle('open') || false;
    menuButton?.setAttribute('aria-expanded', String(isOpen));
    if (menuButton) menuButton.innerHTML = isOpen ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
    document.body.classList.toggle('nav-open', isOpen);
    document.documentElement.classList.toggle('nav-open', isOpen);
}

menuButton?.addEventListener('pointerdown', event => {
    if (event.pointerType === 'mouse') return;
    menuPointerHandled = true;
    toggleMobileNav(event);
}, { capture: true });

menuButton?.addEventListener('click', event => {
    if (menuPointerHandled) {
        menuPointerHandled = false;
        event.preventDefault();
        event.stopPropagation();
        return;
    }
    toggleMobileNav(event);
});

document.addEventListener('click', event => {
    if (event.target.closest('.site-nav') || event.target.closest('.menu-button')) return;
    dropdownItems.forEach(item => item.classList.remove('open'));
    dropdownItems.forEach(item => item.querySelector(':scope > a')?.setAttribute('aria-expanded', 'false'));
    if (nav?.classList.contains('open')) {
        closeMobileNav();
    }
});

dropdownItems.forEach(item => {
    const trigger = item.querySelector(':scope > a');
    trigger?.setAttribute('aria-haspopup', 'true');
    trigger?.setAttribute('aria-expanded', 'false');
    trigger?.addEventListener('click', event => {
        const triggerHref = trigger.getAttribute('href') || '';
        const isMobileNav = window.innerWidth <= 980 || nav?.classList.contains('open');
        const wasOpen = item.classList.contains('open');
        if (isMobileNav && wasOpen && triggerHref !== '#') return;
        if (triggerHref === '#' || isMobileNav) {
            event.preventDefault();
            event.stopPropagation();
        }
        // Desktop uses hover for the submenu; a click follows course.php normally.
        if (!isMobileNav && triggerHref !== '#') return;
        dropdownItems.forEach(other => {
            if (other === item) return;
            other.classList.remove('open');
            other.querySelector(':scope > a')?.setAttribute('aria-expanded', 'false');
        });
        item.classList.toggle('open', !wasOpen);
        trigger.setAttribute('aria-expanded', String(!wasOpen));
    });
});

nav?.querySelectorAll('a[href]').forEach(link => {
    link.addEventListener('click', event => {
        if (window.innerWidth > 980) return;
        const isMenuTrigger = link.closest('.nav-item.has-menu')?.querySelector(':scope > a') === link;
        if (isMenuTrigger) return;
        closeMobileNav();
        const linkUrl = new URL(link.href, window.location.href);
        const currentUrl = new URL(window.location.href);
        if (linkUrl.pathname === currentUrl.pathname && linkUrl.hash === currentUrl.hash) {
            event.preventDefault();
        }
    });
});

window.addEventListener('resize', () => {
    if (window.innerWidth <= 980) return;
    closeMobileNav();
}, { passive: true });

document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    closeMobileNav();
});

const scrollTopButton = document.querySelector('.scroll-top');
function updateScrollTop() {
    scrollTopButton?.classList.toggle('is-visible', window.scrollY > 500);
}
updateScrollTop();
window.addEventListener('scroll', updateScrollTop, { passive: true });
scrollTopButton?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
});

// Home course path tabs update the visual card without leaving the page.
document.querySelectorAll('[data-path-tabs]').forEach(pathTabs => {
    const section = pathTabs.closest('.model-path-section');
    const visual = section?.querySelector('.model-path-visual');
    const image = visual?.querySelector('img');
    const step = visual?.querySelector('div > span');
    const title = visual?.querySelector('h3');
    const desc = visual?.querySelector('p');
    const buttons = [...pathTabs.querySelectorAll('button')];

    const setActivePath = button => {
        if (!button) return;
        buttons.forEach(item => {
            const isActive = item === button;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-pressed', String(isActive));
        });
        if (step && button.dataset.step) step.textContent = button.dataset.step;
        if (title && button.dataset.title) title.textContent = button.dataset.title;
        if (desc && button.dataset.desc) desc.textContent = button.dataset.desc;
        if (image && button.dataset.image && image.getAttribute('src') !== button.dataset.image) {
            image.classList.add('is-changing');
            window.setTimeout(() => {
                image.src = button.dataset.image;
                image.alt = `${button.textContent.trim()} training path`;
                image.classList.remove('is-changing');
            }, reduceMotion ? 0 : 120);
        }
    };

    buttons.forEach(button => {
        button.addEventListener('click', () => setActivePath(button));
        if (canHover) button.addEventListener('mouseenter', () => setActivePath(button));
    });
});

document.querySelectorAll('form:not(.whatsapp-enquiry-form):not([data-download-form]):not([data-ai-form])').forEach(form => {
    form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.dataset.originalText = button.innerHTML;
        button.textContent = 'Submitting…';
    });
});

const revealItems = document.querySelectorAll('.reveal');
if (reduceMotion) {
    revealItems.forEach(item => item.classList.add('is-visible'));
} else if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.14, rootMargin: '0px 0px -45px 0px' });

    revealItems.forEach(item => {
        const siblings = item.parentElement
            ? [...item.parentElement.children].filter(child => child.classList.contains('reveal'))
            : [];
        const siblingIndex = Math.max(0, siblings.indexOf(item));
        item.style.transitionDelay = `${Math.min(siblingIndex * 75, 300)}ms`;
        observer.observe(item);
    });
} else {
    revealItems.forEach(item => item.classList.add('is-visible'));
}

const counterItems = document.querySelectorAll('.stat-card strong, .about-highlights strong, .home-stats strong');

const homeStatsBar = document.querySelector('.home-stats');
if (homeStatsBar) {
    if (reduceMotion || !('IntersectionObserver' in window)) {
        homeStatsBar.classList.add('is-visible');
    } else {
        const statsBarObserver = new IntersectionObserver(entries => {
            if (!entries[0]?.isIntersecting) return;
            homeStatsBar.classList.add('is-visible');
            statsBarObserver.disconnect();
        }, { threshold: 0.45 });
        statsBarObserver.observe(homeStatsBar);
    }
}

function animateCounter(item) {
    const raw = item.textContent.trim();
    const number = parseInt(raw.replace(/[^0-9]/g, ''), 10);
    if (!number || item.dataset.counted === 'true') return;

    item.dataset.counted = 'true';
    const suffix = raw.replace(/[0-9]/g, '');
    const start = performance.now();
    const duration = 1200;

    function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        item.textContent = `${Math.round(number * eased)}${suffix}`;
        if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
}

if (reduceMotion) {
    counterItems.forEach(item => { item.dataset.counted = 'true'; });
} else if (counterItems.length) {
    const counterObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.65 });

    counterItems.forEach(item => counterObserver.observe(item));
}

document.querySelectorAll('.course-card, .catalog-card, .feature-card, .detail-tile, .price-card, .home-course-card, .home-process-card, .testimonial-card').forEach(card => {
    card.classList.add('tilt-ready');
    let frame = 0;
    card.addEventListener('pointermove', event => {
        if (reduceMotion || !canHover || window.innerWidth < 760 || frame) return;
        frame = requestAnimationFrame(() => {
            const rect = card.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width - 0.5) * 4;
            const y = ((event.clientY - rect.top) / rect.height - 0.5) * -4;
            card.style.setProperty('--card-x', `${event.clientX - rect.left}px`);
            card.style.setProperty('--card-y', `${event.clientY - rect.top}px`);
            card.style.transform = `perspective(900px) rotateX(${y}deg) rotateY(${x}deg) translateY(-4px)`;
            frame = 0;
        });
    });
    card.addEventListener('pointerleave', () => {
        if (frame) {
            cancelAnimationFrame(frame);
            frame = 0;
        }
        card.style.transform = '';
    });
});

const courseDetailModal = document.getElementById('courseDetailModal');
const closeCourseDetail = () => {
    if (!courseDetailModal) return;
    courseDetailModal.classList.remove('is-open');
    courseDetailModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
};

function openCourseDetails(button) {
        if (!courseDetailModal) return;
        courseDetailModal.querySelector('#courseDetailTitle').textContent = button.dataset.title || '';
        courseDetailModal.querySelector('.course-detail-category').textContent = button.dataset.category || 'Course';
        courseDetailModal.querySelector('.course-detail-description').textContent = button.dataset.description || 'Contact us for complete course details.';
        const highlights = courseDetailModal.querySelector('.course-detail-highlights');
        const highlightItems = (button.dataset.highlights || '')
            .split(/\r?\n/)
            .map(item => item.trim())
            .filter(Boolean);
        highlights.innerHTML = '';
        highlights.hidden = highlightItems.length === 0;
        highlightItems.forEach(item => {
            const li = document.createElement('li');
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-check';
            li.append(icon, document.createTextNode(` ${item}`));
            highlights.appendChild(li);
        });
        const duration = courseDetailModal.querySelector('.course-detail-duration');
        const fee = courseDetailModal.querySelector('.course-detail-fee');
        const meta = courseDetailModal.querySelector('.course-detail-meta');
        duration.textContent = button.dataset.duration ? `Duration: ${button.dataset.duration}` : '';
        fee.textContent = button.dataset.fee || '';
        if (meta) meta.hidden = !duration.textContent && !fee.textContent;
        const enquire = courseDetailModal.querySelector('.course-detail-enquire');
        if (enquire) enquire.href = button.dataset.enquire || 'contact.php';
        const download = courseDetailModal.querySelector('.course-detail-download');
        if (download) download.hidden = !button.dataset.download;
        if (button.dataset.download) download.href = button.dataset.download;
        const imageWrap = courseDetailModal.querySelector('.course-detail-image');
        const image = imageWrap?.querySelector('img');
        if (imageWrap && image) {
            if (button.dataset.image) {
                image.src = button.dataset.image;
                image.alt = `${button.dataset.title || 'Course'} preview`;
                imageWrap.style.setProperty('--course-detail-image-bg', `url("${button.dataset.image.replace(/"/g, '\\"')}")`);
                imageWrap.hidden = false;
            } else {
                image.removeAttribute('src');
                image.alt = '';
                imageWrap.style.removeProperty('--course-detail-image-bg');
                imageWrap.hidden = true;
            }
        }
        courseDetailModal.classList.add('is-open');
        courseDetailModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        courseDetailModal.querySelector('.course-detail-close').focus();
}

document.querySelectorAll('[data-course-modal]').forEach(button => {
    button.addEventListener('click', event => {
        if (button.matches('.course-card, .catalog-card, .home-course-card') && event.target.closest('a, button, input, select, textarea')) return;
        openCourseDetails(button);
    });
    button.addEventListener('keydown', event => {
        if (!button.matches('.course-card, .catalog-card, .home-course-card')) return;
        if (event.target.closest('a, button, input, select, textarea')) return;
        if (!['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        openCourseDetails(button);
    });
});

document.querySelectorAll('.catalog-card').forEach(card => {
    card.addEventListener('click', event => {
        if (card.matches('[data-course-modal]')) return;
        if (event.target.closest('a, button, input, select, textarea')) return;
        card.querySelector('[data-course-modal]')?.click();
    });
});

document.querySelectorAll('.home-course-card').forEach(card => {
    const openDetails = event => {
        if (card.matches('[data-course-modal]')) return;
        if (event.target.closest('a, button, input, select, textarea')) return;
        card.querySelector('[data-course-modal]')?.click();
    };
    card.addEventListener('click', openDetails);
    card.addEventListener('keydown', event => {
        if (event.target.closest('a, button, input, select, textarea')) return;
        if (!['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        card.querySelector('[data-course-modal]')?.click();
    });
});

document.querySelectorAll('[data-close-course-detail]').forEach(button => button.addEventListener('click', closeCourseDetail));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeCourseDetail();
});

const trainingVideoModal = document.getElementById('trainingVideoModal');
const trainingVideo = trainingVideoModal?.querySelector('video');
const closeTrainingVideo = () => {
    if (!trainingVideoModal) return;
    trainingVideoModal.classList.remove('is-open');
    trainingVideoModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (trainingVideo) {
        trainingVideo.pause();
        trainingVideo.currentTime = 0;
    }
};

document.querySelectorAll('[data-video-open]').forEach(button => {
    button.addEventListener('click', () => {
        if (!trainingVideoModal) return;
        trainingVideoModal.classList.add('is-open');
        trainingVideoModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        trainingVideoModal.querySelector('.training-video-close')?.focus();
        trainingVideo?.play().catch(() => {});
    });
});

document.querySelectorAll('[data-video-close]').forEach(button => button.addEventListener('click', closeTrainingVideo));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeTrainingVideo();
});

// Home page local AI-style assistant. No external API required.
const aiChat = document.querySelector('[data-ai-chat]');
if (aiChat) {
    const aiToggle = aiChat.querySelector('[data-ai-toggle]');
    const aiBack = aiChat.querySelector('[data-ai-back]');
    const aiClose = aiChat.querySelector('[data-ai-close]');
    const aiPanel = aiChat.querySelector('.home-ai-panel');
    const aiMessages = aiChat.querySelector('[data-ai-messages]');
    const aiForm = aiChat.querySelector('[data-ai-form]');
    const aiInput = aiForm?.querySelector('input[name="question"]');

    const companyDetails = {
        name: 'Talentteno Institute',
        address: 'Plot 81, Poriyalar Nagar, Tiruppalai, Madurai, Tamil Nadu - 625014',
        phone1: '+91 82484 15023',
        phone2: '+91 63836 43141',
        email: 'talentteno.socials@gmail.com'
    };

    const replies = [
        {
            keys: ['company', 'institute', 'about', 'talentteno', 'details', 'detail', 'more', 'who are you', 'what is talentteno', 'institute details', 'about institute'],
            text: `About: ${companyDetails.name} is an IT training institute in Madurai offering practical classroom training, free internship guidance, live project practice, certification support and placement preparation.`
        },
        {
            keys: ['course', 'courses', 'class', 'classes', 'training', 'learn', 'program', 'programs', 'syllabus', 'available', 'teach', 'study', 'enna course', 'course list', 'all course', 'enna courses', 'what course', 'which course'],
            text: 'Courses available: Full Stack with AI, Data Science and AI, Cyber Security, Digital Marketing, UI/UX Design, Tally, basic computer, MS Office, programming, short-term courses and advanced professional courses. Tell me your interest, education level or goal and I can suggest a suitable course.'
        },
        {
            keys: ['fee', 'fees', 'cost', 'price', 'amount', 'charges', 'offer', 'discount', 'emi', 'payment', 'pay', 'how much', 'fees evlo', 'fee evlo', 'evlo', 'rate', 'fees details'],
            text: `Fees: Fees change based on course, duration, batch and current offer. For the correct fee, discount or EMI details, call ${companyDetails.phone1} / ${companyDetails.phone2} or submit the free counselling form.`
        },
        {
            keys: ['internship', 'intern', 'project', 'projects', 'live project', 'portfolio', 'practical', 'hands on', 'experience', 'internship iruka', 'project iruka', 'iruka', 'practical class', 'real time project'],
            text: 'Internship & projects: Yes. Talentteno provides free internship guidance and live project practice. Students work on practical tasks to build portfolio-ready confidence.'
        },
        {
            keys: ['placement', 'placements', 'job', 'jobs', 'career', 'interview', 'resume', 'hiring', 'work', 'job support', 'job assistance', 'velai'],
            text: 'Placement: Support includes resume preparation, mock interview practice, job-readiness mentoring and hiring guidance for eligible students.'
        },
        {
            keys: ['demo', 'trial', 'counselling', 'counseling', 'free class', 'free demo', 'sample class', 'visit', 'demo class', 'free demo class'],
            text: 'Demo class: You can book a free demo class or counselling session from the Sign Up form on the home page. Our counsellor will call and guide you.'
        },
        {
            keys: ['location', 'address', 'where', 'madurai', 'tiruppalai', 'poriyalar', 'map', 'near', 'place', 'route', 'enga', 'where is', 'office', 'branch'],
            text: `Address: ${companyDetails.address}.`
        },
        {
            keys: ['phone', 'contact', 'call', 'whatsapp', 'mobile', 'number', 'email', 'mail', 'talk', 'reach'],
            text: `Contact: ${companyDetails.phone1}, ${companyDetails.phone2}. Email: ${companyDetails.email}. You can also use the WhatsApp button for quick enquiry.`
        },
        {
            keys: ['online', 'offline', 'batch', 'batches', 'timing', 'timings', 'time', 'schedule', 'mode', 'morning', 'evening', 'weekend', 'hours', 'open', 'class time', 'duration', 'how many days', 'month', 'months'],
            text: 'Timing & duration: Batch timing, duration and online/offline mode depend on the selected course. Morning, evening or weekend availability can be confirmed by the admission team after you choose a course.'
        },
        {
            keys: ['certificate', 'certification', 'certified'],
            text: 'Certificate: Certification support is available after course completion. Students also get guidance to complete practical tasks and project work.'
        },
        {
            keys: ['admission', 'join', 'enroll', 'enrol', 'apply', 'register', 'joining', 'epdi join', 'how to join', 'join panna'],
            text: 'Admission: To join Talentteno, submit the free counselling form, call the institute, or send a WhatsApp enquiry. The team will guide course selection, fee details and batch timing.'
        },
        {
            keys: ['full stack', 'fullstack', 'web development', 'frontend', 'backend'],
            text: 'Full Stack with AI covers practical web development skills, frontend/backend workflow, projects and career preparation. Ask for fees or demo class to continue.'
        },
        {
            keys: ['data science', 'python', 'analytics', 'ai', 'artificial intelligence'],
            text: 'Data Science and AI training focuses on practical tools, analytics basics, project practice and career guidance for IT roles.'
        },
        {
            keys: ['digital marketing', 'marketing', 'seo', 'social media'],
            text: 'Digital Marketing training covers practical marketing skills, campaign basics, social media/SEO guidance and project-based learning.'
        },
        {
            keys: ['cyber', 'cyber security', 'security'],
            text: 'Cyber Security training includes guided practical labs, security workflow basics and project practice for beginners.'
        },
        {
            keys: ['ui', 'ux', 'design', 'designing'],
            text: 'UI/UX Design training covers design fundamentals, practical tools, portfolio practice and career guidance.'
        },
        {
            keys: ['tally', 'accounts', 'accounting', 'gst'],
            text: 'Tally and accounting training helps students learn practical business entries, GST basics and office-ready accounting workflow.'
        },
        {
            keys: ['short term', 'short-term', 'basic computer', 'computer course', 'ms office', 'excel'],
            text: 'Short-term courses include computer basics, MS Office, programming foundations and other practical skill courses for students and working professionals.'
        }
    ];

    const normalizeAiText = value => String(value || '')
        .toLowerCase()
        .replace(/full\s*stack/g, 'full stack')
        .replace(/cyber\s*security/g, 'cyber security')
        .replace(/ui\s*\/\s*ux/g, 'ui ux')
        .replace(/fees?/g, 'fees')
        .replace(/evvalavu/g, 'evlo')
        .replace(/[^\w\s/+.-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const hasAny = (text, keys) => keys.some(key => text.includes(key));

    const getAiReply = question => {
        const clean = normalizeAiText(question);
        if (['hi', 'hello', 'hey', 'hai', 'vanakkam'].includes(clean)) {
            return `Hi! Welcome to ${companyDetails.name}. I can help with courses, fees, internship, placement, demo class, address, contact number, timings and admission.`;
        }
        if (['thanks', 'thank you', 'ok', 'okay'].includes(clean)) {
            return 'You are welcome. For admission help, use the free counselling form or WhatsApp button.';
        }

        const asksFee = hasAny(clean, ['fee', 'fees', 'cost', 'price', 'amount', 'charges', 'how much', 'emi', 'payment', 'evlo', 'rate', 'discount', 'offer']);
        const asksCourse = hasAny(clean, ['course', 'courses', 'class', 'training', 'syllabus', 'learn', 'teach', 'available']);
        const asksContact = hasAny(clean, ['contact', 'phone', 'call', 'whatsapp', 'number', 'mobile', 'email', 'mail']);
        const asksLocation = hasAny(clean, ['address', 'location', 'where', 'map', 'route', 'near']);
        const asksInternship = hasAny(clean, ['internship', 'intern', 'project', 'portfolio', 'practical']);
        const asksPlacement = hasAny(clean, ['placement', 'job', 'career', 'interview', 'resume', 'hiring']);
        const asksTiming = hasAny(clean, ['timing', 'time', 'batch', 'duration', 'morning', 'evening', 'weekend', 'online', 'offline']);
        const asksAdmission = hasAny(clean, ['admission', 'join', 'enroll', 'enrol', 'apply', 'register']);
        const courseNames = [
            ['full stack', 'Full Stack with AI'],
            ['fullstack', 'Full Stack with AI'],
            ['web development', 'Full Stack with AI'],
            ['data science', 'Data Science and AI'],
            ['python', 'Data Science and AI'],
            ['ai', 'Data Science and AI'],
            ['cyber', 'Cyber Security'],
            ['digital marketing', 'Digital Marketing'],
            ['ui ux', 'UI/UX Design'],
            ['design', 'UI/UX Design'],
            ['tally', 'Tally'],
            ['basic computer', 'Basic Computer'],
            ['ms office', 'MS Office'],
            ['excel', 'MS Office and Excel'],
        ];
        const mentionedCourse = courseNames.find(([key]) => clean.includes(key))?.[1];

        if (mentionedCourse && asksFee) {
            return `${mentionedCourse} fee depends on batch, duration and current offer. For the correct fee, EMI and discount, call ${companyDetails.phone1} / ${companyDetails.phone2} or send a WhatsApp enquiry.`;
        }
        if (mentionedCourse && asksInternship) {
            return `Yes, ${mentionedCourse} students get guided practical tasks, live project or internship support, and portfolio preparation.`;
        }
        if (mentionedCourse && asksPlacement) {
            return `${mentionedCourse} includes career support such as resume guidance, mock interview preparation and placement assistance for eligible students.`;
        }
        if (mentionedCourse && asksTiming) {
            return `${mentionedCourse} batch timing and duration depend on the current schedule. Call ${companyDetails.phone1} or submit the free counselling form to confirm the next available batch.`;
        }
        if (mentionedCourse && asksCourse) {
            return `${mentionedCourse} is available at ${companyDetails.name}. It includes practical training, mentor guidance, project work and career support.`;
        }
        if (asksContact && asksLocation) {
            return `${companyDetails.name} is at ${companyDetails.address}. Contact: ${companyDetails.phone1}, ${companyDetails.phone2}. Email: ${companyDetails.email}.`;
        }
        if (asksAdmission && asksFee) {
            return `Admission and fees: Submit the free counselling form or call ${companyDetails.phone1}. Our team will explain the right course, current fee, offer, EMI option and batch timing.`;
        }

        const scoredMatches = replies
            .map(item => ({
                item,
                score: item.keys.reduce((total, key) => total + (clean.includes(key) ? key.split(' ').length : 0), 0)
            }))
            .filter(match => match.score > 0)
            .sort((a, b) => b.score - a.score);

        const matchedAnswers = [];
        scoredMatches.forEach(match => {
            if (matchedAnswers.includes(match.item.text)) return;
            matchedAnswers.push(match.item.text);
        });

        if (matchedAnswers.length > 1) {
            const answerLimit = clean.length > 90 || /\b(and|also|with|plus|,)\b/.test(clean) ? 5 : 3;
            return matchedAnswers
                .slice(0, answerLimit)
                .map((answer, index) => `${index + 1}. ${answer}`)
                .join('\n\n');
        }

        return matchedAnswers[0]
            || `Please ask about course names, fees, internship, placement, demo class, admission, certificate, timing, address or contact details. For direct help call ${companyDetails.phone1} or ${companyDetails.phone2}.`;
    };

    const appendAiMessage = (message, type = 'bot') => {
        if (!aiMessages) return;
        const bubble = document.createElement('div');
        bubble.className = `ai-message ${type}`;
        bubble.textContent = message;
        aiMessages.appendChild(bubble);
        aiMessages.scrollTop = aiMessages.scrollHeight;
    };

    const openAiChat = () => {
        aiPanel?.classList.add('is-open');
        aiPanel?.setAttribute('aria-hidden', 'false');
        aiToggle?.setAttribute('aria-expanded', 'true');
        window.setTimeout(() => aiInput?.focus(), 80);
    };

    const closeAiChat = () => {
        aiPanel?.classList.remove('is-open');
        aiPanel?.setAttribute('aria-hidden', 'true');
        aiToggle?.setAttribute('aria-expanded', 'false');
    };

    const submitAiQuestion = question => {
        const clean = String(question || '').trim();
        if (!clean) return;
        appendAiMessage(clean, 'user');
        if (aiInput) aiInput.value = '';
        const typing = document.createElement('div');
        typing.className = 'ai-message bot typing';
        typing.textContent = 'Typing...';
        aiMessages?.appendChild(typing);
        aiMessages.scrollTop = aiMessages.scrollHeight;
        window.setTimeout(() => {
            typing.remove();
            appendAiMessage(getAiReply(clean), 'bot');
        }, 520);
    };

    aiToggle?.addEventListener('click', () => {
        const isOpen = aiPanel?.classList.contains('is-open');
        if (isOpen) closeAiChat();
        else openAiChat();
    });
    aiBack?.addEventListener('click', closeAiChat);
    aiClose?.addEventListener('click', closeAiChat);
    document.addEventListener('click', event => {
        if (!aiPanel?.classList.contains('is-open')) return;
        if (aiChat.contains(event.target)) return;
        closeAiChat();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeAiChat();
    });
    aiChat.querySelectorAll('[data-ai-question]').forEach(button => {
        button.addEventListener('click', () => submitAiQuestion(button.dataset.aiQuestion));
    });
    aiForm?.addEventListener('submit', event => {
        event.preventDefault();
        submitAiQuestion(aiInput?.value);
    });
}


// Hero slider — auto-play, arrows, dots, pause on hover
(function () {
    const slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;

    const slides = [...slider.querySelectorAll('[data-slide]')];
    const dots = [...slider.querySelectorAll('[data-slider-dots] .slider-dot')];
    const prevBtn = slider.querySelector('[data-slider-prev]');
    const nextBtn = slider.querySelector('[data-slider-next]');

    let current = 0;
    let timer = null;
    const INTERVAL = 4000;

    function updateTrack() {
        var track = slider.querySelector('[data-slider-track]');
        if (!track) return;
        track.style.transform = 'translate3d(-' + (current * 100) + '%, 0, 0)';
    }

    function syncAspectRatio(index) {
        const img = slides[index]?.querySelector('img');
        if (!img) return;

        function applyRatio() {
            if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                slider.style.aspectRatio = img.naturalWidth + ' / ' + img.naturalHeight;
            }
        }

        if (img.complete) {
            applyRatio();
        } else {
            img.addEventListener('load', applyRatio, { once: true });
        }
    }

    if (slides.length <= 1) {
        syncAspectRatio(current);
        return;
    }

    function goTo(index) {
        slides[current].classList.remove('is-active');
        slides[current].setAttribute('aria-hidden', 'true');
        if (dots[current]) {
            dots[current].classList.remove('is-active');
            dots[current].setAttribute('aria-pressed', 'false');
        }

        current = (index + slides.length) % slides.length;
        updateTrack();

        slides[current].classList.add('is-active');
        slides[current].setAttribute('aria-hidden', 'false');
        syncAspectRatio(current);
        if (dots[current]) {
            dots[current].classList.add('is-active');
            dots[current].setAttribute('aria-pressed', 'true');
        }
    }

    function startAuto() {
        stopAuto();
        timer = setInterval(function () { goTo(current + 1); }, INTERVAL);
    }

    function stopAuto() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); startAuto(); });

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); startAuto(); });
    });

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);
    slider.addEventListener('focusin', stopAuto);
    slider.addEventListener('focusout', startAuto);

    // Touch swipe support
    var touchStartX = 0;
    slider.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    slider.addEventListener('touchend', function (e) {
        var diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) {
            goTo(diff > 0 ? current + 1 : current - 1);
            startAuto();
        }
    }, { passive: true });

    syncAspectRatio(current);
    updateTrack();
    startAuto();
}());
