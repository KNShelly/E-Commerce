<?php
// Simple navbar links using index routing, with auth-aware items
$is_logged_in = isset($_SESSION['user']);
$user_name = $is_logged_in ? ($_SESSION['user']['name'] ?? 'Account') : null;
$roles = $is_logged_in ? ($_SESSION['user']['roles'] ?? []) : [];
$is_admin = is_array($roles) && in_array('admin', $roles, true);
?>
<nav style="margin-left:auto;display:flex;gap:.5rem;align-items:center;">
  <a href="index.php?page=home">Home</a>
  <a href="index.php?page=product">Products</a>
  <a href="index.php?page=cart">Cart</a>
  <a href="index.php?page=checkout">Checkout</a>
  <?php if ($is_admin): ?>
    <a href="admin/dashboard.php">Admin</a>
  <?php endif; ?>
  <?php if ($is_logged_in): ?>
    <span style="opacity:.8;">Hi, <?php echo htmlspecialchars($user_name); ?></span>
    <a href="index.php?page=logout">Logout</a>
  <?php else: ?>
    <a href="index.php?page=login">Login</a>
    <a href="index.php?page=register">Register</a>
  <?php endif; ?>
  
</nav>