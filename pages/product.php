<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = ($id && db_has_connection()) ? get_product_by_id($id) : null;
$cart_error = $cart_error ?? null; // Provided by index.php when POST fails
?>
<section class="container">
  <h2>Product Details</h2>
  <?php if ($product): ?>
    <div class="card" style="max-width:600px;">
      <?php if (!empty($product['image_path'])): ?>
        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
      <?php else: ?>
        <img src="assets/images/placeholder1.jpg" alt="Product" />
      <?php endif; ?>
      <div class="content">
        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        <p><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
        <p class="price">$<?php echo number_format((float)($product['sale_price'] ?? $product['price']), 2); ?></p>
        <?php if ($cart_error): ?>
          <div class="alert" style="margin:.75rem 0;padding:.5rem;border-radius:6px;background:#1f2937;color:#fca5a5;border:1px solid #ef4444;">
            <?php echo htmlspecialchars($cart_error); ?>
          </div>
        <?php endif; ?>
        <form method="post" action="index.php?page=product&id=<?php echo (int)$id; ?>" style="display:flex;gap:.5rem;align-items:center;">
          <input type="hidden" name="product_id" value="<?php echo (int)$id; ?>" />
          <input type="number" name="quantity" min="1" value="1" style="width:80px;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
          <button class="btn" type="submit" name="add_to_cart" value="1">Add to Cart</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <?php if ($id): ?>
      <p>Product not found or database not configured.</p>
    <?php else: ?>
      <p>Browse our products. Select one from the home page.</p>
      <div class="card" style="max-width:600px;">
        <img src="assets/images/placeholder1.jpg" alt="Product" />
        <div class="content">
          <h3>Demo Product</h3>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
          <p class="price">$39.99</p>
          <a class="btn" href="index.php?page=cart">Add to Cart</a>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>