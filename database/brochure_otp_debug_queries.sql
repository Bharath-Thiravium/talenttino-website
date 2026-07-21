-- Inspect recent OTP send records without exposing OTP values.
SELECT
    mobile,
    course_title,
    ip_address,
    created_at,
    resend_available_at,
    verified_at,
    used_at,
    attempt_count
FROM brochure_otp_verifications
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC
LIMIT 100;

-- Count recent OTP records by mobile.
SELECT mobile, COUNT(*) AS sends_last_hour
FROM brochure_otp_verifications
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY mobile
ORDER BY sends_last_hour DESC;

-- Count recent OTP records by IP address.
SELECT ip_address, COUNT(*) AS sends_last_hour
FROM brochure_otp_verifications
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address
ORDER BY sends_last_hour DESC;

-- Optional local-development cleanup for test mobile numbers only.
-- Review the SELECT first, then uncomment DELETE only for local/dev data.
SELECT id, mobile, course_title, ip_address, created_at
FROM brochure_otp_verifications
WHERE mobile IN ('7395875934', '7395875935')
  AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);

-- DELETE FROM brochure_otp_verifications
-- WHERE mobile IN ('7395875934', '7395875935')
--   AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
