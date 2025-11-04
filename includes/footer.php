<?php
  // Compute base path so assets resolve from /admin and root
  if (!isset($base)) {
    $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $base = $in_admin ? '..' : '.';
  }
?>
<footer class="container mt-5 py-4">
  <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> E‑Commerce</p>
  <small class="text-muted">All categories · Great prices</small>
</footer>

<!-- Vendor scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<!-- App script -->
<script src="<?php echo $base; ?>/assets/js/main.js"></script>
</body>
</html>