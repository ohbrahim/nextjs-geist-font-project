/**
 * Main JavaScript - Personal Website
 * PHP 8.0 Personal Website
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize Application
function initializeApp() {
    initializeNavigation();
    initializeForms();
    initializeAnimations();
    initializeLazyLoading();
    initializeScrollEffects();
}

// Navigation Functions
function initializeNavigation() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Form Handling
function initializeForms() {
    // Contact form
    const contactForm = document.querySelector('#contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }
    
    // Newsletter form
    const newsletterForm = document.querySelector('#newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterForm);
    }
    
    // Poll voting
    const pollForm = document.querySelector('#poll-form');
    if (pollForm) {
        pollForm.addEventListener('submit', handlePollVote);
    }
}

// Contact Form Handler
function handleContactForm(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'جاري الإرسال...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('تم إرسال رسالتك بنجاح!', 'success');
            form.reset();
        } else {
            showMessage(data.message || 'حدث خطأ أثناء الإرسال', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('حدث خطأ في الاتصال بالخادم', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'إرسال';
    });
}

// Newsletter Form Handler
function handleNewsletterForm(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('/personal-website/ajax/newsletter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('تم الاشتراك بنجاح!', 'success');
            form.reset();
        } else {
            showMessage(data.message || 'حدث خطأ', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('حدث خطأ في الاتصال', 'error');
    });
}

// Poll Vote Handler
function handlePollVote(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('/personal-website/ajax/vote.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('تم التصويت بنجاح!', 'success');
            document.getElementById('poll-form').style.display = 'none';
            document.getElementById('poll-results').style.display = 'block';
            document.getElementById('poll-results').innerHTML = data.results;
        } else {
            showMessage(data.message || 'حدث خطأ في التصويت', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('حدث خطأ في الاتصال', 'error');
    });
}

// Show Message Function
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insert at top of page
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Animation Functions
function initializeAnimations() {
    // Fade in elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// Lazy Loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Scroll Effects
function initializeScrollEffects() {
    // Back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '↑';
    backToTopBtn.className = 'back-to-top';
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1000;
    `;
    
    document.body.appendChild(backToTopBtn);
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.opacity = '1';
        } else {
            backToTopBtn.style.opacity = '0';
        }
    });
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.querySelector('#search-input');
    const searchResults = document.querySelector('#search-results');
    
    if (searchInput && searchResults) {
        const debouncedSearch = debounce((query) => {
            if (query.length > 2) {
                fetch(`/personal-website/ajax/search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        displaySearchResults(data);
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                    });
            } else {
                searchResults.innerHTML = '';
            }
        }, 300);
        
        searchInput.addEventListener('input', (e) => {
            debouncedSearch(e.target.value);
        });
    }
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('#search-results');
    if (!searchResults) return;
    
    if (results.length === 0) {
        searchResults.innerHTML = '<p>لا توجد نتائج</p>';
        return;
    }
    
    const html = results.map(item => `
        <div class="search-result">
            <a href="${item.url}">${item.title}</a>
            <p>${item.description}</p>
        </div>
    `).join('');
    
    searchResults.innerHTML = html;
}

// Image Gallery
function initializeGallery() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            openLightbox(this);
        });
    });
}

function openLightbox(element) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img src="${element.src}" alt="${element.alt}">
        </div>
    `;
    
    document.body.appendChild(lightbox);
    
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.className === 'lightbox-close') {
            lightbox.remove();
        }
    });
}

// Counter Animation
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        element.textContent = Math.floor(start);
        
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, 16);
}

// Initialize counters when visible
function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target);
                animateCounter(entry.target, target);
                counterObserver.unobserve(entry.target);
            }
        });
    });
    
    counters.forEach(counter => counterObserver.observe(counter));
}

// Cookie Consent
function initializeCookieConsent() {
    if (!localStorage.getItem('cookieConsent')) {
        const consentDiv = document.createElement('div');
        consentDiv.className = 'cookie-consent';
        consentDiv.innerHTML = `
            <div class="cookie-content">
                <p>نستخدم ملفات تعريف الارتباط لتحسين تجربتك</p>
                <button class="btn-cookie-accept">موافق</button>
            </div>
        `;
        
        document.body.appendChild(consentDiv);
        
        consentDiv.querySelector('.btn-cookie-accept').addEventListener('click', () => {
            localStorage.setItem('cookieConsent', 'true');
            consentDiv.remove();
        });
    }
}

// Dark Mode Toggle
function initializeDarkMode() {
    const darkModeToggle = document.querySelector('#dark-mode-toggle');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
        
        // Load saved preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
}

// Initialize all functions
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    initializeSearch();
    initializeGallery();
    initializeCounters();
    initializeCookieConsent();
    initializeDarkMode();
});

// Export functions for global use
window.PersonalWebsite = {
    showMessage,
    debounce,
    throttle,
    animateCounter,
    openLightbox
};
