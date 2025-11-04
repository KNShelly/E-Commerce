<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';
$items = db_has_connection() ? get_products(12) : [];
?>

<section class="container py-4">
  <!-- Hero (template-style) -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="main-swiper swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <div class="p-4 rounded-3" style="background:#0b1220;border:1px solid #1f2937;">
              <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                <img src="https://via.placeholder.com/600x300?text=Tech+Deals" class="img-fluid rounded" alt="Tech"
                     onerror="this.src='assets/images/placeholder1.jpg'">
                <div>
                  <h2 class="display-6">Tech Essentials</h2>
                  <p class="text-muted mb-3">Laptops, phones, audio, and accessories.</p>
                  <a href="index.php?page=product" class="btn btn-dark">Shop Tech</a>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="p-4 rounded-3" style="background:#0b1220;border:1px solid #1f2937;">
              <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                <img src="https://via.placeholder.com/600x300?text=Home+Goods" class="img-fluid rounded" alt="Home"
                     onerror="this.src='assets/images/placeholder2.jpg'">
                <div>
                  <h2 class="display-6">Home & Living</h2>
                  <p class="text-muted mb-3">Smart home, kitchenware, decor, and more.</p>
                  <a href="index.php?page=product" class="btn btn-dark">Shop Home</a>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="p-4 rounded-3" style="background:#0b1220;border:1px solid #1f2937;">
              <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                <img src="https://via.placeholder.com/600x300?text=Fashion+Finds" class="img-fluid rounded" alt="Fashion"
                     onerror="this.src='assets/images/placeholder3.jpg'">
                <div>
                  <h2 class="display-6">Fashion & Style</h2>
                  <p class="text-muted mb-3">Apparel, footwear, accessories for everyone.</p>
                  <a href="index.php?page=product" class="btn btn-dark">Shop Fashion</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>

  <!-- Product grid -->
  <div class="row g-4">
    <?php if ($items): ?>
      <?php foreach ($items as $p): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100">
            <?php if (!empty($p['image_path'])): ?>
              <img class="card-img-top" src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
                   onerror="this.src='https://via.placeholder.com/600x400?text=Product'">
            <?php else: ?>
              <img class="card-img-top" src="https://via.placeholder.com/600x400?text=Product" alt="Product">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title mb-1"><?php echo htmlspecialchars($p['name']); ?></h5>
              <?php $display = $p['sale_price'] ?? $p['price']; ?>
              <p class="card-text text-muted">$<?php echo number_format((float)$display, 2); ?></p>
              <a class="btn btn-outline-dark mt-auto" href="index.php?page=product&id=<?php echo (int)$p['id']; ?>">View</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <img class="card-img-top" src="https://via.placeholder.com/600x400?text=Product" alt="Product">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">Sample Product A</h5>
            <p class="card-text text-muted">$29.99</p>
            <a class="btn btn-outline-dark mt-auto" href="index.php?page=product&id=1">View</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <img class="card-img-top" src="https://via.placeholder.com/600x400?text=Product" alt="Product">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">Sample Product B</h5>
            <p class="card-text text-muted">$49.99</p>
            <a class="btn btn-outline-dark mt-auto" href="index.php?page=product&id=2">View</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
          <img class="card-img-top" src="https://via.placeholder.com/600x400?text=Product" alt="Product">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">Sample Product C</h5>
            <p class="card-text text-muted">$19.99</p>
            <a class="btn btn-outline-dark mt-auto" href="index.php?page=product&id=3">View</a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>