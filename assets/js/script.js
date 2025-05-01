document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');
    const menuOverlay = document.querySelector('.menu-overlay');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            this.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
    }
    
    // Close menu when clicking the overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', function() {
            nav.classList.remove('active');
            menuOverlay.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            document.body.classList.remove('menu-open');
        });
    }
    
    // Close menu when clicking a menu item
    const menuLinks = document.querySelectorAll('nav ul li a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            nav.classList.remove('active');
            menuOverlay.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            document.body.classList.remove('menu-open');
        });
    });
    
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    
    // Check for saved theme preference
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Toggle between dark and light
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Save the preference
            localStorage.setItem('theme', newTheme);
            
            // Apply the theme
            document.documentElement.setAttribute('data-theme', newTheme);
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Scroll animations
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
        
        // Check if stats section is in view to start counter animation
        const statsSection = document.querySelector('.about-stats');
        if (statsSection) {
            const statsSectionPosition = statsSection.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (statsSectionPosition < windowHeight - 100 && !statsSection.classList.contains('counted')) {
                statsSection.classList.add('counted');
                animateCounters();
            }
        }
    };
    
    // Animate number counters
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-value');
        const speed = 200; // Lower is faster
        
        counters.forEach(counter => {
            const targetValue = counter.innerText;
            let targetNumber = parseInt(targetValue.replace(/\D/g, ''));
            const suffix = targetValue.replace(/[0-9]/g, '');
            let count = 0;
            
            const updateCount = () => {
                const increment = targetNumber / speed;
                
                if (count < targetNumber) {
                    count += increment;
                    counter.innerText = Math.ceil(count) + suffix;
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = targetValue;
                }
            };
            
            updateCount();
        });
    }
    
    // Apply 'animate-on-scroll' class to elements that should animate
    document.querySelectorAll('.step, .craftsman-card, .post-card, .testimonial-card, .stat').forEach(el => {
        el.classList.add('animate-on-scroll');
    });
    
    // Run once on load
    setTimeout(animateOnScroll, 500);
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
    
    // Add active class to current menu item
    const currentLocation = window.location.pathname;
    const menuItems = document.querySelectorAll('nav ul li a');
    menuItems.forEach(item => {
        const itemPath = item.getAttribute('href');
        if (currentLocation.includes(itemPath) && itemPath !== 'index.php') {
            item.classList.add('active');
        } else if (currentLocation.endsWith('/') && itemPath === 'index.php') {
            item.classList.add('active');
        }
    });
}); 