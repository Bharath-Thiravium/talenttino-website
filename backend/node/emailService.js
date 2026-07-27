import './env.js';
import nodemailer from 'nodemailer';

const REQUIRED_SMTP_FIELDS = [
  'SMTP_HOST',
  'SMTP_PORT',
  'SMTP_USER',
  'SMTP_PASS',
  'SMTP_FROM_EMAIL'
];

function maskEmail(value) {
  const email = String(value || '').trim();
  const [local, domain] = email.split('@');
  if (!local || !domain) return email ? '[invalid-email]' : '[empty]';
  const maskedLocal = local.length <= 2
    ? `${local[0] || ''}***`
    : `${local.slice(0, 2)}***${local.slice(-1)}`;
  return `${maskedLocal}@${domain}`;
}

function maskPassword(value) {
  const normalized = String(value || '').replace(/\s+/g, '');
  if (!normalized) return '[empty]';
  if (normalized.length <= 4) return `[set:${normalized.length} chars]`;
  return `${normalized.slice(0, 2)}***${normalized.slice(-2)} (${normalized.length} chars)`;
}

export function getMaskedSmtpConfig() {
  return {
    envPath: process.env.DOTENV_CONFIG_PATH || '[process cwd]/.env',
    host: process.env.SMTP_HOST || '[empty]',
    port: process.env.SMTP_PORT || '[empty]',
    secure: String(process.env.SMTP_SECURE || 'false'),
    requireTLS: true,
    user: maskEmail(process.env.SMTP_USER),
    fromEmail: maskEmail(process.env.SMTP_FROM_EMAIL),
    fromName: process.env.SMTP_FROM_NAME || 'Talentteno Institute',
    pass: maskPassword(process.env.SMTP_PASS)
  };
}

export function logMaskedSmtpConfig() {
  console.info('SMTP configuration:', getMaskedSmtpConfig());
}

export function getMissingSmtpFields() {
  return REQUIRED_SMTP_FIELDS.filter((field) => {
    const value = String(process.env[field] || '').trim();
    if (!value) return true;
    if (field === 'SMTP_PASS' && /^your[_-]|google[_-]?app[_-]?password|app[_-]?password[_-]?here/i.test(value)) return true;
    if ((field === 'SMTP_USER' || field === 'SMTP_FROM_EMAIL') && /^your[_-]/i.test(value)) return true;
    return false;
  });
}

export function isEmailConfigured() {
  return getSmtpCredentialProblem() === null;
}

export function smtpConfigMissingPayload() {
  return {
    success: false,
    code: 'SMTP_CONFIG_MISSING',
    message: 'Email service is not configured. Please contact the administrator.',
    missingFields: getMissingSmtpFields()
  };
}

function normalizedSmtpPass() {
  return String(process.env.SMTP_PASS || '').trim();
}

export function getSmtpCredentialProblem() {
  const missingFields = getMissingSmtpFields();
  if (missingFields.length) return smtpConfigMissingPayload();

  const host = String(process.env.SMTP_HOST || '').toLowerCase();
  const user = String(process.env.SMTP_USER || '').trim().toLowerCase();
  const fromEmail = String(process.env.SMTP_FROM_EMAIL || '').trim().toLowerCase();
  const pass = normalizedSmtpPass();
  if (host === 'smtp.gmail.com' && !/@gmail\.com$|@googlemail\.com$/.test(user)) {
    return {
      success: false,
      code: 'SMTP_AUTH_FAILED',
      message: 'Invalid Gmail SMTP sender. SMTP_USER must be the Gmail account that owns the App Password.'
    };
  }
  if (host === 'smtp.gmail.com' && fromEmail !== user) {
    return {
      success: false,
      code: 'SMTP_AUTH_FAILED',
      message: 'Invalid Gmail SMTP sender. SMTP_FROM_EMAIL must match SMTP_USER for Gmail SMTP.'
    };
  }
  if (host.includes('gmail') && !/^[A-Za-z0-9]{16}$/.test(pass)) {
    return {
      success: false,
      code: 'SMTP_AUTH_FAILED',
      message: 'Email authentication failed. Use a 16-character Google App Password for SMTP_PASS.'
    };
  }
  return null;
}

export function logEmailError(context, error) {
  console.error(`${context}:`, {
    code: error?.code || 'UNKNOWN',
    command: error?.command || '',
    responseCode: error?.responseCode || '',
    response: error?.response || '',
    message: error?.message || String(error)
  });
  if (process.env.NODE_ENV === 'development' && error?.stack) {
    console.error(error.stack);
  }
}

export function classifyEmailError(error) {
  const code = String(error?.code || '').toUpperCase();
  const command = String(error?.command || '').toUpperCase();
  const message = String(error?.message || '').toLowerCase();

  if (code === 'SMTP_AUTH_FAILED' || code === 'EAUTH' || command === 'AUTH' || message.includes('auth')) {
    return {
      status: 500,
      code: 'SMTP_AUTH_FAILED',
      message: 'Invalid Google App Password. Generate a new 16-character App Password and update backend/node/.env.'
    };
  }
  if (code === 'ENOTFOUND') {
    return {
      status: 500,
      code: 'SMTP_DNS_FAILED',
      message: 'Email server hostname could not be resolved.'
    };
  }
  if (code === 'ETIMEDOUT' || message.includes('timeout')) {
    return {
      status: 500,
      code: 'SMTP_TIMEOUT',
      message: 'Unable to connect to the email server.'
    };
  }
  if (code === 'ECONNREFUSED' || code === 'ECONNECTION') {
    return {
      status: 500,
      code: 'SMTP_CONNECTION_FAILED',
      message: 'Unable to connect to the email server.'
    };
  }
  return {
    status: 500,
    code: 'EMAIL_SEND_FAILED',
    message: 'Unable to send OTP email. Please try again.'
  };
}

let transporter;

export function getTransporter() {
  if (!transporter) {
    const smtpPass = (process.env.SMTP_PASS || '').trim();
    transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT || 587),
      secure: String(process.env.SMTP_SECURE || 'false').toLowerCase() === 'true',
      auth: {
        user: process.env.SMTP_USER,
        pass: smtpPass
      },
      requireTLS: true,
      connectionTimeout: 10000,
      greetingTimeout: 10000,
      socketTimeout: 15000
    });
  }
  return transporter;
}

function senderAddress() {
  const fromEmail = process.env.SMTP_FROM_EMAIL || process.env.SMTP_USER;
  const fromName = process.env.SMTP_FROM_NAME || 'Talentteno Institute';
  return `${fromName} <${fromEmail}>`;
}

function escapeHtml(value) {
  return String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export async function verifySmtpConnection() {
  const configProblem = getSmtpCredentialProblem();
  if (configProblem) {
    if (configProblem.code === 'SMTP_CONFIG_MISSING') {
      console.warn(`SMTP_CONFIG_MISSING: ${configProblem.missingFields.join(', ')}`);
    } else {
      console.warn(configProblem.code);
    }
    return { ok: false, code: configProblem.code };
  }

  try {
    await getTransporter().verify();
    console.info('SMTP connection verified successfully');
    return { ok: true };
  } catch (error) {
    const classified = classifyEmailError(error);
    console.warn(`SMTP verification failed: ${classified.code}`);
    logEmailError('SMTP verify error', error);
    return { ok: false, code: classified.code };
  }
}

export async function sendOtpEmail({ name, email, otp, course }) {
  const configProblem = getSmtpCredentialProblem();
  if (configProblem) {
    const error = new Error(configProblem.message);
    error.code = configProblem.code;
    error.missingFields = configProblem.missingFields || [];
    throw error;
  }

  const safeName = escapeHtml(name || 'Student');
  const safeCourse = escapeHtml(course || 'Talentteno Course Brochure');
  const expiresMinutes = Number(process.env.OTP_EXPIRY_MINUTES || 10);
  const text = [
    'Talentteno Institute',
    '',
    `Hi ${name || 'Student'},`,
    '',
    `Your 6-digit OTP for ${course || 'Talentteno Course Brochure'} is ${otp}.`,
    `This OTP is valid for ${expiresMinutes} minutes.`,
    '',
    'Do not share this OTP with anyone.',
    '',
    'Talentteno Institute'
  ].join('\n');

  const html = `
    <div style="font-family:Arial,sans-serif;line-height:1.5;color:#111827">
      <h2 style="margin:0 0 12px;color:#0845b2">Talentteno Institute</h2>
      <p>Hi ${safeName},</p>
      <p>Your 6-digit OTP for <strong>${safeCourse}</strong> is:</p>
      <p style="font-size:28px;font-weight:700;letter-spacing:4px;margin:18px 0;color:#0845b2">${otp}</p>
      <p>This OTP is valid for ${expiresMinutes} minutes.</p>
      <p><strong>Do not share this OTP with anyone.</strong></p>
    </div>`;

  return getTransporter().sendMail({
    from: senderAddress(),
    to: email,
    subject: 'Your OTP for Talentteno Brochure Download',
    text,
    html
  });
}
