// Advanced Parallax Scrolling with Multiple Effects and Animations
(function() {
  'use strict';
  
  let ticking = false;
  const scrollElements = [];
  
  // Initialize parallax elements
  function initParallax() {
    const elements = document.querySelectorAll('.hero, .section, .hero-card, .doc-photo, .feature, .hero-copy, .doc-info');
    elements.forEach(el => {
      scrollElements.push({
        element: el,
        elementTop: el.getBoundingClientRect().top + window.scrollY,
        distance: 0,
        speed: parseFloat(el.getAttribute('data-speed')) || (el.classList.contains('hero') ? 0.5 : 0.3)
      });
    });
  }
  
  // Update parallax on scroll
  function updateParallax() {
    const scrollY = window.scrollY;
    
    scrollElements.forEach(item => {
      const element = item.element;
      const elementTop = item.elementTop;
      const elementHeight = element.offsetHeight;
      const viewportHeight = window.innerHeight;
      
      // Calculate distance from viewport center
      const distanceFromViewport = (elementTop + elementHeight / 2) - (scrollY + viewportHeight / 2);
      
      // Only animate if element is in or near viewport
      if (Math.abs(distanceFromViewport) < viewportHeight + elementHeight) {
        // Calculate parallax intensity based on element type
        let parallaxValue = 0;
        
        if (element.classList.contains('hero')) {
          parallaxValue = (scrollY - elementTop) * 0.4;
        } else if (element.classList.contains('hero-card')) {
          parallaxValue = (scrollY - elementTop) * 0.25;
        } else if (element.classList.contains('doc-photo')) {
          parallaxValue = (scrollY - elementTop) * 0.35;
        } else if (element.classList.contains('feature')) {
          parallaxValue = (scrollY - elementTop) * 0.2;
        } else {
          parallaxValue = (scrollY - elementTop) * 0.15;
        }
        
        // Apply smooth parallax transform
        element.style.transform = 'translateY(' + parallaxValue + 'px)';
        
        // Fade in effect as elements enter viewport
        const fadePercent = Math.min(Math.max((scrollY - elementTop + viewportHeight) / viewportHeight, 0), 1);
        if (fadePercent < 1) {
          element.style.opacity = Math.min(fadePercent * 1.2, 1);
        }
      }
    });
    
    ticking = false;
  }
  
  // Optimize scroll event with requestAnimationFrame
  function onScroll() {
    if (!ticking) {
      window.requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }
  
  // Mouse move parallax effect for hero card
  function initMouseParallax() {
    const heroCard = document.querySelector('.hero-card');
    if (!heroCard) return;
    
    document.addEventListener('mousemove', function(e) {
      const x = (e.clientX / window.innerWidth - 0.5) * 20;
      const y = (e.clientY / window.innerHeight - 0.5) * 20;
      heroCard.style.transform = 'translateX(' + x + 'px) translateY(' + y + 'px) perspective(1000px) rotateX(' + (-y * 0.05) + 'deg) rotateY(' + (x * 0.05) + 'deg)';
    });
  }
  
  // Initialize on page load
  window.addEventListener('load', function() {
    initParallax();
    initMouseParallax();
    updateParallax();
  });
  
  // Update on scroll with throttling
  window.addEventListener('scroll', onScroll, { passive: true });
  
  // Handle window resize
  window.addEventListener('resize', function() {
    scrollElements.length = 0;
    initParallax();
  });
})();
