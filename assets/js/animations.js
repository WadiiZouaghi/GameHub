/**
 * Advanced Animations for GameHub
 * - Page transitions
 * - Parallax scrolling
 * - 3D hover tilt effect
 * - Confetti celebration
 */

(function() {
    'use strict';

    // ==========================================
    // PAGE TRANSITIONS
    // ==========================================
    function initPageTransitions() {
        // Safety: ensure body is always visible after 500ms
        let transitionStarted = false;
        
        // Set initial state
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.3s ease-in-out';
        
        // Fade in on load
        function fadeIn() {
            if (!transitionStarted) {
                transitionStarted = true;
                document.body.style.opacity = '1';
            }
        }
        
        // Multiple triggers to ensure fade-in happens
        window.addEventListener('load', () => {
            setTimeout(fadeIn, 50);
        });
        
        // Fallback: force fade-in after 500ms regardless
        setTimeout(() => {
            if (document.body.style.opacity !== '1') {
                document.body.style.opacity = '1';
            }
        }, 500);
        
        // If already loaded, fade in immediately
        if (document.readyState === 'complete') {
            setTimeout(fadeIn, 50);
        }

        // Fade out on navigation
        const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([data-no-transition])');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && !href.startsWith('javascript:') && !link.hasAttribute('download')) {
                    e.preventDefault();
                    document.body.style.opacity = '0';
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                }
            });
        });
    }

    // ==========================================
    // PARALLAX SCROLLING
    // ==========================================
    function initParallax() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        if (parallaxElements.length === 0) return;

        let ticking = false;

        function updateParallax() {
            try {
                const scrolled = window.pageYOffset;
                
                parallaxElements.forEach(element => {
                    const speed = parseFloat(element.getAttribute('data-parallax')) || 0.5;
                    const yPos = -(scrolled * speed);
                    element.style.transform = `translate3d(0, ${yPos}px, 0)`;
                });
            } catch (error) {
                console.warn('Parallax error:', error);
            }

            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });

        // Initial update
        setTimeout(updateParallax, 100);
    }

    // ==========================================
    // 3D HOVER TILT EFFECT
    // ==========================================
    function init3DTilt() {
        const tiltCards = document.querySelectorAll('[data-tilt]');
        
        tiltCards.forEach(card => {
            try {
                const intensity = parseFloat(card.getAttribute('data-tilt')) || 10;
                
                card.addEventListener('mousemove', (e) => {
                    try {
                        const rect = card.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        
                        const centerX = rect.width / 2;
                        const centerY = rect.height / 2;
                        
                        const rotateX = ((y - centerY) / centerY) * intensity;
                        const rotateY = ((centerX - x) / centerX) * intensity;
                        
                        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`;
                        card.style.transition = 'transform 0.1s ease-out';
                    } catch (error) {
                        console.warn('Tilt mousemove error:', error);
                    }
                });
                
                card.addEventListener('mouseleave', () => {
                    try {
                        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
                        card.style.transition = 'transform 0.3s ease-out';
                    } catch (error) {
                        console.warn('Tilt mouseleave error:', error);
                    }
                });
            } catch (error) {
                console.warn('Tilt initialization error:', error);
            }
        });
    }

    // ==========================================
    // CONFETTI ANIMATION
    // ==========================================
    function createConfetti(x, y) {
        const colors = ['#00a8e8', '#b026ff', '#ff006e', '#ffbe0b', '#8338ec'];
        const confettiCount = 50;
        const container = document.createElement('div');
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 999999;
        `;
        document.body.appendChild(container);

        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.random() * 10 + 5;
            const speedX = (Math.random() - 0.5) * 6;
            const speedY = Math.random() * -15 - 5;
            const rotation = Math.random() * 360;
            const rotationSpeed = (Math.random() - 0.5) * 10;

            confetti.style.cssText = `
                position: absolute;
                left: ${x}px;
                top: ${y}px;
                width: ${size}px;
                height: ${size}px;
                background: ${color};
                border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
                transform: rotate(${rotation}deg);
                opacity: 1;
                pointer-events: none;
            `;

            container.appendChild(confetti);

            animateConfetti(confetti, speedX, speedY, rotationSpeed);
        }

        setTimeout(() => {
            container.remove();
        }, 3000);
    }

    function animateConfetti(element, vx, vy, rotationSpeed) {
        let x = parseFloat(element.style.left);
        let y = parseFloat(element.style.top);
        let rotation = 0;
        let opacity = 1;
        let gravity = 0.5;
        let frameCount = 0;

        function update() {
            frameCount++;
            vy += gravity;
            x += vx;
            y += vy;
            rotation += rotationSpeed;
            
            if (frameCount > 60) {
                opacity -= 0.02;
            }

            element.style.left = x + 'px';
            element.style.top = y + 'px';
            element.style.transform = `rotate(${rotation}deg)`;
            element.style.opacity = opacity;

            if (opacity > 0 && y < window.innerHeight + 50) {
                requestAnimationFrame(update);
            }
        }

        update();
    }

    // Trigger confetti on purchase success
    function initConfettiTriggers() {
        // Check if on purchase success page
        const successMessage = document.querySelector('[data-confetti-trigger]');
        if (successMessage) {
            setTimeout(() => {
                createConfetti(window.innerWidth / 2, window.innerHeight / 2);
            }, 300);
        }

        // Add to any element with data-confetti attribute
        const confettiButtons = document.querySelectorAll('[data-confetti]');
        confettiButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const rect = button.getBoundingClientRect();
                createConfetti(rect.left + rect.width / 2, rect.top + rect.height / 2);
            });
        });
    }

    // ==========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ==========================================
    function initSmoothScroll() {
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        
        anchorLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // ==========================================
    // SCROLL REVEAL ANIMATIONS
    // ==========================================
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('[data-reveal]');
        
        if (revealElements.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(element);
        });

        // Add revealed class styles
        const style = document.createElement('style');
        style.textContent = `
            [data-reveal].revealed {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    }

    // ==========================================
    // INITIALIZE ALL ANIMATIONS
    // ==========================================
    function init() {
        try {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    try {
                        initPageTransitions();
                        initParallax();
                        init3DTilt();
                        initConfettiTriggers();
                        initSmoothScroll();
                        initScrollReveal();
                    } catch (error) {
                        console.error('Animation initialization error:', error);
                        // Ensure page is visible even if animations fail
                        document.body.style.opacity = '1';
                    }
                });
            } else {
                initPageTransitions();
                initParallax();
                init3DTilt();
                initConfettiTriggers();
                initSmoothScroll();
                initScrollReveal();
            }
        } catch (error) {
            console.error('Animation init error:', error);
            // Ensure page is visible even if animations fail
            document.body.style.opacity = '1';
        }
    }

    init();

    // Expose confetti function globally
    window.GameHubAnimations = {
        confetti: createConfetti
    };

})();
