<?php
/**
 * Homepage - Personal Website
 * PHP 8.0 Personal Website
 */

require_once 'config/config.php';
require_once 'classes/VisitorTracker.php';

// Set page variables
$currentPage = 'home';
$pageTitle = 'الصفحة الرئيسية';
$pageDescription = getSiteSetting('site_description', 'موقع شخصي متكامل');
$pageKeywords = getSiteSetting('site_keywords', '');

// Get homepage content
$homePage = getPageBySlug('home');
$homeContent = $homePage ? $homePage['content'] : '<h1>مرحباً بكم في موقعي الشخصي</h1><p>مرحباً بكم في موقعي الشخصي، حيث أقدم لكم مجموعة متنوعة من الخدمات والمنتجات المبتكرة. استكشف عالمي واكتشف ما يمكنني تقديمه لكم.</p>';

// Get featured services
$featuredServices = getActiveServices();

// Get featured portfolio items
$featuredPortfolio = getActivePortfolio(6);

include 'includes/header.php';
?>

<div class="homepage">
    <h1>مرحباً بكم في موقعي الشخصي</h1>
    <p>مرحباً بكم في موقعي الشخصي، حيث أقدم لكم مجموعة متنوعة من الخدمات والمنتجات المبتكرة. استكشف عالمي واكتشف ما يمكنني تقديمه لكم.</p>
    
    <h2>خدماتنا</h2>
    <div class="services">
        <?php foreach ($featuredServices as $service): ?>
            <div class="service">
                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <h2>أعمالي المنجزة</h2>
    <div class="portfolio">
        <?php foreach ($featuredPortfolio as $item): ?>
            <div class="portfolio-item">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
