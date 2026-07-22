-- =============================================
-- Talentteno Institute - Database Setup
-- Run this in phpMyAdmin or MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS talentteno_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE talentteno_db;

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    course_type ENUM('course', 'short', 'popular', 'advanced', 'designing', 'cyber') DEFAULT 'course',
    description TEXT,
    highlights TEXT,
    duration VARCHAR(100),
    fee DECIMAL(10,2) DEFAULT 0,
    original_fee DECIMAL(10,2) DEFAULT 0,
    brochure_file VARCHAR(255),
    image VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS course_seed_log (
    course_type VARCHAR(30) PRIMARY KEY,
    seeded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Course Enquiries / Downloads Table
CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    course_id INT,
    course_name VARCHAR(255),
    message TEXT,
    resume_path VARCHAR(255),
    type ENUM('enquiry', 'download', 'callback') DEFAULT 'enquiry',
    status ENUM('new', 'contacted', 'enrolled', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    email VARCHAR(255),
    role ENUM('superadmin', 'admin', 'staff') DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    course VARCHAR(255),
    review TEXT NOT NULL,
    rating INT DEFAULT 5,
    photo VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Batches / Schedule Table
CREATE TABLE IF NOT EXISTS batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    batch_name VARCHAR(255),
    start_date DATE,
    timing VARCHAR(100),
    mode ENUM('offline', 'online', 'hybrid') DEFAULT 'offline',
    seats_total INT DEFAULT 20,
    seats_filled INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Services Table (managed dynamically from Admin -> shown on Frontend)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-laptop-code',
    image VARCHAR(255),
    short_desc VARCHAR(500),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Process / "How It Works" Steps Table (managed dynamically from Admin -> shown on Frontend)
CREATE TABLE IF NOT EXISTS process_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    step_number INT DEFAULT 1,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    icon VARCHAR(100) DEFAULT 'fa-flag',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Careers Table (managed dynamically from Admin -> shown on Frontend)
CREATE TABLE IF NOT EXISTS careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-briefcase',
    image VARCHAR(255),
    short_desc VARCHAR(500),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog Posts Table (managed dynamically from Admin -> shown on Frontend)
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-newspaper',
    image VARCHAR(255),
    short_desc VARCHAR(500),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects Table (managed dynamically from Admin -> shown on Frontend)
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-diagram-project',
    image VARCHAR(255),
    short_desc VARCHAR(500),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Home Slider Table (managed dynamically from Admin -> shown on Homepage hero)
CREATE TABLE IF NOT EXISTS home_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT '',
    image VARCHAR(255) NOT NULL,
    mobile_image VARCHAR(255) DEFAULT '',
    sort_order INT DEFAULT 0,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Site Settings Table (single row) - About Us content, contact info, social links
-- Editable from Admin -> Manage About/Site Content -> reflected instantly on Frontend
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY DEFAULT 1,
    site_name VARCHAR(150) DEFAULT 'Talentteno Institute',
    tagline VARCHAR(255) DEFAULT 'The Future of Your IT Career Starts Here',
    about_title VARCHAR(255) DEFAULT 'About Talentteno Institute',
    about_content TEXT,
    mission TEXT,
    vision TEXT,
    founded_year VARCHAR(10) DEFAULT '2020',
    total_students VARCHAR(20) DEFAULT '2000+',
    total_trainers VARCHAR(20) DEFAULT '15+',
    success_rate VARCHAR(20) DEFAULT '100%',
    avg_rating VARCHAR(10) DEFAULT '4.9',
    address VARCHAR(500) DEFAULT 'Plot 81, Poriyalar Nagar, Tiruppalai, Madurai, Tamil Nadu - 625014',
    phone1 VARCHAR(20) DEFAULT '+91 82484 15023',
    phone2 VARCHAR(20) DEFAULT '+91 63836 43141',
    email VARCHAR(150) DEFAULT 'talentteno.socials@gmail.com',
    facebook_url VARCHAR(255) DEFAULT '#',
    instagram_url VARCHAR(255) DEFAULT '#',
    linkedin_url VARCHAR(255) DEFAULT '#',
    youtube_url VARCHAR(255) DEFAULT '#',
    seo_title VARCHAR(255) DEFAULT 'Talentteno Institute | Best IT Training Institute in Madurai',
    seo_description VARCHAR(500) DEFAULT 'Talentteno Institute offers practical IT training in Madurai for Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with live projects, free internship and placement assistance.',
    seo_keywords VARCHAR(700) DEFAULT 'IT training institute in Madurai, best software training institute Madurai, full stack course Madurai, data science course Madurai, cyber security course Madurai, digital marketing course Madurai, UI UX course Madurai, Tally course Madurai',
    business_hours VARCHAR(120) DEFAULT 'Monday to Saturday, 9:00 AM to 7:00 PM',
    footer_description VARCHAR(500) DEFAULT 'Practical IT training in Madurai with free internship, spoken English support, live projects, certification and placement assistance.',
    footer_copyright VARCHAR(255) DEFAULT '© 2026 Talentteno Institute | All Rights Reserved',
    map_embed_url TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- Default Admin User
-- Username: admin | Password: password
-- =============================================
INSERT INTO admin_users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'admin@talentteno.com', 'superadmin');

-- Sample Courses
INSERT INTO courses (title, slug, category, description, highlights, duration, fee, original_fee, is_featured, is_active) VALUES
('Data Science & Data Analyst', 'data-science', 'Data & AI', 'Master Python, Machine Learning, Data Visualization, SQL and become a certified Data Analyst.', 'Python\nMachine Learning\nAI Projects', '4 Months', 18000, 25000, 1, 1),
('Full Stack Web Development', 'full-stack-development', 'Development', 'Learn HTML, CSS, JavaScript, React, Node.js, PHP, MySQL and build real-world web applications.', 'Live Website\nInternship\nPlacement Support', '6 Months', 20000, 30000, 1, 1),
('Digital Marketing', 'digital-marketing', 'Marketing', 'SEO, Social Media Marketing, Google Ads, Email Marketing, Analytics and complete digital marketing course.', 'Google Ads\nSEO\nLive Campaigns', '3 Months', 12000, 18000, 1, 1),
('Artificial Intelligence & Machine Learning', 'ai-machine-learning', 'Data & AI', 'Deep Learning, NLP, Computer Vision, TensorFlow and become an AI/ML expert.', 'Python\nDeep Learning\nAI Projects', '5 Months', 22000, 32000, 1, 1),
('Cyber Security', 'cyber-security', 'Security', 'Ethical Hacking, Network Security, VAPT, CEH and complete cybersecurity training.', 'Kali Linux\nLive Labs\nCertification', '4 Months', 20000, 28000, 0, 1),
('Python Programming', 'python-programming', 'Development', 'Python basics to advanced, Django, Flask, automation scripts and project-based learning.', 'Core Python\nDjango Basics\nProjects', '2 Months', 8000, 12000, 0, 1),
('Tally', 'tally', 'Business', 'Accounting basics, GST, inventory, payroll and practical business accounting workflows.', 'Tally Prime\nGST Practical\nPayroll', '2 Months', 14999, 35000, 0, 1),
('UI/UX Design', 'ui-ux-design', 'Design', 'Figma, wireframes, prototypes, design systems and portfolio-ready interface projects.', 'Figma\nPrototypes\nPortfolio', '3 Months', 14999, 35000, 0, 1),
('PHP & React JS', 'php-react-js', 'Development', 'Frontend and backend web development with React, PHP, MySQL and deployable projects.', 'React\nPHP & MySQL\nLive Project', '4 Months', 14999, 35000, 0, 1),
('C, C++, Java & Python', 'programming-languages', 'Development', 'Programming fundamentals through advanced problem solving using C, C++, Java and Python.', 'Live Coding\nProjects\nInterview Training', '4 Months', 14999, 35000, 0, 1),
('Full Stack Web Development with AI', 'full-stack-web-development-ai', 'Development', 'Full stack development upgraded with AI-assisted workflows, APIs and modern project practices.', 'Live Website\nInternship\nPlacement Support', '6 Months', 14999, 35000, 1, 1),
('Advanced Cyber Security Combo', 'advanced-cyber-security-combo', 'Security', 'CCNA, CCNP, MCSA/MCSE, Ethical Hacking, Firewall Security, AWS, Linux Administration, Network Security, Penetration Testing, SOC Analyst, CISA, CISSP and IoT Penetration Tester topics.', 'Kali Linux\nLive Labs\nCertification', '6 Months', 49999, 75000, 1, 1);

-- Sample Testimonials
INSERT INTO testimonials (student_name, company, course, review, rating, is_active) VALUES
('Karthik Rajendran', 'Infosys', 'Full Stack Development', 'Talentteno gave me the real-world skills I needed. Got placed at Infosys within 2 months of completing the course!', 5, 1),
('Priya Sundaram', 'TCS Digital', 'Data Science', 'The trainers are industry experts. Hands-on projects made the difference. Highly recommend!', 5, 1),
('Arun Murugan', 'HCL Technologies', 'Digital Marketing', 'From zero knowledge to a Digital Marketing Manager role — Talentteno made it possible!', 5, 1),
('Deepa Krishnan', 'Wipro', 'AI & Machine Learning', 'Best institute in Madurai for AI/ML. Live project experience was outstanding.', 5, 1);

-- Sample Services
INSERT INTO services (title, icon, short_desc, description, sort_order, is_active) VALUES
('IT Skill Training', 'fa-laptop-code', 'Basic to advanced hands-on training across top IT domains.', 'Structured, hands-on classroom and lab training covering Data Science, Full Stack Development, Digital Marketing, AI/ML and Cyber Security — designed with inputs from hiring partners so every module maps directly to real job requirements.', 1, 1),
('Free Internship Program', 'fa-id-badge', 'Work on live client projects before you graduate.', 'Every student completes a structured internship inside Talentteno, working on real client-style projects under the guidance of mentors. This builds a genuine portfolio and practical confidence before stepping into interviews.', 2, 1),
('100% Job Assistance', 'fa-briefcase', 'Dedicated placement cell connecting you to hiring companies.', 'Our placement team works with IT companies and startups across Tamil Nadu and beyond to line up interviews for every job-ready student, along with resume building, mock interviews and aptitude preparation.', 3, 1),
('Resume & Interview Preparation', 'fa-file-signature', 'Get your resume, LinkedIn and interview skills job-ready.', 'One-on-one resume reviews, LinkedIn profile optimisation, group discussions and mock technical/HR interview rounds conducted by trainers with real industry hiring experience.', 4, 1),
('Spoken English & Soft Skills', 'fa-comments', 'Free communication training included with every course.', 'Confidence in communication is as important as technical skill. Every student gets free spoken English and soft-skills sessions covering email etiquette, client communication and presentation skills.', 5, 1),
('Corporate & Campus Training', 'fa-building', 'Customised training programs for colleges and companies.', 'We partner with colleges and companies to deliver customised technical bootcamps, faculty development programs and corporate upskilling batches on flexible schedules.', 6, 1);

-- Sample Process / How It Works Steps
INSERT INTO process_steps (step_number, title, description, icon, sort_order, is_active) VALUES
(1, 'Free Counselling', 'Talk to our career counsellor about your background, interests and goals to find the right course for you.', 'fa-comments', 1, 1),
(2, 'Enroll & Start Learning', 'Join the next batch (offline, online or hybrid) with EMI options and start hands-on, mentor-led training.', 'fa-user-graduate', 2, 1),
(3, 'Live Projects & Internship', 'Apply what you learn on real client-style projects during your free internship inside Talentteno.', 'fa-project-diagram', 3, 1),
(4, 'Certification', 'Earn an industry-recognised Talentteno course completion certificate validating your skills.', 'fa-certificate', 4, 1),
(5, 'Interview Preparation', 'Get resume building, mock interviews and aptitude training from our placement cell.', 'fa-comments-dollar', 5, 1),
(6, 'Job Placement', 'Get connected with hiring partners through our 100% job assistance program until you get placed.', 'fa-handshake', 6, 1);

-- Default Site Settings (About Us content, stats, contact info)
INSERT INTO site_settings (id, site_name, tagline, about_title, about_content, mission, vision, founded_year, total_students, total_trainers, success_rate, avg_rating, address, phone1, phone2, email) VALUES
(1, 'Talentteno Institute', 'The Future of Your IT Career Starts Here',
'About Talentteno Institute',
'Talentteno Institute is a Madurai-based IT training institute dedicated to turning beginners into industry-ready professionals. We teach everything from the basics to advanced technologies across Data Science, Data Analyst, Full Stack Development with AI, Digital Marketing, AI/ML, Cyber Security, UI/UX Design, Tally, PHP & React JS and programming languages. Every course is built around practical, project-based learning delivered by trainers with real industry experience, and is backed by a free internship, free spoken English classes and a dedicated placement cell offering 100% job assistance. With flexible EMI options and certification on completion, Talentteno makes quality IT education accessible to every student who is serious about building a career in technology.',
'To bridge the gap between classroom learning and industry requirements by delivering practical, mentor-led IT training that makes every student genuinely job-ready — not just certificate-ready.',
'To become South Tamil Nadu''s most trusted IT training institute, known for transforming students into skilled professionals through honest training, real projects and a relentless focus on placement outcomes.',
'2020', '2000+', '15+', '100%', '4.9',
'Plot 81, Poriyalar Nagar, Tiruppalai, Madurai, Tamil Nadu - 625014',
'+91 82484 15023', '+91 63836 43141', 'talentteno.socials@gmail.com');

-- Review Showcase Images (Student Review section on home page)
CREATE TABLE IF NOT EXISTS review_showcase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    icon VARCHAR(60) NOT NULL DEFAULT 'fa-image',
    image VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO review_showcase (title, icon, image, sort_order, is_active) VALUES
('Full Stack Development', 'fa-code', 'uploads/media/full-stack-development-20260703-133158-761383.png', 1, 1),
('AI & Machine Learning', 'fa-brain', 'uploads/media/data-science-ai-20260703-133112-527863.png', 2, 1),
('Cyber Security', 'fa-shield-halved', 'uploads/media/cyber-security-20260703-133329-242125.png', 3, 1),
('Data Analyst', 'fa-chart-line', 'uploads/media/data-analyst-20260703-133130-702998.png', 4, 1),
('Digital Marketing', 'fa-bullhorn', 'uploads/media/digital-marketing-20260703-133146-981935.png', 5, 1),
('Programming Languages', 'fa-terminal', 'uploads/media/programming-languages-20260703-133210-630417.png', 6, 1);
