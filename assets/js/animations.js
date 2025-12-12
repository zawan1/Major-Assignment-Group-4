/* ========================================
   SCROLL ANIMATIONS & PARALLAX ENGINE
   Medical SaaS Landing Page
   ======================================== */

class MedicalAnimationEngine {
  constructor() {
    this.elements = [];
    this.isAnimating = false;
    this.animationFrameId = null;
    this.init();
  }

  init() {
    this.collectElements();
    this.handleScroll = this.handleScroll.bind(this);
    this.setupParallax();
    window.addEventListener('scroll', this.handleScroll, { passive: true });
    window.addEventListener('resize', () => this.collectElements());
    
    // Initial check on page load
    setTimeout(() => this.triggerVisibleElements(), 100);
  }

  /* ========================================
     ELEMENT COLLECTION & VISIBILITY DETECTION
     ======================================== */

  collectElements() {
    // Collect all elements with scroll-reveal class
    this.elements = Array.from(document.querySelectorAll('.scroll-reveal')).map(el => ({
      element: el,
      triggerPoint: 0,
      hasAnimated: false,
      animationType: el.dataset.animation || 'slideUp'
    }));

    // Calculate trigger points
    this.elements.forEach(item => {
      const rect = item.element.getBoundingClientRect();
      const elementTop = rect.top + window.scrollY;
      item.triggerPoint = elementTop - (window.innerHeight * 0.75);
    });
  }

  isElementInView(element) {
    const rect = element.getBoundingClientRect();
    return rect.top < window.innerHeight && rect.bottom > 0;
  }

  /* ========================================
     SCROLL REVEAL ANIMATIONS
     ======================================== */

  handleScroll() {
    const scrollY = window.scrollY;

    this.elements.forEach(item => {
      if (!item.hasAnimated && scrollY > item.triggerPoint) {
        item.hasAnimated = true;
        this.triggerAnimation(item.element, item.animationType);
      }
    });

    // Update parallax on every scroll
    this.updateParallax();
  }

  triggerAnimation(element, type) {
    element.classList.remove('scroll-reveal');
    
    // Apply animation based on type
    switch(type) {
      case 'slideUp':
        element.style.animation = 'slideUp 0.6s ease-out forwards';
        break;
      case 'slideDown':
        element.style.animation = 'slideDown 0.6s ease-out forwards';
        break;
      case 'slideLeft':
        element.style.animation = 'slideLeft 0.6s ease-out forwards';
        break;
      case 'slideRight':
        element.style.animation = 'slideRight 0.6s ease-out forwards';
        break;
      case 'fadeIn':
        element.style.animation = 'fadeIn 0.6s ease-out forwards';
        break;
      case 'zoomIn':
        element.style.animation = 'zoomIn 0.6s ease-out forwards';
        break;
      default:
        element.style.animation = 'slideUp 0.6s ease-out forwards';
    }
  }

  triggerVisibleElements() {
    this.elements.forEach(item => {
      if (this.isElementInView(item.element)) {
        item.hasAnimated = true;
        this.triggerAnimation(item.element, item.animationType);
      }
    });
  }

  /* ========================================
     PARALLAX SCROLLING ENGINE
     ======================================== */

  setupParallax() {
    this.parallaxElements = Array.from(document.querySelectorAll('[data-parallax]')).map(el => ({
      element: el,
      speed: parseFloat(el.dataset.parallax) || 0.5,
      startY: 0,
      offset: 0
    }));

    // Set initial positions
    this.parallaxElements.forEach(item => {
      const rect = item.element.getBoundingClientRect();
      item.startY = rect.top + window.scrollY;
      item.element.style.transform = 'translateY(0px)';
    });
  }

  updateParallax() {
    const scrollY = window.scrollY;

    this.parallaxElements.forEach(item => {
      const distance = scrollY - item.startY;
      const movement = distance * item.speed;
      item.element.style.transform = `translateY(${movement}px)`;
    });
  }

  /* ========================================
     CLEANUP
     ======================================== */

  destroy() {
    window.removeEventListener('scroll', this.handleScroll);
    if (this.animationFrameId) {
      cancelAnimationFrame(this.animationFrameId);
    }
  }
}

/* ========================================
   INITIALIZE ON DOM READY
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {
  window.animationEngine = new MedicalAnimationEngine();
});

/* ========================================
   HELPER: Add data-animation to elements
   
   Usage in HTML:
   <div class="scroll-reveal" data-animation="slideUp">Content</div>
   <div class="scroll-reveal" data-animation="fadeIn">Content</div>
   
   Parallax Usage:
   <div data-parallax="0.5">Content</div>
   ======================================== */
