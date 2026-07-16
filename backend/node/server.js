import express from 'express';
import cors from 'cors';
import bcrypt from 'bcryptjs';
import dotenv from 'dotenv';
import { query } from './db.js';

dotenv.config();

const app = express();
const port = Number(process.env.PORT || 5000);
const host = process.env.HOST || '127.0.0.1';

app.use(cors({
  origin: process.env.FRONTEND_ORIGIN || true,
  credentials: true
}));
app.use(express.json());

function asyncRoute(handler) {
  return (req, res, next) => Promise.resolve(handler(req, res, next)).catch(next);
}

function normalizeCourse(row) {
  const highlights = String(row.highlights || '')
    .split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean);

  return {
    ...row,
    highlights: highlights.length
      ? highlights
      : [
          row.duration ? `${row.duration} training` : '',
          'Live projects',
          'Placement support'
        ].filter(Boolean),
    fee_label: Number(row.fee || 0) > 0
      ? `Rs ${Number(row.fee).toLocaleString('en-IN', { maximumFractionDigits: 0 })}/-`
      : 'Contact for fee'
  };
}

function stripHtml(value = '') {
  return String(value).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function limitText(value, maxLength = 170) {
  const text = stripHtml(value);
  if (text.length <= maxLength) return text;
  return `${text.slice(0, maxLength - 3).replace(/[\s.,;:-]+$/, '')}...`;
}

function publicBaseUrl(req) {
  const configured = String(process.env.PUBLIC_SITE_URL || '').trim().replace(/\/+$/, '');
  if (configured) return configured;
  const protocol = req.get('x-forwarded-proto') || req.protocol;
  return `${protocol}://${req.get('host')}`;
}

function normalizeSettings(row = {}) {
  return {
    site_name: row.site_name || 'Talentteno Institute',
    tagline: row.tagline || 'The Future of Your IT Career Starts Here',
    about_title: row.about_title || 'About Talentteno Institute',
    about_content: row.about_content || 'Talentteno Institute is a practical IT training institute focused on helping students move from basic knowledge to advanced job-ready skills.',
    mission: row.mission || 'To bridge the gap between classroom learning and industry requirements through practical, mentor-led IT training.',
    vision: row.vision || 'To become South Tamil Nadu\'s most trusted IT training institute.',
    founded_year: row.founded_year || '2020',
    total_students: row.total_students || '2000+',
    total_trainers: row.total_trainers || '15+',
    success_rate: row.success_rate || '100%',
    avg_rating: row.avg_rating || '4.9',
    address: row.address || 'Plot 81, Poriyalar Nagar, Tiruppalai, Madurai, Tamil Nadu - 625014',
    phone1: row.phone1 || '+91 82484 15023',
    phone2: row.phone2 || '+91 63836 43141',
    email: row.email || 'talentteno.socials@gmail.com',
    facebook_url: row.facebook_url || '#',
    instagram_url: row.instagram_url || '#',
    linkedin_url: row.linkedin_url || '#',
    youtube_url: row.youtube_url || '#',
    footer_description: row.footer_description || 'Practical IT training in Madurai with free internship, spoken English support, live projects, certification and placement assistance.',
    seo_title: row.seo_title || 'Talentteno Institute | Best IT Training Institute in Madurai',
    seo_description: row.seo_description || 'Talentteno Institute offers practical IT training in Madurai for Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with live projects, free internship and placement assistance.',
    seo_keywords: row.seo_keywords || 'IT training institute in Madurai, best software training institute Madurai, full stack course Madurai, data science course Madurai, cyber security course Madurai, digital marketing course Madurai, UI UX course Madurai, Tally course Madurai',
    business_hours: row.business_hours || 'Monday to Saturday, 9:00 AM to 7:00 PM'
  };
}

async function getSettings() {
  const rows = await query('SELECT * FROM site_settings WHERE id = 1');
  return normalizeSettings(rows[0] || {});
}

function buildCompanyProfile(settings) {
  return {
    name: settings.site_name,
    type: 'IT Training Institute',
    tagline: settings.tagline,
    description: settings.seo_description || settings.footer_description,
    founded_year: settings.founded_year,
    address: settings.address,
    phones: [settings.phone1, settings.phone2].filter(Boolean),
    email: settings.email,
    business_hours: settings.business_hours,
    stats: {
      students_trained: settings.total_students,
      expert_trainers: settings.total_trainers,
      career_support: settings.success_rate,
      average_rating: settings.avg_rating
    },
    services: [
      'Basic to advanced IT training',
      'Live project training',
      'Free internship guidance',
      'Spoken English and soft-skill support',
      'Resume building and interview preparation',
      'Placement assistance',
      'Corporate and campus training'
    ],
    social: {
      facebook: settings.facebook_url,
      instagram: settings.instagram_url,
      linkedin: settings.linkedin_url,
      youtube: settings.youtube_url
    }
  };
}

function buildSeoPayload(req, settings, overrides = {}) {
  const baseUrl = publicBaseUrl(req);
  const title = overrides.title || settings.seo_title;
  const description = limitText(overrides.description || settings.seo_description || settings.footer_description);
  const canonical = overrides.canonical || `${baseUrl}${req.path === '/api/seo' ? '/' : req.path}`;
  const image = overrides.image || `${baseUrl}/frontend/assets/images/talentteno-board.png`;
  const company = buildCompanyProfile(settings);
  const sameAs = Object.values(company.social).filter((url) => url && url !== '#');
  const organizationId = `${baseUrl}/frontend#organization`;
  const websiteId = `${baseUrl}/frontend#website`;
  const webpageId = `${canonical}#webpage`;
  const breadcrumbs = overrides.breadcrumbs || [
    { name: 'Home', url: `${baseUrl}/frontend/index.php` }
  ];
  const structuredData = {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': ['EducationalOrganization', 'LocalBusiness'],
        '@id': organizationId,
        name: settings.site_name,
        description: settings.seo_description || settings.footer_description,
        url: `${baseUrl}/frontend`,
        logo: {
          '@type': 'ImageObject',
          url: `${baseUrl}/frontend/assets/images/talentteno-board.png`
        },
        image,
        telephone: settings.phone1,
        email: settings.email,
        address: {
          '@type': 'PostalAddress',
          streetAddress: settings.address,
          addressLocality: 'Madurai',
          addressRegion: 'Tamil Nadu',
          postalCode: '625014',
          addressCountry: 'IN'
        },
        areaServed: ['Madurai', 'Tamil Nadu', 'India'],
        priceRange: 'Rs 8,000 - Rs 75,000',
        openingHours: 'Mo-Sa 09:00-19:00',
        sameAs,
        makesOffer: company.services.map((service) => ({
          '@type': 'Offer',
          itemOffered: { '@type': 'Service', name: service }
        }))
      },
      {
        '@type': 'WebSite',
        '@id': websiteId,
        url: `${baseUrl}/frontend`,
        name: settings.site_name,
        description: settings.seo_description || settings.footer_description,
        publisher: { '@id': organizationId },
        inLanguage: 'en-IN'
      },
      {
        '@type': 'WebPage',
        '@id': webpageId,
        url: canonical,
        name: title,
        description,
        isPartOf: { '@id': websiteId },
        about: { '@id': organizationId },
        primaryImageOfPage: {
          '@type': 'ImageObject',
          url: image
        },
        inLanguage: 'en-IN'
      },
      {
        '@type': 'BreadcrumbList',
        '@id': `${canonical}#breadcrumb`,
        itemListElement: breadcrumbs.map((item, index) => ({
          '@type': 'ListItem',
          position: index + 1,
          name: item.name,
          item: item.url
        }))
      }
    ]
  };

  return {
    title,
    description,
    keywords: overrides.keywords || settings.seo_keywords,
    canonical,
    robots: overrides.robots || 'index, follow, max-image-preview:large',
    theme_color: '#11143d',
    alternates: {
      'en-IN': canonical,
      'x-default': canonical
    },
    open_graph: {
      type: overrides.type || 'website',
      locale: 'en_IN',
      title,
      description,
      url: canonical,
      image,
      image_alt: `${settings.site_name} logo`,
      site_name: settings.site_name
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      image
    },
    structured_data: structuredData,
    company
  };
}

app.get('/api/health', asyncRoute(async (_req, res) => {
  await query('SELECT 1');
  res.json({ ok: true, service: 'talentteno-node-backend' });
}));

app.get('/api/settings', asyncRoute(async (_req, res) => {
  res.json(await getSettings());
}));

app.get('/api/company-profile', asyncRoute(async (_req, res) => {
  const settings = await getSettings();
  res.json(buildCompanyProfile(settings));
}));

app.get('/api/seo', asyncRoute(async (req, res) => {
  const settings = await getSettings();
  const page = String(req.query.page || 'home').trim().toLowerCase();
  const pages = {
    home: {
      title: 'Talentteno Institute | Best IT Training Institute in Madurai',
      description: 'Join Talentteno Institute in Madurai for practical IT courses, live projects, free internship, spoken English support, certification and placement assistance.',
      canonical: `${publicBaseUrl(req)}/frontend/index.php`,
      breadcrumbs: [
        { name: 'Home', url: `${publicBaseUrl(req)}/frontend/index.php` }
      ]
    },
    about: {
      title: 'About Talentteno Institute | Practical IT Training in Madurai',
      description: settings.about_content,
      canonical: `${publicBaseUrl(req)}/frontend/about.php`,
      breadcrumbs: [
        { name: 'Home', url: `${publicBaseUrl(req)}/frontend/index.php` },
        { name: 'About', url: `${publicBaseUrl(req)}/frontend/about.php` }
      ]
    },
    courses: {
      title: 'IT Courses in Madurai | Full Stack, Data Science, AI, Cyber Security',
      description: 'Explore Talentteno IT courses in Madurai including Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with internship and placement support.',
      canonical: `${publicBaseUrl(req)}/frontend/course.php`,
      breadcrumbs: [
        { name: 'Home', url: `${publicBaseUrl(req)}/frontend/index.php` },
        { name: 'Courses', url: `${publicBaseUrl(req)}/frontend/course.php` }
      ]
    },
    contact: {
      title: 'Contact Talentteno Institute Madurai | Course Counselling',
      description: 'Contact Talentteno Institute in Tiruppalai, Madurai for IT course admission, free counselling, demo class, EMI details, internship support and placement assistance.',
      canonical: `${publicBaseUrl(req)}/frontend/contact.php`,
      breadcrumbs: [
        { name: 'Home', url: `${publicBaseUrl(req)}/frontend/index.php` },
        { name: 'Contact', url: `${publicBaseUrl(req)}/frontend/contact.php` }
      ]
    }
  };

  res.json(buildSeoPayload(req, settings, pages[page] || pages.home));
}));

app.get('/api/courses', asyncRoute(async (req, res) => {
  const featured = req.query.featured === '1';
  const type = String(req.query.type || '').trim();
  const allowedTypes = new Set(['course', 'short', 'popular', 'advanced', 'designing', 'cyber']);
  const filters = ['is_active = 1'];
  const params = {};

  if (featured) filters.push('is_featured = 1');
  if (allowedTypes.has(type)) {
    filters.push('course_type = :type');
    params.type = type;
  }

  const rows = await query(
    `SELECT * FROM courses WHERE ${filters.join(' AND ')} ORDER BY is_featured DESC, id DESC`,
    params
  );
  res.json(rows.map(normalizeCourse));
}));

app.get('/api/services', asyncRoute(async (_req, res) => {
  res.json(await query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'));
}));

app.get('/api/process-steps', asyncRoute(async (_req, res) => {
  res.json(await query('SELECT * FROM process_steps WHERE is_active = 1 ORDER BY sort_order ASC, step_number ASC'));
}));

app.get('/api/testimonials', asyncRoute(async (_req, res) => {
  res.json(await query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC'));
}));

app.post('/api/enquiries', asyncRoute(async (req, res) => {
  const name = String(req.body.name || '').trim();
  const phone = String(req.body.phone || '').trim();
  const email = String(req.body.email || 'not-provided@talentteno.local').trim();
  const courseName = String(req.body.course_name || req.body.course || '').trim();
  const message = String(req.body.message || '').trim();
  const type = ['enquiry', 'download', 'callback'].includes(req.body.type) ? req.body.type : 'enquiry';

  if (!name || !phone) {
    return res.status(422).json({ ok: false, message: 'Name and phone number are required.' });
  }

  const result = await query(
    'INSERT INTO enquiries (name, email, phone, course_name, message, type, status) VALUES (:name, :email, :phone, :courseName, :message, :type, "new")',
    { name, email, phone, courseName, message, type }
  );

  res.status(201).json({ ok: true, id: result.insertId });
}));

app.post('/api/admin/login', asyncRoute(async (req, res) => {
  const username = String(req.body.username || '').trim();
  const password = String(req.body.password || '');

  if (!username || !password) {
    return res.status(422).json({ ok: false, message: 'Username and password are required.' });
  }

  const rows = await query('SELECT * FROM admin_users WHERE username = :username LIMIT 1', { username });
  const admin = rows[0];

  if (!admin || !bcrypt.compareSync(password, admin.password)) {
    return res.status(401).json({ ok: false, message: 'Invalid username or password.' });
  }

  await query('UPDATE admin_users SET last_login = NOW() WHERE id = :id', { id: admin.id });
  res.json({
    ok: true,
    admin: {
      id: admin.id,
      username: admin.username,
      full_name: admin.full_name,
      email: admin.email,
      role: admin.role
    }
  });
}));

app.use((err, _req, res, _next) => {
  console.error(err);
  res.status(500).json({ ok: false, message: 'Server error' });
});

app.listen(port, host, () => {
  console.log(`Talentteno Node backend running on http://${host}:${port}`);
});
