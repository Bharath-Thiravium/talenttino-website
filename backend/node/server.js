import express from 'express';
import cors from 'cors';
import bcrypt from 'bcryptjs';
import dotenv from 'dotenv';
import crypto from 'crypto';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { query } from './db.js';

dotenv.config();

const app = express();
const port = Number(process.env.PORT || 5010);
const host = process.env.HOST || '127.0.0.1';
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '../..');
const brochureDir = path.join(projectRoot, 'frontend/uploads/brochures');
const generatedBrochureSecret = process.env.BROCHURE_TOKEN_SECRET || process.env.SESSION_SECRET || 'talentteno-local-brochure-secret';
const isDevelopment = process.env.NODE_ENV === 'development';
function positiveIntEnv(name, fallback) {
  const value = Number.parseInt(process.env[name] || '', 10);
  return Number.isFinite(value) && value > 0 ? value : fallback;
}
const otpMobileLimitPerHour = positiveIntEnv('OTP_MOBILE_LIMIT_PER_HOUR', isDevelopment ? 20 : 5);
const otpIpLimitPerHour = positiveIntEnv('OTP_IP_LIMIT_PER_HOUR', isDevelopment ? 100 : 15);
const otpResendSeconds = positiveIntEnv('OTP_RESEND_SECONDS', 60);
const otpExpiryMinutes = positiveIntEnv('OTP_EXPIRY_MINUTES', 5);

app.set('trust proxy', Number(process.env.TRUST_PROXY || 1));
const allowedOrigins = (process.env.FRONTEND_ORIGIN || '')
  .split(',').map((o) => o.trim()).filter(Boolean);
app.use(cors({
  origin: (origin, cb) => {
    if (!origin || allowedOrigins.length === 0 || allowedOrigins.includes(origin)) return cb(null, true);
    if (isDevelopment && /^https?:\/\/(127\.0\.0\.1|localhost)(:[0-9]+)?$/.test(origin)) return cb(null, true);
    cb(new Error('CORS'));
  },
  credentials: true
}));
app.use(express.json({ limit: '32kb', type: ['application/json'] }));
app.use((req, res, next) => {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  next();
});

function asyncRoute(handler) {
  return (req, res, next) => Promise.resolve(handler(req, res, next)).catch(next);
}

function ok(res, payload = {}) {
  return res.json({ success: true, ...payload });
}

function fail(res, status, code, message, errors = {}, extra = {}) {
  if (typeof message !== 'string') {
    return res.status(status).json({ success: false, code: 'REQUEST_FAILED', message: code, errors: message || {} });
  }
  return res.status(status).json({ success: false, code, message, errors, ...extra });
}

function hashValue(value) {
  return crypto.createHmac('sha256', generatedBrochureSecret).update(String(value)).digest('hex');
}

function randomToken(bytes = 32) {
  return crypto.randomBytes(bytes).toString('base64url');
}

function normalizeMobile(value = '') {
  let mobile = String(value).trim().replace(/[\s-]+/g, '');
  if (mobile.startsWith('+91')) mobile = mobile.slice(3);
  else if (/^91[6-9][0-9]{9}$/.test(mobile)) mobile = mobile.slice(2);
  return mobile.replace(/\D+/g, '');
}

function normalizeName(value = '') {
  return String(value).trim().replace(/\s+/g, ' ');
}

function normalizeEmail(value = '') {
  return String(value).trim().replace(/\s+/g, '').toLowerCase();
}

function normalizeFreeText(value = '') {
  return String(value).trim().replace(/\s+/g, ' ');
}

function isRepeatedChars(value, min = 5) {
  return new RegExp(`^(.)\\1{${min - 1},}$`, 'i').test(String(value).replace(/\s+/g, ''));
}

const fakeWords = new Set(['test', 'testing', 'demo', 'admin', 'user', 'abc', 'xyz', 'none', 'unknown', 'sample', 'fake', 'null']);
const blockedEmailDomains = new Set(['mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com', 'yopmail.com', 'throwawaymail.com', 'fakeinbox.com']);
const blockedMobiles = new Set(['0000000000', '1111111111', '2222222222', '3333333333', '4444444444', '5555555555', '6666666666', '7777777777', '8888888888', '9999999999', '1234567890', '9876543210']);

function validateName(value) {
  const name = normalizeName(value);
  if (!name || name.length < 3 || name.length > 50) return 'Please enter a valid full name.';
  if (!/^[A-Za-z ]+$/.test(name)) return 'Please enter a valid full name.';
  if (isRepeatedChars(name, 5)) return 'Please enter a valid full name.';
  if (fakeWords.has(name.toLowerCase())) return 'Please enter a valid full name.';
  return '';
}

function validateEmail(value) {
  const email = normalizeEmail(value);
  if (!email || email.length > 190 || !/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/.test(email)) {
    return 'Please enter a valid email address.';
  }
  const domain = email.split('@').pop();
  if (!domain || blockedEmailDomains.has(domain)) return 'Temporary email addresses are not allowed.';
  return '';
}

function validateMobile(value) {
  const mobile = normalizeMobile(value);
  if (!/^[6-9][0-9]{9}$/.test(mobile) || blockedMobiles.has(mobile)) {
    return 'Please enter a valid 10-digit Indian mobile number.';
  }
  return '';
}

function validateLeadPayload(body) {
  const data = {
    courseTitle: normalizeFreeText(body.courseTitle || body.course_title),
    fullName: normalizeName(body.fullName || body.name),
    email: normalizeEmail(body.email),
    mobile: normalizeMobile(body.mobile || body.phone),
    degree: normalizeFreeText(body.degree),
    college: normalizeFreeText(body.college),
    address: normalizeFreeText(body.address) || 'Not provided',
    currentStatus: normalizeFreeText(body.currentStatus || body.study_year),
    otpVerificationRef: normalizeFreeText(body.otpVerificationRef),
    captchaToken: String(body.captchaToken || '').trim()
  };
  const errors = {};
  const nameError = validateName(data.fullName);
  const emailError = validateEmail(data.email);
  const mobileError = validateMobile(data.mobile);
  if (nameError) errors.name = nameError;
  if (emailError) errors.email = emailError;
  if (mobileError) errors.mobile = mobileError;
  if (data.degree.length < 2 || data.degree.length > 100) errors.degree = 'Please enter your degree.';
  if (data.college.length < 2 || data.college.length > 150) errors.college = 'Please enter your college name.';
  if (data.address.length > 300) errors.address = 'Please enter a shorter address.';
  if (!['1st Year', '2nd Year', '3rd Year', 'Passout'].includes(data.currentStatus)) errors.study_year = 'Please select your current year or status.';
  if (!data.courseTitle || /[<>]/.test(data.courseTitle) || data.courseTitle.length > 150) errors.courseTitle = 'Please select a valid course.';
  return { data, errors };
}

function clientIp(req) {
  return String(req.ip || '').trim().slice(0, 64);
}

async function ensureBrochureTables() {
  await query(`CREATE TABLE IF NOT EXISTS brochure_otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(10) NOT NULL,
    course_title VARCHAR(150) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    verification_ref_hash VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    resend_available_at DATETIME NOT NULL,
    attempt_count INT NOT NULL DEFAULT 0,
    send_count INT NOT NULL DEFAULT 1,
    verified_at DATETIME NULL,
    used_at DATETIME NULL,
    ip_address VARCHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bov_mobile (mobile),
    INDEX idx_bov_ip_created (ip_address, created_at),
    INDEX idx_bov_ref (verification_ref_hash)
  )`);
  await query(`CREATE TABLE IF NOT EXISTS brochure_download_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT NULL,
    course_title VARCHAR(150) NOT NULL,
    full_name VARCHAR(50) NOT NULL,
    email VARCHAR(190) NOT NULL,
    mobile VARCHAR(10) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    college VARCHAR(150) NOT NULL,
    address VARCHAR(300) NOT NULL,
    current_status VARCHAR(30) NOT NULL,
    mobile_verified TINYINT(1) NOT NULL DEFAULT 0,
    otp_verification_id BIGINT UNSIGNED NOT NULL,
    captcha_verified TINYINT(1) NOT NULL DEFAULT 0,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    download_token_hash VARCHAR(255) NOT NULL,
    token_expires_at DATETIME NOT NULL,
    downloaded_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bdl_mobile (mobile),
    INDEX idx_bdl_email (email),
    INDEX idx_bdl_course_title (course_title),
    INDEX idx_bdl_created_at (created_at),
    INDEX idx_bdl_download_token_hash (download_token_hash)
  )`);
}

async function findCourse(courseTitle) {
  const rows = await query(
    'SELECT id, title, slug, brochure_file FROM courses WHERE is_active = 1 AND LOWER(title) = LOWER(:title) LIMIT 1',
    { title: courseTitle }
  );
  return rows[0] || null;
}

async function sendSmsOtp(mobile, otp) {
  if (isDevelopment && process.env.DEV_OTP && !process.env.SMS_PROVIDER) {
    console.info('Development OTP mode is active for brochure download.');
    return { sent: true, simulated: true, message: 'OTP sent successfully.' };
  }
  const provider = String(process.env.SMS_PROVIDER || '').toLowerCase();
  if (!provider) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };

  try {
    if (provider === 'fast2sms') {
      if (!process.env.FAST2SMS_API_KEY) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };
      const response = await fetch('https://www.fast2sms.com/dev/bulkV2', {
        method: 'POST',
        headers: {
          authorization: process.env.FAST2SMS_API_KEY || '',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ route: 'otp', variables_values: otp, numbers: mobile }),
        signal: AbortSignal.timeout(10000)
      });
      return { sent: response.ok, simulated: false, code: response.ok ? undefined : 'OTP_SEND_FAILED', message: response.ok ? 'OTP sent successfully.' : 'Unable to send OTP right now. Please try again.' };
    }

    if (provider === 'msg91') {
      if (!process.env.MSG91_AUTH_KEY) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };
      const response = await fetch('https://api.msg91.com/api/v5/otp', {
        method: 'POST',
        headers: { authkey: process.env.MSG91_AUTH_KEY || '', 'Content-Type': 'application/json' },
        body: JSON.stringify({ mobile: `91${mobile}`, otp }),
        signal: AbortSignal.timeout(10000)
      });
      return { sent: response.ok, simulated: false, code: response.ok ? undefined : 'OTP_SEND_FAILED', message: response.ok ? 'OTP sent successfully.' : 'Unable to send OTP right now. Please try again.' };
    }

    if (provider === 'twilio') {
      const sid = process.env.TWILIO_ACCOUNT_SID || '';
      const token = process.env.TWILIO_AUTH_TOKEN || '';
      const from = process.env.TWILIO_FROM_NUMBER || '';
      if (!sid || !token || !from) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };
      const body = new URLSearchParams({ To: `+91${mobile}`, From: from, Body: `Your Talentteno brochure OTP is ${otp}. It expires in 5 minutes.` });
      const response = await fetch(`https://api.twilio.com/2010-04-01/Accounts/${sid}/Messages.json`, {
        method: 'POST',
        headers: { Authorization: `Basic ${Buffer.from(`${sid}:${token}`).toString('base64')}`, 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
        signal: AbortSignal.timeout(10000)
      });
      return { sent: response.ok, simulated: false, code: response.ok ? undefined : 'OTP_SEND_FAILED', message: response.ok ? 'OTP sent successfully.' : 'Unable to send OTP right now. Please try again.' };
    }

    if (['twilio_whatsapp', 'twilio-whatsapp', 'whatsapp_twilio', 'whatsapp-twilio'].includes(provider)) {
      const sid = process.env.TWILIO_ACCOUNT_SID || '';
      const token = process.env.TWILIO_AUTH_TOKEN || '';
      const from = process.env.TWILIO_WHATSAPP_FROM || '';
      if (!sid || !token || !from) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'WhatsApp OTP is not configured.' };
      const body = new URLSearchParams({
        To: `whatsapp:+91${mobile}`,
        From: from.startsWith('whatsapp:') ? from : `whatsapp:${from}`,
        Body: `Your Talentteno brochure OTP is ${otp}. It expires in ${otpExpiryMinutes} minutes.`
      });
      const response = await fetch(`https://api.twilio.com/2010-04-01/Accounts/${sid}/Messages.json`, {
        method: 'POST',
        headers: { Authorization: `Basic ${Buffer.from(`${sid}:${token}`).toString('base64')}`, 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
        signal: AbortSignal.timeout(10000)
      });
      return { sent: response.ok, simulated: false, code: response.ok ? undefined : 'OTP_SEND_FAILED', message: response.ok ? 'OTP sent successfully on WhatsApp.' : 'Unable to send WhatsApp OTP right now. Please try again.' };
    }

    if (['meta_whatsapp', 'meta-whatsapp', 'whatsapp_meta', 'whatsapp-meta', 'whatsapp'].includes(provider)) {
      const accessToken = process.env.WHATSAPP_ACCESS_TOKEN || '';
      const phoneNumberId = process.env.WHATSAPP_PHONE_NUMBER_ID || '';
      const templateName = process.env.WHATSAPP_TEMPLATE_NAME || '';
      const languageCode = process.env.WHATSAPP_TEMPLATE_LANGUAGE || 'en_US';
      if (!accessToken || !phoneNumberId || !templateName) return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'WhatsApp OTP is not configured.' };

      const components = [
        { type: 'body', parameters: [{ type: 'text', text: otp }] }
      ];
      if (process.env.WHATSAPP_TEMPLATE_BUTTON_INDEX !== undefined) {
        components.push({
          type: 'button',
          sub_type: 'url',
          index: String(process.env.WHATSAPP_TEMPLATE_BUTTON_INDEX || '0'),
          parameters: [{ type: 'text', text: otp }]
        });
      }

      const response = await fetch(`https://graph.facebook.com/v20.0/${phoneNumberId}/messages`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${accessToken}`, 'Content-Type': 'application/json' },
        body: JSON.stringify({
          messaging_product: 'whatsapp',
          to: `91${mobile}`,
          type: 'template',
          template: {
            name: templateName,
            language: { code: languageCode },
            components
          }
        }),
        signal: AbortSignal.timeout(10000)
      });
      return { sent: response.ok, simulated: false, code: response.ok ? undefined : 'OTP_SEND_FAILED', message: response.ok ? 'OTP sent successfully on WhatsApp.' : 'Unable to send WhatsApp OTP right now. Please try again.' };
    }
  } catch (error) {
    console.error('OTP provider error:', error.message);
    return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };
  }

  return { sent: false, simulated: false, code: 'OTP_SEND_FAILED', message: 'Unable to send OTP right now. Please try again.' };
}

async function verifyTurnstile(token, ip) {
  if (process.env.NODE_ENV === 'development' && !process.env.TURNSTILE_SECRET_KEY) return token === 'dev-turnstile';
  if (!process.env.TURNSTILE_SECRET_KEY || !token) return false;
  const response = await fetch('https://challenges.cloudflare.com/turnstile/v0/siteverify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ secret: process.env.TURNSTILE_SECRET_KEY, response: token, remoteip: ip })
  });
  if (!response.ok) return false;
  const payload = await response.json();
  return payload.success === true;
}

function safeDownloadName(course) {
  return `${String(course?.slug || course?.title || 'talentteno-brochure').toLowerCase().replace(/[^a-z0-9-]+/g, '-').replace(/^-+|-+$/g, '') || 'talentteno-brochure'}-brochure.pdf`;
}

function pdfEscape(text) {
  return String(text).replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
}

function buildGeneratedBrochurePdf(title) {
  const lines = [
    'Talentteno Institute',
    title || 'Talentteno Course Brochure',
    'Practical IT training with live projects, internship support, certification, and placement assistance.',
    'Contact Talentteno Institute for the complete syllabus, batch timing, fee details, and admission support.'
  ];
  const content = `BT
/F1 22 Tf
72 760 Td
(${pdfEscape(lines[0])}) Tj
/F1 16 Tf
0 -42 Td
(${pdfEscape(lines[1])}) Tj
/F1 11 Tf
0 -42 Td
(${pdfEscape(lines[2])}) Tj
0 -24 Td
(${pdfEscape(lines[3])}) Tj
ET`;
  const objects = [
    '1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n',
    '2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n',
    '3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n',
    '4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n',
    `5 0 obj\n<< /Length ${Buffer.byteLength(content)} >>\nstream\n${content}\nendstream\nendobj\n`
  ];
  let pdf = '%PDF-1.4\n';
  const offsets = [0];
  for (const object of objects) {
    offsets.push(Buffer.byteLength(pdf));
    pdf += object;
  }
  const xrefOffset = Buffer.byteLength(pdf);
  pdf += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
  for (let i = 1; i <= objects.length; i += 1) {
    pdf += `${String(offsets[i]).padStart(10, '0')} 00000 n \n`;
  }
  pdf += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`;
  return Buffer.from(pdf);
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

app.post('/api/brochure/send-otp', asyncRoute(async (req, res) => {
  await ensureBrochureTables();
  const mobile = normalizeMobile(req.body.mobile);
  const courseTitle = normalizeFreeText(req.body.courseTitle);
  const ip = clientIp(req);
  const errors = {};
  const mobileError = validateMobile(mobile);
  if (mobileError) errors.mobile = mobileError;
  if (!courseTitle || /[<>]/.test(courseTitle)) errors.courseTitle = 'Please select a valid course.';
  const course = courseTitle ? await findCourse(courseTitle) : null;
  if (!course) errors.courseTitle = 'Please select a valid course.';
  if (Object.keys(errors).length) {
    return fail(res, 422, mobileError ? 'INVALID_MOBILE' : 'INVALID_COURSE', 'Please correct the highlighted fields.', errors);
  }

  const recent = await query(
    `SELECT GREATEST(TIMESTAMPDIFF(SECOND, NOW(), resend_available_at), 0) AS retry_after
     FROM brochure_otp_verifications
     WHERE mobile = :mobile AND course_title = :courseTitle
       AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
     ORDER BY id DESC LIMIT 1`,
    { mobile, courseTitle: course.title }
  );
  const retryAfter = Number(recent[0]?.retry_after || 0);
  if (retryAfter > 0) {
    return fail(
      res,
      429,
      'OTP_RESEND_WAIT',
      'Please wait before requesting another OTP.',
      { mobile: `Please wait ${retryAfter} seconds before requesting another OTP.` },
      { retryAfter }
    );
  }

  const mobileCount = await query(
    'SELECT COUNT(*) AS total FROM brochure_otp_verifications WHERE mobile = :mobile AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
    { mobile }
  );
  if (Number(mobileCount[0]?.total || 0) >= otpMobileLimitPerHour) {
    return fail(
      res,
      429,
      'OTP_MOBILE_LIMIT',
      'Too many OTP requests for this mobile number. Please try again after one hour.',
      { mobile: 'Too many OTP requests for this mobile number. Please try again after one hour.' }
    );
  }

  const ipCount = await query(
    'SELECT COUNT(*) AS total FROM brochure_otp_verifications WHERE ip_address = :ip AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
    { ip }
  );
  if (Number(ipCount[0]?.total || 0) >= otpIpLimitPerHour) {
    return fail(
      res,
      429,
      'OTP_IP_LIMIT',
      'Too many OTP requests from this network. Please try again later.',
      { mobile: 'Too many OTP requests from this network. Please try again later.' }
    );
  }

  const isDevelopmentOtp = isDevelopment && process.env.DEV_OTP;
  const otp = isDevelopmentOtp
    ? String(process.env.DEV_OTP).padStart(6, '0').slice(0, 6)
    : String(crypto.randomInt(100000, 1000000));
  const smsResult = await sendSmsOtp(mobile, otp);
  if (!smsResult.sent) {
    return fail(
      res,
      503,
      smsResult.code || 'OTP_SEND_FAILED',
      smsResult.message || 'Unable to send OTP right now. Please try again.',
      { mobile: smsResult.message || 'Unable to send OTP right now. Please try again.' }
    );
  }

  const otpHash = await bcrypt.hash(otp, 10);
  await query(
    'UPDATE brochure_otp_verifications SET used_at = NOW() WHERE mobile = :mobile AND course_title = :courseTitle AND used_at IS NULL',
    { mobile, courseTitle: course.title }
  );
  await query(
    `INSERT INTO brochure_otp_verifications
      (mobile, course_title, otp_hash, expires_at, resend_available_at, ip_address)
      VALUES (:mobile, :courseTitle, :otpHash, DATE_ADD(NOW(), INTERVAL ${otpExpiryMinutes} MINUTE), DATE_ADD(NOW(), INTERVAL ${otpResendSeconds} SECOND), :ip)`,
    { mobile, courseTitle: course.title, otpHash, ip }
  );

  return ok(res, { message: 'OTP sent successfully.', resendAfterSeconds: otpResendSeconds, expiresInSeconds: otpExpiryMinutes * 60 });
}));

app.post('/api/brochure/verify-otp', asyncRoute(async (req, res) => {
  await ensureBrochureTables();
  const mobile = normalizeMobile(req.body.mobile);
  const otp = String(req.body.otp || '').trim();
  const courseTitle = normalizeFreeText(req.body.courseTitle);
  const mobileError = validateMobile(mobile);
  if (mobileError || !/^[0-9]{6}$/.test(otp)) {
    return fail(res, 422, mobileError ? 'INVALID_MOBILE' : 'INVALID_OTP', 'Please correct the highlighted fields.', {
      ...(mobileError ? { mobile: mobileError } : {}),
      ...(!/^[0-9]{6}$/.test(otp) ? { otp: 'Please enter the 6-digit OTP.' } : {})
    });
  }
  const rows = await query(
    `SELECT * FROM brochure_otp_verifications
     WHERE mobile = :mobile AND course_title = :courseTitle AND used_at IS NULL
     ORDER BY id DESC LIMIT 1`,
    { mobile, courseTitle }
  );
  const record = rows[0];
  if (!record || new Date(record.expires_at).getTime() < Date.now()) return fail(res, 422, 'OTP_EXPIRED', 'OTP expired. Please request a new OTP.', { otp: 'OTP expired. Please request a new OTP.' });
  if (Number(record.attempt_count || 0) >= 3) return fail(res, 429, 'OTP_ATTEMPT_LIMIT', 'Maximum OTP attempts reached. Please request a new OTP.', { otp: 'Maximum OTP attempts reached.' });

  const valid = await bcrypt.compare(otp, record.otp_hash);
  if (!valid) {
    await query('UPDATE brochure_otp_verifications SET attempt_count = attempt_count + 1 WHERE id = :id', { id: record.id });
    return fail(res, 422, 'INVALID_OTP', 'Incorrect OTP. Please try again.', { otp: 'Incorrect OTP. Please try again.' });
  }

  const verificationRef = randomToken(24);
  await query(
    'UPDATE brochure_otp_verifications SET verified_at = NOW(), verification_ref_hash = :refHash WHERE id = :id',
    { refHash: hashValue(verificationRef), id: record.id }
  );
  return ok(res, { message: 'Mobile number verified successfully.', verificationRef });
}));

app.post('/api/brochure/submit', asyncRoute(async (req, res) => {
  await ensureBrochureTables();
  const ip = clientIp(req);
  const { data, errors } = validateLeadPayload(req.body);
  if (Object.keys(errors).length) return fail(res, 422, 'Please correct the highlighted fields.', errors);
  const course = await findCourse(data.courseTitle);
  if (!course) return fail(res, 422, 'Please correct the highlighted fields.', { courseTitle: 'Please select a valid course.' });

  const captchaOk = await verifyTurnstile(data.captchaToken, ip);
  if (!captchaOk) return fail(res, 422, 'Captcha verification failed. Please try again.', { captcha: 'Captcha verification failed. Please try again.' });

  const otpRows = await query(
    `SELECT * FROM brochure_otp_verifications
     WHERE verification_ref_hash = :refHash AND mobile = :mobile AND course_title = :courseTitle
       AND verified_at IS NOT NULL AND used_at IS NULL AND expires_at >= NOW()
     ORDER BY id DESC LIMIT 1`,
    { refHash: hashValue(data.otpVerificationRef), mobile: data.mobile, courseTitle: course.title }
  );
  const otpRecord = otpRows[0];
  if (!otpRecord) return fail(res, 403, 'OTP_EXPIRED', 'Mobile verification expired. Please verify OTP again.', { mobile: 'Mobile verification expired. Please verify OTP again.' });

  const duplicateMobile = await query(
    'SELECT id FROM brochure_download_leads WHERE mobile = :mobile AND course_title = :courseTitle AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 1',
    { mobile: data.mobile, courseTitle: course.title }
  );
  if (duplicateMobile[0]) return fail(res, 429, 'You have already downloaded this brochure recently.', { mobile: 'You have already downloaded this brochure recently.' });

  const duplicateEmail = await query(
    'SELECT id FROM brochure_download_leads WHERE email = :email AND course_title = :courseTitle AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 1',
    { email: data.email, courseTitle: course.title }
  );
  if (duplicateEmail[0]) return fail(res, 429, 'You have already downloaded this brochure recently.', { email: 'You have already downloaded this brochure recently.' });

  const ipCount = await query(
    'SELECT COUNT(*) AS total FROM brochure_download_leads WHERE ip_address = :ip AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
    { ip }
  );
  if (Number(ipCount[0]?.total || 0) >= 10) return fail(res, 429, 'Too many download attempts. Please try again later.', { form: 'Too many download attempts. Please try again later.' });

  const token = randomToken(32);
  const tokenHash = hashValue(token);
  const lead = await query(
    `INSERT INTO brochure_download_leads
      (course_id, course_title, full_name, email, mobile, degree, college, address, current_status, mobile_verified, otp_verification_id, captcha_verified, ip_address, user_agent, download_token_hash, token_expires_at)
      VALUES (:courseId, :courseTitle, :fullName, :email, :mobile, :degree, :college, :address, :currentStatus, 1, :otpId, 1, :ip, :userAgent, :tokenHash, DATE_ADD(NOW(), INTERVAL 10 MINUTE))`,
    {
      courseId: course.id,
      courseTitle: course.title,
      fullName: data.fullName,
      email: data.email,
      mobile: data.mobile,
      degree: data.degree,
      college: data.college,
      address: data.address,
      currentStatus: data.currentStatus,
      otpId: otpRecord.id,
      ip,
      userAgent: String(req.get('user-agent') || '').slice(0, 255),
      tokenHash
    }
  );
  await query('UPDATE brochure_otp_verifications SET used_at = NOW() WHERE id = :id', { id: otpRecord.id });
  await query(
    'INSERT INTO enquiries (name, email, phone, course_id, course_name, message, type, status) VALUES (:name, :email, :phone, :courseId, :courseTitle, :message, "download", "new")',
    {
      name: data.fullName,
      email: data.email,
      phone: data.mobile,
      courseId: course.id,
      courseTitle: course.title,
      message: `Degree: ${data.degree}\nCollege: ${data.college}\nAddress: ${data.address}\nYear Status: ${data.currentStatus}\nVerified brochure lead ID: ${lead.insertId}`
    }
  );

  return ok(res, {
    message: 'Your details have been verified.',
    downloadUrl: `/api/brochure/download/${token}`
  });
}));

app.get('/api/brochure/download/:token', asyncRoute(async (req, res) => {
  await ensureBrochureTables();
  const token = String(req.params.token || '');
  if (!/^[A-Za-z0-9_-]{32,}$/.test(token)) return fail(res, 404, 'Invalid or expired download link.');
  const rows = await query(
    `SELECT l.*, c.slug, c.brochure_file
     FROM brochure_download_leads l
     LEFT JOIN courses c ON c.id = l.course_id
     WHERE l.download_token_hash = :tokenHash AND l.downloaded_at IS NULL AND l.token_expires_at >= NOW()
     LIMIT 1`,
    { tokenHash: hashValue(token) }
  );
  const lead = rows[0];
  if (!lead) return fail(res, 410, 'This download link has expired or was already used.');

  const brochureName = path.basename(String(lead.brochure_file || ''));
  const filePath = brochureName ? path.join(brochureDir, brochureName) : '';
  await query('UPDATE brochure_download_leads SET downloaded_at = NOW() WHERE id = :id', { id: lead.id });
  if (lead.course_id) await query('UPDATE courses SET download_count = download_count + 1 WHERE id = :id', { id: lead.course_id });
  if (!filePath || !filePath.startsWith(brochureDir) || !fs.existsSync(filePath) || !fs.statSync(filePath).isFile()) {
    const pdf = buildGeneratedBrochurePdf(lead.course_title);
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader('Content-Disposition', `attachment; filename="${safeDownloadName(lead)}"`);
    res.setHeader('Content-Length', String(pdf.length));
    return res.end(pdf);
  }
  res.download(filePath, safeDownloadName(lead), (err) => {
    if (err && !res.headersSent) res.status(500).json({ success: false, message: 'Unable to download brochure right now.' });
  });
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
  res.status(500).json({ success: false, message: 'Server error. Please try again later.', errors: {} });
});

app.listen(port, host, () => {
  console.log(`Talentteno Node backend running on http://${host}:${port}`);
});
