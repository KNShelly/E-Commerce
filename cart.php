<?php
$user = $_SESSION['user'] ?? null;
?>
<section class="container">
  <h2>Your Cart</h2>
  <?php if (!$user): ?>
    <p>Please <a href="index.php?page=login">login</a> to view your cart.</p>
  <?php else: ?>
    <?php
      require_once __DIR__ . '/../includes/db_functions.php';
      $cart = get_user_cart((int)$user['id']);
    ?>
    <?php if (!$cart || empty($cart['items'])): ?>
      <p>No items yet. Add products from the home or product page.</p>
    <?php else: ?>
      <div style="display:grid;gap:.75rem;max-width:800px;">
        <?php
          $total = 0.0;
          foreach ($cart['items'] as $item):
            $total += (float)$item['subtotal'];
        ?>
          <div class="card" style="display:flex;justify-content:space-between;align-items:center;padding:.75rem;">
            <div>
              <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
              <div style="opacity:.8;">Qty: <?php echo (int)$item['quantity']; ?> × $<?php echo number_format((float)$item['unit_price'], 2); ?></div>
            </div>
            <div>
              $<?php echo number_format((float)$item['subtotal'], 2); ?>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="card" style="padding:.75rem;display:flex;justify-content:space-between;">
          <strong>Total</strong>
          <strong>$<?php echo number_format($total, 2); ?></strong>
        </div>
      </div>
      <a class="btn" href="index.php?page=checkout" style="margin-top:.75rem;">Proceed to Checkout</a>
    <?php endif; ?>
  <?php endif; ?>
</section>