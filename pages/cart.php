<?php
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../includes/db_functions.php';
// Load cart for user or session (guest)
if ($user) {
    $cart = get_user_cart((int)$user['id']);
} else {
    $cart = get_session_cart();
}
?>
<!-- Cart page uses global theme-light.css; removed inline styles and meta tags -->
    <section class="container py-5">
        <!-- Continue Shopping Link -->
        <a href="index.php?page=home" class="continue-shopping">
            <i class="fas fa-arrow-left"></i>
            Continue Shopping
        </a>

        <!-- Cart Header -->
        <div class="cart-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-3"><i class="fas fa-shopping-cart me-3"></i>Your Shopping Cart</h1>
                    <p class="mb-0">Review your items and proceed to checkout</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="fs-4 fw-bold">
                        <?php if ($user && isset($cart) && !empty($cart['items'])): ?>
                            <?php echo count($cart['items']); ?> item<?php echo count($cart['items']) !== 1 ? 's' : ''; ?>
                        <?php else: ?>
                            0 items
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$cart || empty($cart['items'])): ?>
                <!-- Empty Cart State -->
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Your Cart is Empty</h3>
                    <p class="text-muted mb-4">Browse our products and add items to get started</p>
                    <a href="index.php?page=home" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                    </a>
                </div>
        <?php else: ?>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="cart-items">
                            <?php
                                $total = 0.0;
                                $itemCount = 0;
                                foreach ($cart['items'] as $item):
                                $total += (float)$item['subtotal'];
                                $itemCount += (int)$item['quantity'];
                            ?>
                                <div class="cart-item">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <!-- Product Image -->
                                            <div class="col-md-2">
                                                <img src="<?php echo !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     class="cart-item-image">
                                            </div>
                                            
                                            <!-- Product Details -->
                                            <div class="col-md-4">
                                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                                <p class="text-muted mb-0 small">SKU: <?php echo htmlspecialchars($item['product_id']); ?></p>
                                            </div>
                                            
                                            <!-- Quantity Controls -->
                                            <div class="col-md-3">
                                                <form method="POST" action="index.php?page=cart" class="m-0 p-0">
                                                    <input type="hidden" name="action" value="update_quantity">
                                                    <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                                    <div class="quantity-control">
                                                        <button type="button" class="quantity-btn" onclick="stepQuantity(this, -1)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" name="quantity" class="quantity-input" value="<?php echo (int)$item['quantity']; ?>" min="1">
                                                        <button type="button" class="quantity-btn" onclick="stepQuantity(this, 1)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="text-muted small mt-1">Change quantity updates server</div>
                                                    <button type="submit" class="btn btn-link p-0 small">Update</button>
                                                </form>
                                            </div>
                                            
                                            <!-- Price and Remove -->
                                            <div class="col-md-3 text-end">
                                                <div class="fw-bold fs-5 text-primary mb-2">
                                                    <?php echo format_currency((float)$item['subtotal']); ?>
                                                </div>
                                                <div class="text-muted small mb-2">
                                                    <?php echo format_currency((float)$item['unit_price']); ?> each
                                                </div>
                                                <form method="POST" action="index.php?page=cart" class="d-inline">
                                                    <input type="hidden" name="action" value="remove_item">
                                                    <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                                    <button type="submit" class="remove-btn">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h4 class="fw-bold mb-4">Order Summary</h4>
                            
                            <div class="summary-row">
                                <span>Items (<?php echo $itemCount; ?>):</span>
                                <span><?php echo format_currency($total); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Delivery:</span>
                                <span class="text-muted">Calculated at checkout</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Tax:</span>
                                <span><?php echo format_currency($total * 0.08); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Total:</span>
                                <span class="text-primary fw-bold"><?php echo format_currency($total * 1.08); ?></span>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <a href="index.php?page=checkout" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                                </a>
                                <a href="index.php?page=home" class="btn btn-outline-primary">
                                    Continue Shopping
                                </a>
                            </div>
                            
                            <!-- Trust Badges -->
                            <div class="mt-4 text-center">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="text-muted small">
                                            <i class="fas fa-shield-alt text-success"></i><br>
                                            Secure<br>Checkout
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">
                                            <i class="fas fa-truck text-primary"></i><br>
                                            Fast<br>Delivery
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">
                                            <i class="fas fa-undo text-info"></i><br>
                                            Easy<br>Returns
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </section>

    <script>
        function stepQuantity(buttonEl, change) {
            const form = buttonEl.closest('form');
            const input = form.querySelector('.quantity-input');
            let current = parseInt(input.value);
            if (isNaN(current)) current = 1;
            current += change;
            if (current < 1) current = 1;
            input.value = current;
        }

        function showMessage(message, type) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `position-fixed bottom-0 end-0 p-3`;
            toast.innerHTML = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
<!-- End of partial -->