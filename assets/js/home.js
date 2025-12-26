// Homepage-specific interactions extracted from pages/home.php inline <script>

// Sticky Header (uses jQuery if available, else vanilla)
(function() {
  function onScroll() {
    var scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 100;
    var header = document.querySelector('.header-area');
    if (!header) return;
    if (scrolled) header.classList.add('header-sticky');
    else header.classList.remove('header-sticky');
  }
  if (window.jQuery) {
    jQuery(window).on('scroll', onScroll);
  } else {
    window.addEventListener('scroll', onScroll);
  }
})();

// Smooth scrolling for anchor links
(function() {
  function handleClick(e) {
    var hash = this.hash || (this.getAttribute('href') || '').split('#')[1];
    if (!hash) return;
    var target = document.getElementById(hash) || document.querySelector('#' + hash);
    if (target) {
      e.preventDefault();
      var top = target.getBoundingClientRect().top + window.pageYOffset - 80;
      window.scrollTo({ top: top, behavior: 'smooth' });
    }
  }
  var links = document.querySelectorAll('a[href*="#"]');
  links.forEach(function(a) { a.addEventListener('click', handleClick); });
})();

// Add to cart functionality (demo)
(function() {
  var buttons = document.querySelectorAll('.add-to-cart');
  buttons.forEach(function(button) {
    button.addEventListener('click', function() {
      var productName = this.getAttribute('data-product-name') || 'Item';
      var originalText = this.innerHTML;
      this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
      this.disabled = true;
      setTimeout(function() {
        var badge = document.querySelector('.badge.bg-primary') || createCartBadge();
        var count = parseInt(badge.textContent || '0');
        badge.textContent = count + 1;
        showToast(productName + ' added to cart!');
        button.innerHTML = originalText;
        button.disabled = false;
      }, 500);
    });
  });
})();

function createCartBadge() {
  var badge = document.createElement('span');
  badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary';
  var cartLink = document.querySelector('a[href*="cart"]');
  if (cartLink) cartLink.appendChild(badge);
  return badge;
}

function showToast(message) {
  var toast = document.createElement('div');
  toast.className = 'position-fixed bottom-0 end-0 p-3';
  toast.style.zIndex = '9999';
  toast.innerHTML = (
    '<div class="toast show" role="alert">' +
      '<div class="toast-header bg-primary text-white">' +
        '<i class="fas fa-check-circle me-2"></i>' +
        '<strong class="me-auto">Success</strong>' +
        '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>' +
      '</div>' +
      '<div class="toast-body">' + message + '</div>' +
    '</div>'
  );
  document.body.appendChild(toast);
  setTimeout(function() { toast.remove(); }, 3000);
}

function validateEmail(email) {
  var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Newsletter form handling
(function() {
  var form = document.getElementById('newsletter-form');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var emailInput = form.querySelector('input[type="email"]');
    var button = form.querySelector('button');
    if (validateEmail((emailInput && emailInput.value) || '')) {
      var originalText = button.innerHTML;
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      button.disabled = true;
      setTimeout(function() {
        showToast('Thank you for subscribing to our newsletter!');
        if (emailInput) emailInput.value = '';
        button.innerHTML = originalText;
        button.disabled = false;
      }, 1000);
    } else {
      alert('Please enter a valid email address.');
      if (emailInput) emailInput.focus();
    }
  });
})();

// Loading indicator behavior
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function() {
      var href = this.getAttribute('href') || '';
      if (href && href.charAt(0) !== '#') {
        var loading = document.getElementById('loading-indicator');
        if (loading) loading.style.display = 'block';
      }
    });
  });
  window.addEventListener('load', function() {
    var loading = document.getElementById('loading-indicator');
    if (loading) loading.style.display = 'none';
  });
});