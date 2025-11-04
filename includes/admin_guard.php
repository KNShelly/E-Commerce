<?php
// Simple admin access guard; include at the top of admin pages
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$is_logged_in = isset($_SESSION['user']);
$roles = $is_logged_in ? ($_SESSION['user']['roles'] ?? []) : [];
$is_admin = is_array($roles) && in_array('admin', $roles, true);

if (!$is_admin) {
  $_SESSION['flash'] = 'Admin access required.';
  $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/admin/dashboard.php';
  header('Location: ../index.php?page=login&next=' . urlencode($next));
  exit;
}
?>