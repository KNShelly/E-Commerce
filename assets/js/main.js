// Minimal JS hook for future template interactions
document.addEventListener('DOMContentLoaded', () => {
  // Initialize Swiper if present
  if (window.Swiper) {
    // Example: attach to a hero swiper if available
    const mainEl = document.querySelector('.main-swiper');
    if (mainEl) {
      new Swiper(mainEl, {
        slidesPerView: 1,
        loop: true,
        speed: 500,
        pagination: { el: '.swiper-pagination', clickable: true },
        // Fix height without reflow; CSS ensures consistent slide height
        autoHeight: false,
        grabCursor: true,
      });
    }
    // Initialize hero swiper variant if present
    const heroEl = document.querySelector('.hero-swiper');
    if (heroEl) {
      new Swiper(heroEl, {
        loop: true,
        effect: 'fade',
        speed: 1000,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
      });
    }
  }
});