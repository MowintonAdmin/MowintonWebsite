<?php
if (!isset($page_title)) {
    $page_title = 'Mowinton Consulting';
}
if (!isset($page_description)) {
    $page_description = 'Mowinton Consulting - IT Consulting, Program & Project Management, Talent Specialist & Recruitment.';
}
if (!isset($active_nav)) {
    $active_nav = 'home';
}
if (!isset($root_path)) {
    $root_path = '.';
}
$base_url = $root_path;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- Google Fonts: Poppins & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>

<!-- Header Navigation -->
<header class="site-header">
    <div class="header-container">
        <!-- Logo -->
        <a href="<?php echo $base_url; ?>/" class="site-logo" aria-label="Mowinton Consulting Home">
            <img src="<?php echo $base_url; ?>/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg" 
                 alt="Mowinton Consulting" 
                 width="115" 
                 height="43"
                 onerror="this.onerror=null; this.src='<?php echo $base_url; ?>/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.avif';">
        </a>

        <!-- Desktop Navigation -->
        <nav class="main-nav" aria-label="Primary Navigation">
            <ul class="nav-list">
                <li class="nav-item <?php echo ($active_nav === 'home') ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo $base_url; ?>/">Home</a>
                </li>
                <li class="nav-item <?php echo ($active_nav === 'services') ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo $base_url; ?>/services/">Services</a>
                </li>
                <li class="nav-item <?php echo ($active_nav === 'about') ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo $base_url; ?>/about/">About</a>
                </li>
                <li class="nav-item has-dropdown <?php echo in_array($active_nav, ['careers', 'internship', 'interview', 'job-application']) ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo $base_url; ?>/careers-2/" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                        Careers
                        <svg class="dropdown-icon" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M1.5 4L6 8L10.5 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <ul class="dropdown-menu" aria-label="Careers submenu">
                        <li><a href="<?php echo $base_url; ?>/internship-program/">Internship Program</a></li>
                        <li><a href="<?php echo $base_url; ?>/interview-guide/">Interview Guide</a></li>
                        <li><a href="<?php echo $base_url; ?>/submit-job-application/">Submit Job Application</a></li>
                    </ul>
                </li>
                <li class="nav-item <?php echo ($active_nav === 'news') ? 'current-menu-item' : ''; ?>">
                    <a href="<?php echo $base_url; ?>/news-insights/">News &amp; Insights</a>
                </li>
            </ul>
        </nav>

        <!-- Mobile Menu Toggle Button -->
        <button class="mobile-menu-toggle" aria-label="Open mobile menu" aria-expanded="false" aria-controls="mobileNavModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <line x1="4" y1="6" x2="20" y2="6"></line>
                <line x1="4" y1="12" x2="20" y2="12"></line>
                <line x1="4" y1="18" x2="20" y2="18"></line>
            </svg>
        </button>
    </div>
</header>

<!-- Mobile Navigation Drawer / Overlay -->
<div class="mobile-nav-modal" id="mobileNavModal" aria-hidden="true" role="dialog" aria-label="Mobile Menu">
    <div class="mobile-nav-backdrop"></div>
    <div class="mobile-nav-content">
        <div class="mobile-nav-header">
            <a href="<?php echo $base_url; ?>/" class="site-logo">
                <img src="<?php echo $base_url; ?>/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg" alt="Mowinton Consulting" width="115" height="43">
            </a>
            <button class="mobile-menu-close" aria-label="Close mobile menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <ul class="mobile-nav-list">
            <li class="<?php echo ($active_nav === 'home') ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/">Home</a>
            </li>
            <li class="<?php echo ($active_nav === 'services') ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/services/">Services</a>
            </li>
            <li class="<?php echo ($active_nav === 'about') ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/about/">About</a>
            </li>
            <li class="mobile-dropdown <?php echo in_array($active_nav, ['careers', 'internship', 'interview', 'job-application']) ? 'active open' : ''; ?>">
                <div class="mobile-dropdown-header">
                    <a href="<?php echo $base_url; ?>/careers-2/">Careers</a>
                    <button class="mobile-submenu-toggle" aria-label="Toggle careers submenu">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M1.5 4L6 8L10.5 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <ul class="mobile-submenu">
                    <li><a href="<?php echo $base_url; ?>/internship-program/">Internship Program</a></li>
                    <li><a href="<?php echo $base_url; ?>/interview-guide/">Interview Guide</a></li>
                    <li><a href="<?php echo $base_url; ?>/submit-job-application/">Submit Job Application</a></li>
                </ul>
            </li>
            <li class="<?php echo ($active_nav === 'news') ? 'active' : ''; ?>">
                <a href="<?php echo $base_url; ?>/news-insights/">News &amp; Insights</a>
            </li>
        </ul>
    </div>
</div>
