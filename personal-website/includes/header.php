<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

// Track visitor if enabled
if (getSiteSetting('visitor_counter_enabled', '1') == '1') {
    $visitorTracker = new VisitorTracker($db);
    $visitorTracker->track();
    $visitorStats = $visitorTracker->getComprehensiveStats();
}

// Get site settings
$siteName = getSiteSetting('site_name', 'موقعي الشخصي');
$siteDescription = getSiteSetting('site_description', 'موقع شخصي متكامل');
$headerLogo = getSiteSetting('header_logo', '');
$menuPages = getMenuPages();

// Get active banners
$adBanners = [];
$eventBanners = [];

if (getSiteSetting('banner_ads_enabled', '1') == '1') {
    $adBanners = getActiveBanners('advertisement');
}

if (getSiteSetting('banner_events_enabled', '1') == '1') {
    $eventBanners = getActiveBanners('daily_event');
}

// Get active poll
$activePoll = getActivePoll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : $siteDescription; ?>">
    <meta name="keywords" content="<?php echo isset($pageKeywords) ? $pageKeywords : getSiteSetting('site_keywords', ''); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : $siteDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName; ?>">
    <meta name="twitter:description" content="<?php echo isset($pageDescription) ? $pageDescription : $siteDescription; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/personal-website/assets/images/favicon.ico">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/personal-website/assets/css/style.css">
    <link rel="stylesheet" href="/personal-website/assets/css/responsive.css">
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #fff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }
        
        .logo img {
            height: 40px;
            margin-left: 10px;
        }
        
        .visitor-stats {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .main-nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
            justify-content: center;
        }
        
        .main-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        
        .main-nav a:hover,
        .main-nav a.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        /* Banner Styles */
        .banners-section {
            padding: 1rem 0;
        }
        
        .banner {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .banner.advertisement {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            border-color: #ffc107;
        }
        
        .banner.daily-event {
            background: linear-gradient(45deg, #d1ecf1, #bee5eb);
            border-color: #17a2b8;
        }
        
        .banner h3 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .banner img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .banner a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .banner a:hover {
            text-decoration: underline;
        }
        
        /* Poll Styles */
        .poll-widget {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .poll-widget h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .poll-option {
            margin-bottom: 10px;
        }
        
        .poll-option label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .poll-option label:hover {
            background-color: var(--light-bg);
        }
        
        .poll-option input {
            margin-left: 10px;
        }
        
        .poll-submit {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .poll-submit:hover {
            background: #2980b9;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 15px;
            }
            
            .visitor-stats {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .main-nav ul {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
        
        /* Success/Error Messages */
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <a href="/personal-website/" class="logo">
                    <?php if ($headerLogo): ?>
                        <img src="/personal-website/uploads/<?php echo $headerLogo; ?>" alt="<?php echo $siteName; ?>">
                    <?php endif; ?>
                    <?php echo $siteName; ?>
                </a>
                
                <?php if (isset($visitorStats)): ?>
                <div class="visitor-stats">
                    <div class="stat-item">
                        <span>🌐</span>
                        <span>المتواجدون: <?php echo $visitorStats['online_users']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>👥</span>
                        <span>إجمالي الزوار: <?php echo $visitorStats['total_visitors']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>📅</span>
                        <span>زوار اليوم: <?php echo $visitorStats['today_visitors']; ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <?php foreach ($menuPages as $page): ?>
                        <li>
                            <a href="/personal-website/<?php echo $page['slug'] == 'home' ? '' : 'pages/' . $page['slug'] . '.php'; ?>" 
                               class="<?php echo (isset($currentPage) && $currentPage == $page['slug']) ? 'active' : ''; ?>">
                                <?php echo $page['title']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Banners Section -->
    <?php if (!empty($adBanners) || !empty($eventBanners)): ?>
    <section class="banners-section">
        <div class="container">
            <?php foreach ($adBanners as $banner): ?>
                <div class="banner advertisement">
                    <h3><?php echo htmlspecialchars($banner['title']); ?></h3>
                    <?php if ($banner['image']): ?>
                        <img src="/personal-website/uploads/<?php echo $banner['image']; ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <?php endif; ?>
                    <div><?php echo $banner['content']; ?></div>
                    <?php if ($banner['link_url']): ?>
                        <a href="<?php echo $banner['link_url']; ?>" target="_blank">اقرأ المزيد</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php foreach ($eventBanners as $banner): ?>
                <div class="banner daily-event">
                    <h3><?php echo htmlspecialchars($banner['title']); ?></h3>
                    <?php if ($banner['image']): ?>
                        <img src="/personal-website/uploads/<?php echo $banner['image']; ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <?php endif; ?>
                    <div><?php echo $banner['content']; ?></div>
                    <?php if ($banner['link_url']): ?>
                        <a href="<?php echo $banner['link_url']; ?>" target="_blank">التفاصيل</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <main class="main-content">
        <div class="container">
            <!-- Success/Error Messages -->
            <?php 
            $successMessage = getSuccessMessage();
            $errorMessage = getErrorMessage();
            ?>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="content-wrapper">
                <div class="main-column">
