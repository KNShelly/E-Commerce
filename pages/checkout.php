<?php
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../includes/db_functions.php';

if (!$user) {
  echo '
  <section class="container py-5">
    <div class="text-center py-5">
      <div class="mb-4">
        <i class="fas fa-lock fa-4x text-muted"></i>
      </div>
      <h2 class="fw-bold mb-3">Checkout</h2>
      <p class="text-muted mb-4">Please login to proceed to checkout</p>
      <a href="index.php?page=login" class="btn btn-primary btn-lg">
        <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
      </a>
    </div>
  </section>';
  return;
}

$user_id = (int)$user['id'];
$cart = get_user_cart($user_id);
if (!$cart || empty($cart['items'])) {
  echo '
  <section class="container py-5">
    <div class="text-center py-5">
      <div class="mb-4">
        <i class="fas fa-shopping-cart fa-4x text-muted"></i>
      </div>
      <h2 class="fw-bold mb-3">Checkout</h2>
      <p class="text-muted mb-4">Your cart is empty. Add items before checking out.</p>
      <a href="index.php?page=home" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
      </a>
    </div>
  </section>';
  return;
}

// Error placeholder (set by router when order placement fails)
$error = $_SESSION['checkout_error'] ?? null;
unset($_SESSION['checkout_error']);

$addresses = get_user_addresses($user_id);
$delivery_distance_km = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $delivery_distance_km = isset($_POST['delivery_distance_km']) && $_POST['delivery_distance_km'] !== ''
    ? (float)$_POST['delivery_distance_km']
    : null;
}
$totals = calculate_cart_totals($cart, $delivery_distance_km);
?>

<section class="container py-5">
    <!-- Continue Shopping Link -->
    <a href="index.php?page=cart" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Cart
    </a>

    <!-- Checkout Header -->
    <div class="checkout-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-3"><i class="fas fa-shopping-bag me-3"></i>Checkout</h1>
                <p class="mb-0">Complete your purchase with secure checkout</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="item-count">
                    <?php echo count($cart['items']); ?> item<?php echo count($cart['items']) !== 1 ? 's' : ''; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-elegant" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Shipping Address Form -->
        <div class="col-lg-8">
            <form method="post" action="index.php?page=checkout">
                <div class="checkout-card">
                    <div class="card-header">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-truck me-2"></i>
                            Delivery Address
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($addresses)): ?>
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">Select Existing Address</h5>
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="address-card" onclick="selectAddress(<?php echo (int)$addr['id']; ?>)">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="address_id" 
                                                   id="address_<?php echo (int)$addr['id']; ?>" 
                                                   value="<?php echo (int)$addr['id']; ?>">
                                            <label class="form-check-label w-100" for="address_<?php echo (int)$addr['id']; ?>">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <strong class="d-block"><?php echo htmlspecialchars($addr['label'] ?: 'Address'); ?></strong>
                                                        <?php if ((int)$addr['is_default'] === 1): ?>
                                                            <span class="badge">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-end">
                                                        <small><?php echo htmlspecialchars($addr['country']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="address-details">
                                                    <div><?php echo htmlspecialchars($addr['line1']); ?> <?php echo htmlspecialchars($addr['line2'] ?: ''); ?></div>
                                                    <div><?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> <?php echo htmlspecialchars($addr['postal_code']); ?></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="divider">
                                <span>OR</span>
                            </div>
                        <?php endif; ?>

                        <!-- New Address Form -->
                        <div>
                            <h5 class="fw-semibold mb-3">
                                <i class="fas fa-plus-circle me-2"></i>
                                Add New Address
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Label</label>
                                    <input type="text" name="label" class="form-control" placeholder="Home, Work, etc.">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address Line 1 *</label>
                                    <input type="text" name="line1" class="form-control" placeholder="123 Main St" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" name="line2" class="form-control" placeholder="Apt 4B, Suite 500, etc.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">City *</label>
                                    <input type="text" name="city" class="form-control" placeholder="City" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">State *</label>
                                    <input type="text" name="state" class="form-control" placeholder="State" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Postal Code *</label>
                                    <input type="text" name="postal_code" class="form-control" placeholder="ZIP / Postal" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Country *</label>
                                    <input type="text" name="country" class="form-control" placeholder="Country" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Delivery Distance (km)</label>
                                    <input type="number" name="delivery_distance_km" class="form-control" placeholder="Distance in km" min="0" step="0.1" value="<?php echo isset($delivery_distance_km) && $delivery_distance_km !== null ? htmlspecialchars($delivery_distance_km) : '';?>">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                                        <label class="form-check-label" for="is_default">
                                            Make this my default delivery address
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card">
                    <div class="card-header">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Payment Method
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_mpesa" value="mpesa" checked>
                                <label class="form-check-label" for="pm_mpesa">
                                    M-Pesa (KES)
                                </label>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">M-Pesa Phone</label>
                                    <input type="tel" name="mpesa_phone" class="form-control" placeholder="e.g. 07xxxxxxxx">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">You will receive a prompt to complete payment (demo stub).</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_paypal" value="paypal">
                                <label class="form-check-label" for="pm_paypal">
                                    PayPal
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">You will be redirected to PayPal to pay (demo stub).</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_card" value="card">
                                <label class="form-check-label" for="pm_card">
                                    Card (Mastercard/Visa)
                                </label>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" placeholder="4111 1111 1111 1111" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Expiry</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" disabled>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">Card collection is disabled in this demo pending gateway integration.</small>
                        </div>

                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_bank" value="bank">
                                <label class="form-check-label" for="pm_bank">
                                    Bank Transfer
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">We’ll share bank details after placing the order (demo stub).</small>
                        </div>
                    </div>
                </div>

                <!-- Place Order Button -->
                <div class="place-order-section">
                    <button class="btn btn-primary btn-lg w-100" type="submit" name="place_order" value="1">
                        <i class="fas fa-lock me-2"></i>
                        Place Order - <?php echo format_currency($totals['total']); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="card-header">
                    <h4 class="fw-bold mb-0">Order Summary</h4>
                </div>
                <div class="card-body">
                    <!-- Order Items -->
                    <div class="order-items">
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="item-image">
                                <div class="item-details">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <div class="text-muted">Qty: <?php echo (int)$item['quantity']; ?></div>
                                </div>
                                <div class="item-price">
                                    <div class="fw-bold"><?php echo format_currency((float)$item['subtotal']); ?></div>
                                    <div class="text-muted"><?php echo format_currency((float)$item['unit_price']); ?> each</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Totals -->
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span><?php echo format_currency($totals['subtotal']); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax</span>
                            <span><?php echo format_currency($totals['tax']); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Delivery Fee</span>
                            <span class="free-shipping">
                                <?php echo $totals['delivery'] > 0 ? format_currency($totals['delivery']) : 'FREE'; ?>
                            </span>
                        </div>
                        <div class="total-row final-total">
                            <span>Total</span>
                            <span><?php echo format_currency($totals['total']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="trust-badges">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="badge-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <div>Secure</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="badge-item">
                                    <i class="fas fa-truck"></i>
                                    <div>Delivery</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="badge-item">
                                    <i class="fas fa-undo"></i>
                                    <div>Returns</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-bg: #f0f4ff;
    --accent: #10b981;
    --light: #ffffff;
    --border: #e2e8f0;
    --text: #334155;
    --text-light: #64748b;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

body {
    background: #ffffff;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--text);
}

/* Back Link */
.back-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    margin-bottom: 2rem;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: var(--primary-light);
}

/* Checkout Header */
.checkout-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-radius: 16px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
}

.checkout-header h1 {
    margin-bottom: 0.5rem;
}

.checkout-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

.item-count {
    font-size: 1.5rem;
    font-weight: 700;
}

/* Cards */
.checkout-card, .summary-card {
    background: var(--light);
    border-radius: 16px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-header {
    background: var(--primary-bg);
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.card-header h3, .card-header h4 {
    color: var(--text);
    margin: 0;
}

.card-body {
    padding: 2rem;
}

/* Address Cards */
.address-card {
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--light);
}

.address-card:hover {
    border-color: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.address-card.selected {
    border-color: var(--primary);
    background: var(--primary-bg);
}

.address-details {
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.4;
}

.badge {
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Divider */
.divider {
    position: relative;
    text-align: center;
    margin: 2rem 0;
}

.divider:before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--border);
}

.divider span {
    background: var(--light);
    padding: 0 1rem;
    color: var(--text-light);
    font-weight: 600;
    position: relative;
}

/* Form Elements */
.form-control {
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--light);
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}

.form-check-label {
    color: var(--text);
    cursor: pointer;
}

/* Order Items */
.order-items {
    margin-bottom: 1.5rem;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 1rem;
}

.item-details {
    flex: 1;
}

.item-details h6 {
    margin-bottom: 0.25rem;
    color: var(--text);
}

.item-price {
    text-align: right;
}

/* Order Totals */
.order-totals {
    border-top: 1px solid var(--border);
    padding-top: 1.5rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    color: var(--text);
}

.final-total {
    border-top: 1px solid var(--border);
    padding-top: 1rem;
    margin-top: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary);
}

.free-shipping {
    color: var(--accent);
    font-weight: 600;
}

/* Trust Badges */
.trust-badges {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}

.badge-item {
    text-align: center;
    color: var(--text-light);
}

.badge-item i {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.badge-item div {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border: none;
    border-radius: 12px;
    padding: 1.25rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.place-order-section {
    margin-top: 2rem;
}

/* Alert */
.alert-elegant {
    background: #fef3f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    border-left: 4px solid #ef4444;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .checkout-header {
        padding: 1.5rem;
        text-align: center;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .item-count {
        margin-top: 1rem;
    }
}
</style>

<script>
function selectAddress(addressId) {
    // Uncheck all address radios
    document.querySelectorAll('input[name="address_id"]').forEach(radio => {
        radio.checked = false;
    });
    
    // Check the selected address
    const selectedRadio = document.getElementById('address_' + addressId);
    if (selectedRadio) {
        selectedRadio.checked = true;
    }
    
    // Update visual selection
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const selectedCard = selectedRadio.closest('.address-card');
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
}

// Initialize address selection
document.addEventListener('DOMContentLoaded', function() {
    const firstAddressRadio = document.querySelector('input[name="address_id"]');
    if (firstAddressRadio) {
        selectAddress(firstAddressRadio.value);
    }
    
    // Add smooth interactions
    const cards = document.querySelectorAll('.checkout-card, .summary-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = 'var(--shadow-lg)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
});
</script>