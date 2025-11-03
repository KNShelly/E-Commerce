<?php
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../includes/db_functions.php';

if (!$user) {
  echo '<section class="container"><h2>Checkout</h2><p>Please <a href="index.php?page=login">login</a> to proceed to checkout.</p></section>';
  return;
}

$user_id = (int)$user['id'];
$cart = get_user_cart($user_id);
if (!$cart || empty($cart['items'])) {
  echo '<section class="container"><h2>Checkout</h2><p>Your cart is empty. Add items before checking out.</p></section>';
  return;
}

// Handle order placement
$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['place_order'])) {
  $selected_address_id = (int)($_POST['address_id'] ?? 0);
  $new_label = trim($_POST['label'] ?? '');
  $new_line1 = trim($_POST['line1'] ?? '');
  $new_line2 = trim($_POST['line2'] ?? '');
  $new_city = trim($_POST['city'] ?? '');
  $new_state = trim($_POST['state'] ?? '');
  $new_postal = trim($_POST['postal_code'] ?? '');
  $new_country = trim($_POST['country'] ?? '');
  $make_default = isset($_POST['is_default']) ? 1 : 0;

  if ($selected_address_id <= 0) {
    // Require minimal fields for new address
    if ($new_line1 === '' || $new_city === '' || $new_state === '' || $new_postal === '' || $new_country === '') {
      $error = 'Please provide a valid address or select an existing one.';
    } else {
      $addr_id = create_address($user_id, $new_label ?: 'shipping', $new_line1, $new_line2, $new_city, $new_state, $new_postal, $new_country, $make_default);
      if ($addr_id) {
        $selected_address_id = (int)$addr_id;
      } else {
        $error = 'Failed to save address. Please try again.';
      }
    }
  }

  if (!$error && $selected_address_id > 0) {
    $order_id = create_order_from_cart($user_id, $selected_address_id);
    if ($order_id) {
      $_SESSION['flash'] = 'Order placed successfully!';
      header('Location: index.php?page=home');
      exit;
    } else {
      $error = 'Failed to place order. Please try again.';
    }
  }
}

$addresses = get_user_addresses($user_id);
$totals = calculate_cart_totals($cart);
?>
<section class="container">
  <h2>Checkout</h2>
  <?php if ($error): ?>
    <div class="alert" style="margin:.75rem 0;padding:.5rem;border-radius:6px;background:#1f2937;color:#fca5a5;border:1px solid #ef4444;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>
  <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr;align-items:start;">
    <form method="post" action="index.php?page=checkout" style="display:grid;gap:.75rem;">
      <h3>Shipping Address</h3>
      <?php if (!empty($addresses)): ?>
        <div class="card" style="padding:.75rem;display:grid;gap:.5rem;">
          <?php foreach ($addresses as $addr): ?>
            <label style="display:grid;gap:.25rem;">
              <input type="radio" name="address_id" value="<?php echo (int)$addr['id']; ?>" />
              <div><strong><?php echo htmlspecialchars($addr['label'] ?: ''); ?></strong> <?php if ((int)$addr['is_default'] === 1) echo '(default)'; ?></div>
              <div><?php echo htmlspecialchars($addr['line1']); ?> <?php echo htmlspecialchars($addr['line2'] ?: ''); ?></div>
              <div><?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> <?php echo htmlspecialchars($addr['postal_code']); ?></div>
              <div><?php echo htmlspecialchars($addr['country']); ?></div>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <h4>Or add a new address</h4>
      <label>
        Label
        <input type="text" name="label" placeholder="home / work" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
      </label>
      <label>
        Address line 1
        <input type="text" name="line1" placeholder="123 Main St" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
      </label>
      <label>
        Address line 2
        <input type="text" name="line2" placeholder="Apt 4B" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
      </label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
        <label>
          City
          <input type="text" name="city" placeholder="City" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
        </label>
        <label>
          State
          <input type="text" name="state" placeholder="State" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
        </label>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
        <label>
          Postal code
          <input type="text" name="postal_code" placeholder="ZIP / Postal" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
        </label>
        <label>
          Country
          <input type="text" name="country" placeholder="Country" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
        </label>
      </div>
      <label style="display:flex;gap:.5rem;align-items:center;">
        <input type="checkbox" name="is_default" /> Make this my default address
      </label>
      <button class="btn" type="submit" name="place_order" value="1">Place Order</button>
    </form>

    <div style="display:grid;gap:.75rem;">
      <h3>Order Summary</h3>
      <div class="card" style="padding:.75rem;display:grid;gap:.5rem;">
        <?php foreach ($cart['items'] as $item): ?>
          <div style="display:flex;justify-content:space-between;">
            <div><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo (int)$item['quantity']; ?></div>
            <div>$<?php echo number_format((float)$item['subtotal'], 2); ?></div>
          </div>
        <?php endforeach; ?>
        <hr style="border-color:#1f2937;" />
        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>$<?php echo number_format($totals['subtotal'], 2); ?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Tax</span><span>$<?php echo number_format($totals['tax'], 2); ?></span></div>
        <div style="display:flex;justify-content:space-between;"><span>Shipping</span><span>$<?php echo number_format($totals['shipping'], 2); ?></span></div>
        <div style="display:flex;justify-content:space-between;font-weight:bold;"><span>Total</span><span>$<?php echo number_format($totals['total'], 2); ?></span></div>
      </div>
      <p style="opacity:.8;">Tax, shipping, and free shipping threshold use values from Settings.</p>
    </div>
  </div>
</section>