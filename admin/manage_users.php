<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
$users = [];
if (db_has_connection()) {
  try {
    $stmt = $pdo->query("SELECT u.id, u.email, u.first_name, u.last_name,
                                GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
                         FROM users u
                         LEFT JOIN user_roles ur ON u.id = ur.user_id
                         LEFT JOIN roles r ON ur.role_id = r.id
                         GROUP BY u.id ORDER BY u.created_at DESC LIMIT 100");
    $users = $stmt->fetchAll();
  } catch (Throwable $e) {
    $users = [];
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
  <h2>Manage Users</h2>
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Roles</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ''); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['roles'] ?? 'customer'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>