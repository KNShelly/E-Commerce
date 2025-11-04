// Minimal JS hook for future template interactions
document.addEventListener('DOMContentLoaded', () => {
  // Initialize Swiper if present
  if (window.Swiper) {
    // Example: attach to a hero swiper if available
    const heroEl = document.querySelector('.main-swiper');
    if (heroEl) {
      new Swiper(heroEl, {
        slidesPerView: 1,
        loop: true,
        speed: 500,
        pagination: { el: '.swiper-pagination', clickable: true },
        // Fix height without reflow; CSS ensures consistent slide height
        autoHeight: false,
        grabCursor: true,
      });
    }
  }
});