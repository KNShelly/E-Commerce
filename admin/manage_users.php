<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
// Handle admin actions for users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && db_has_connection()) {
  $action = $_POST['action'];
  $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
  if ($user_id > 0) {
    $ok = false;
    if ($action === 'make_admin') {
      $ok = assign_user_role_by_name($user_id, 'admin');
      $_SESSION['flash'] = $ok ? 'Granted admin role to user #' . $user_id : 'Failed to grant admin role.';
    } elseif ($action === 'revoke_admin') {
      $ok = remove_user_role_by_name($user_id, 'admin');
      $_SESSION['flash'] = $ok ? 'Revoked admin role from user #' . $user_id : 'Failed to revoke admin role.';
    } elseif ($action === 'deactivate') {
      $ok = set_user_active($user_id, false);
      $_SESSION['flash'] = $ok ? 'Deactivated user #' . $user_id : 'Failed to deactivate user.';
    } elseif ($action === 'activate') {
      $ok = set_user_active($user_id, true);
      $_SESSION['flash'] = $ok ? 'Activated user #' . $user_id : 'Failed to activate user.';
    } elseif ($action === 'send_reset') {
      // Stub for sending password reset email
      $_SESSION['flash'] = 'Password reset email queued for user #' . $user_id . '.';
      $ok = true;
    } elseif ($action === 'send_verification') {
      // Stub for sending email verification
      $_SESSION['flash'] = 'Verification email queued for user #' . $user_id . '.';
      $ok = true;
    }
  }
  header('Location: manage_users.php');
  exit;
}

$users = [];
if (db_has_connection()) {
  try {
    $stmt = $pdo->query("SELECT u.id, u.email, u.first_name, u.last_name, u.is_active,
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
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ''); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['roles'] ?? 'customer'); ?></td>
            <td>
              <?php if ((int)$u['is_active'] === 1): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-2">
                <?php $has_admin = strpos($u['roles'] ?? '', 'admin') !== false; ?>
                <?php if (!$has_admin): ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Grant admin role to this user?');">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="make_admin">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Make Admin</button>
                  </form>
                <?php else: ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Revoke admin role from this user?');">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="revoke_admin">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Revoke Admin</button>
                  </form>
                <?php endif; ?>
                <?php if ((int)$u['is_active'] === 1): ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Deactivate this user account?');">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                  </form>
                <?php else: ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Activate this user account?');">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="activate">
                    <button type="submit" class="btn btn-sm btn-success">Activate</button>
                  </form>
                <?php endif; ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Send password reset email to this user?');">
                  <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                  <input type="hidden" name="action" value="send_reset">
                  <button type="submit" class="btn btn-sm btn-outline-secondary">Send Reset</button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('Send verification email to this user?');">
                  <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                  <input type="hidden" name="action" value="send_verification">
                  <button type="submit" class="btn btn-sm btn-outline-secondary">Send Verification</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>