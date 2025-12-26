<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
// Handle admin actions: mark order completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && db_has_connection()) {
  $action = $_POST['action'];
  if ($action === 'mark_completed') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    if ($order_id > 0) {
      if (update_order_status($order_id, 'completed')) {
        $_SESSION['flash'] = 'Order #' . $order_id . ' marked as completed.';
      } else {
        $_SESSION['flash'] = 'Failed to update order #' . $order_id . ' status.';
      }
    }
    header('Location: view_orders.php');
    exit;
  }
}

$orders = [];
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (db_has_connection()) {
  try {
    $sql = "SELECT o.id, o.order_number, o.status, o.total, o.created_at, u.email
            FROM orders o LEFT JOIN users u ON o.user_id = u.id";
    $conds = [];
    $params = [];
    if ($status !== '') {
      $conds[] = "o.status = :status";
      $params[':status'] = $status;
    }
    if ($q !== '') {
      $conds[] = "(o.order_number LIKE :q OR u.email LIKE :q)";
      $params[':q'] = '%'.$q.'%';
    }
    if ($conds) {
      $sql .= ' WHERE ' . implode(' AND ', $conds);
    }
    $sql .= ' ORDER BY o.created_at DESC LIMIT 100';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
  } catch (Throwable $e) {
    $orders = [];
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
  <h2>Orders</h2>
  <form method="get" class="row g-3 align-items-end mb-3">
    <div class="col-sm-3">
      <label for="status" class="form-label">Status</label>
      <select id="status" name="status" class="form-select">
        <option value="">All</option>
        <?php foreach (['pending','processing','shipped','completed','cancelled','refunded'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-5">
      <label for="q" class="form-label">Search</label>
      <input id="q" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Order number or customer email">
    </div>
    <div class="col-sm-4">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="view_orders.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>
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
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?php echo (int)$o['id']; ?></td>
            <td><?php echo htmlspecialchars($o['order_number']); ?></td>
            <td><?php echo htmlspecialchars($o['status']); ?></td>
            <td><?php echo format_currency((float)$o['total']); ?></td>
            <td><?php echo htmlspecialchars($o['email'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($o['created_at']); ?></td>
            <td>
              <?php if ($o['status'] !== 'completed'): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Mark this order as completed?');">
                  <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                  <input type="hidden" name="action" value="mark_completed">
                  <button type="submit" class="btn btn-sm btn-success">Mark Completed</button>
                </form>
              <?php else: ?>
                <span class="badge bg-success">Completed</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>