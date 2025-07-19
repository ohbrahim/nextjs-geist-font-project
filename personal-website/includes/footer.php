</div>
                
                <!-- Sidebar -->
                <aside class="sidebar">
                    <!-- Poll Widget -->
                    <?php if ($activePoll): ?>
                    <div class="poll-widget">
                        <h3><?php echo htmlspecialchars($activePoll['question']); ?></h3>
                        <form id="pollForm" method="post" action="/personal-website/ajax/vote.php">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="poll_id" value="<?php echo $activePoll['id']; ?>">
                            
                            <?php foreach ($activePoll['options'] as $option): ?>
                                <div class="poll-option">
                                    <label>
                                        <input type="<?php echo $activePoll['allow_multiple'] ? 'checkbox' : 'radio'; ?>" 
                                               name="<?php echo $activePoll['allow_multiple'] ? 'options[]' : 'option_id'; ?>" 
                                               value="<?php echo $option['id']; ?>">
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="submit" class="poll-submit">تصويت</button>
                        </form>
                        
                        <div id="pollResults" style="display: none;">
                            <h4>نتائج التصويت:</h4>
                            <div id="resultsContainer"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Recent Portfolio -->
                    <?php 
                    $recentPortfolio = getActivePortfolio(3);
                    if (!empty($recentPortfolio)): 
                    ?>
                    <div class="widget">
                        <h3>أحدث الأعمال</h3>
                        <div class="recent-portfolio">
                            <?php foreach ($recentPortfolio as $item): ?>
                                <div class="portfolio-item">
                                    <?php if ($item['image']): ?>
                                        <img src="/personal-website/uploads/<?php echo $item['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php endif; ?>
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                        <p><?php echo truncateText($item['description'], 80); ?></p>
                                        <?php if ($item['project_url']): ?>
                                            <a href="<?php echo $item['project_url']; ?>" target="_blank">عرض المشروع</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter Subscription -->
                    <div class="widget newsletter-widget">
                        <h3>اشترك في النشرة البريدية</h3>
                        <p>احصل على آخر الأخبار والتحديثات</p>
                        <form id="newsletterForm" method="post" action="/personal-website/ajax/newsletter.php">
                            <?php echo csrfField(); ?>
                            <div class="form-group">
                                <input type="text" name="name" placeholder="الاسم" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                            </div>
                            <button type="submit" class="btn-subscribe">اشتراك</button>
                        </form>
                    </div>
                    
                    <!-- Social Links -->
                    <?php 
                    $socialLinks = [
                        'facebook' => getSiteSetting('social_facebook'),
                        'twitter' => getSiteSetting('social_twitter'),
                        'instagram' => getSiteSetting('social_instagram'),
                        'linkedin' => getSiteSetting('social_linkedin')
                    ];
                    $hasSocialLinks = array_filter($socialLinks);
                    ?>
                    
                    <?php if (!empty($hasSocialLinks)): ?>
                    <div class="widget social-widget">
                        <h3>تابعنا على</h3>
                        <div class="social-links">
                            <?php if ($socialLinks['facebook']): ?>
                                <a href="<?php echo $socialLinks['facebook']; ?>" target="_blank" class="social-link facebook">
                                    فيسبوك
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($socialLinks['twitter']): ?>
                                <a href="<?php echo $socialLinks['twitter']; ?>" target="_blank" class="social-link twitter">
                                    تويتر
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($socialLinks['instagram']): ?>
                                <a href="<?php echo $socialLinks['instagram']; ?>" target="_blank" class="social-link instagram">
                                    انستغرام
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($socialLinks['linkedin']): ?>
                                <a href="<?php echo $socialLinks['linkedin']; ?>" target="_blank" class="social-link linkedin">
                                    لينكد إن
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo $siteName; ?></h4>
                    <p><?php echo $siteDescription; ?></p>
                </div>
                
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <?php foreach ($menuPages as $page): ?>
                            <li>
                                <a href="/personal-website/<?php echo $page['slug'] == 'home' ? '' : 'pages/' . $page['slug'] . '.php'; ?>">
                                    <?php echo $page['title']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>معلومات التواصل</h4>
                    <div class="contact-info">
                        <?php if (getSiteSetting('contact_email')): ?>
                            <p>📧 <?php echo getSiteSetting('contact_email'); ?></p>
                        <?php endif; ?>
                        
                        <?php if (getSiteSetting('contact_phone')): ?>
                            <p>📱 <?php echo getSiteSetting('contact_phone'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($hasSocialLinks)): ?>
                <div class="footer-section">
                    <h4>تابعنا</h4>
                    <div class="footer-social">
                        <?php foreach ($socialLinks as $platform => $url): ?>
                            <?php if ($url): ?>
                                <a href="<?php echo $url; ?>" target="_blank" class="social-link <?php echo $platform; ?>">
                                    <?php echo ucfirst($platform); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-bottom">
                <p><?php echo getSiteSetting('footer_text', 'جميع الحقوق محفوظة © ' . date('Y')); ?></p>
                <?php if (isset($visitorStats)): ?>
                    <div class="footer-stats">
                        <span>إجمالي الزوار: <?php echo number_format($visitorStats['total_visitors']); ?></span>
                        <span>|</span>
                        <span>المتواجدون الآن: <?php echo $visitorStats['online_users']; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="/personal-website/assets/js/main.js"></script>
    
    <!-- Additional JavaScript for specific pages -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Content Layout */
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-top: 20px;
        }
        
        .main-column {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        /* Widget Styles */
        .widget {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .widget h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.1rem;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
        }
        
        /* Recent Portfolio Widget */
        .portfolio-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .portfolio-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .portfolio-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .item-info h4 {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .item-info p {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .item-info a {
            font-size: 0.8rem;
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .item-info a:hover {
            text-decoration: underline;
        }
        
        /* Newsletter Widget */
        .newsletter-widget {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }
        
        .newsletter-widget h3 {
            color: white;
            border-bottom-color: rgba(255,255,255,0.3);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .btn-subscribe {
            width: 100%;
            background: white;
            color: var(--secondary-color);
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-subscribe:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
        }
        
        /* Social Widget */
        .social-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .social-link {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .social-link.facebook {
            background: #1877f2;
            color: white;
        }
        
        .social-link.twitter {
            background: #1da1f2;
            color: white;
        }
        
        .social-link.instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            color: white;
        }
        
        .social-link.linkedin {
            background: #0077b5;
            color: white;
        }
        
        .social-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Footer Styles */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-section h4 {
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .contact-info p {
            margin-bottom: 8px;
            color: #ccc;
        }
        
        .footer-social {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .footer-stats {
            display: flex;
            gap: 10px;
            font-size: 0.9rem;
            color: #ccc;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .content-wrapper {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .main-column {
                padding: 20px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .main-column {
                padding: 15px;
            }
            
            .widget {
                padding: 15px;
            }
        }
    </style>
    
    <script>
        // Poll voting functionality
        document.getElementById('pollForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/personal-website/ajax/vote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('pollForm').style.display = 'none';
                    document.getElementById('pollResults').style.display = 'block';
                    document.getElementById('resultsContainer').innerHTML = data.results;
                } else {
                    alert(data.message || 'حدث خطأ في التصويت');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في التصويت');
            });
        });
        
        // Newsletter subscription
        document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/personal-website/ajax/newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم الاشتراك بنجاح!');
                    this.reset();
                } else {
                    alert(data.message || 'حدث خطأ في الاشتراك');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاشتراك');
            });
        });
    </script>
</body>
</html>
