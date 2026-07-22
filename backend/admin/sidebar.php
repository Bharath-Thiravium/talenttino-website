<div class="admin-sidebar">
    <div class="sidebar-logo">
        <img src="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2" alt="Talentteno logo">
        <div>
            <span class="sidebar-name">Talentteno</span>
            <span class="sidebar-role">Admin Panel</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="enquiries.php" class="<?= basename($_SERVER['PHP_SELF']) === 'enquiries.php' ? 'active' : '' ?>">
            <i class="fas fa-inbox"></i> Enquiries
            <?php
            require_once '../includes/db.php';
            $new_count = $conn->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetch_row()[0];
            if ($new_count > 0) echo "<span class='badge-count'>$new_count</span>";
            ?>
        </a>
        <a href="courses.php" class="<?= basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Manage Courses
        </a>
        <a href="media.php" class="<?= basename($_SERVER['PHP_SELF']) === 'media.php' ? 'active' : '' ?>">
            <i class="fas fa-photo-film"></i> Gallery / Media
        </a>
        <a href="home_slider.php" class="<?= basename($_SERVER['PHP_SELF']) === 'home_slider.php' ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Home Slider
        </a>
        <a href="review_showcase.php" class="<?= basename($_SERVER['PHP_SELF']) === 'review_showcase.php' ? 'active' : '' ?>">
            <i class="fas fa-star-half-stroke"></i> Review Showcase
        </a>
        <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : '' ?>">
            <i class="fas fa-concierge-bell"></i> Manage Services
        </a>
        <a href="careers.php" class="<?= basename($_SERVER['PHP_SELF']) === 'careers.php' ? 'active' : '' ?>">
            <i class="fas fa-briefcase"></i> Manage Careers
        </a>
        <a href="blog.php" class="<?= basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i> Manage Blog
        </a>
        <a href="projects.php" class="<?= basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : '' ?>">
            <i class="fas fa-diagram-project"></i> Manage Projects
        </a>
        <a href="why_items.php" class="<?= basename($_SERVER['PHP_SELF']) === 'why_items.php' ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i> Why Talentteno
        </a>
        <a href="hiring_items.php" class="<?= basename($_SERVER['PHP_SELF']) === 'hiring_items.php' ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i> Hiring Items
        </a>
        <a href="franchise_items.php" class="<?= basename($_SERVER['PHP_SELF']) === 'franchise_items.php' ? 'active' : '' ?>">
            <i class="fas fa-handshake"></i> Franchise Items
        </a>
        <a href="process.php" class="<?= basename($_SERVER['PHP_SELF']) === 'process.php' ? 'active' : '' ?>">
            <i class="fas fa-route"></i> Manage Process Steps
        </a>
        <a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : '' ?>">
            <i class="fas fa-info-circle"></i> About / Site Content
        </a>
        <a href="testimonials.php" class="<?= basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'active' : '' ?>">
            <i class="fas fa-star"></i> Testimonials
        </a>
        <a href="account.php" class="<?= basename($_SERVER['PHP_SELF']) === 'account.php' ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> Admin Account
        </a>
        <div class="sidebar-divider"></div>
        <a href="../../frontend/index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>
