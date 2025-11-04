<?php
// Navbar adapted to bootstrap classes and our routing/auth
$is_logged_in = isset($_SESSION['user']);
$user_name = $is_logged_in ? ($_SESSION['user']['name'] ?? 'Account') : null;
$roles = $is_logged_in ? ($_SESSION['user']['roles'] ?? []) : [];
$is_admin = is_array($roles) && in_array('admin', $roles, true);
// Base path provided by header, compute fallback if not set
if (!isset($base)) {
  $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
  $base = $in_admin ? '..' : '.';
}
?>
<nav class="d-flex align-items-center gap-3">
  <a class="nav-link" href="<?php echo $base; ?>/index.php?page=home">Home</a>
  <a class="nav-link" href="<?php echo $base; ?>/index.php?page=product">Products</a>
  <a class="nav-link" href="<?php echo $base; ?>/index.php?page=cart">Cart</a>
  <a class="nav-link" href="<?php echo $base; ?>/index.php?page=checkout">Checkout</a>
  <?php if ($is_admin): ?>
    <a class="nav-link" href="<?php echo $base; ?>/admin/dashboard.php">Admin</a>
  <?php endif; ?>
  <?php if ($is_logged_in): ?>
    <span class="text-muted">Hi, <?php echo htmlspecialchars($user_name); ?></span>
    <a class="btn btn-outline-dark btn-sm" href="<?php echo $base; ?>/index.php?page=logout">Logout</a>
  <?php else: ?>
    <a class="btn btn-light btn-sm" href="<?php echo $base; ?>/index.php?page=login">Login</a>
    <a class="btn btn-dark btn-sm" href="<?php echo $base; ?>/index.php?page=register">Register</a>
  <?php endif; ?>
</nav>