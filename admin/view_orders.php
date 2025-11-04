<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
$orders = [];
if (db_has_connection()) {
  try {
    $stmt = $pdo->query("SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at, u.email
                         FROM orders o LEFT JOIN users u ON o.user_id = u.id
                         ORDER BY o.created_at DESC LIMIT 50");
    $orders = $stmt->fetchAll();
  } catch (Throwable $e) {
    $orders = [];
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
  <h2>Orders</h2>
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Number</th>
          <th>Status</th>
          <th>Total</th>
          <th>Customer</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?php echo (int)$o['id']; ?></td>
            <td><?php echo htmlspecialchars($o['order_number']); ?></td>
            <td><?php echo htmlspecialchars($o['status']); ?></td>
            <td>$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($o['email'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($o['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>