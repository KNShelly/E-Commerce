<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';
$items = db_has_connection() ? get_products(9) : [];
?>
<section class="container">
  <div class="hero">
    <h1>Welcome to the Store</h1>
    <p>Discover great products at amazing prices.</p>
  </div>

  <div class="grid">
    <?php if ($items): ?>
      <?php foreach ($items as $p): ?>
        <article class="card">
          <?php if (!empty($p['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" />
          <?php else: ?>
            <img src="assets/images/placeholder1.jpg" alt="Product" />
          <?php endif; ?>
          <div class="content">
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            <p class="price">$<?php echo number_format((float)$p['price'], 2); ?></p>
            <a class="btn" href="index.php?page=product&id=<?php echo (int)$p['id']; ?>">View</a>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <article class="card">
        <img src="assets/images/placeholder1.jpg" alt="Product" />
        <div class="content">
          <h3>Sample Product A</h3>
          <p class="price">$29.99</p>
          <a class="btn" href="index.php?page=product&id=1">View</a>
        </div>
      </article>
      <article class="card">
        <img src="assets/images/placeholder2.jpg" alt="Product" />
        <div class="content">
          <h3>Sample Product B</h3>
          <p class="price">$49.99</p>
          <a class="btn" href="index.php?page=product&id=2">View</a>
        </div>
      </article>
      <article class="card">
        <img src="assets/images/placeholder3.jpg" alt="Product" />
        <div class="content">
          <h3>Sample Product C</h3>
          <p class="price">$19.99</p>
          <a class="btn" href="index.php?page=product&id=3">View</a>
        </div>
      </article>
    <?php endif; ?>
  </div>
</section>