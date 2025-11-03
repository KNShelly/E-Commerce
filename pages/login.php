<?php
// Show any server-side auth errors set by index.php
$error = $auth_error ?? null;
?>
<section class="container">
  <h2>Login</h2>
  <?php if ($error): ?>
    <div class="alert" style="margin:.75rem 0;padding:.5rem;border-radius:6px;background:#1f2937;color:#fca5a5;border:1px solid #ef4444;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>
  <form method="post" action="index.php?page=login" style="max-width:400px;display:grid;gap:.75rem;">
    <label>
      Email
      <input type="email" name="email" placeholder="you@example.com" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" required />
    </label>
    <label>
      Password
      <input type="password" name="password" placeholder="••••••••" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" required />
    </label>
    <button class="btn" type="submit">Sign In</button>
  </form>
  <p style="margin-top:.5rem;opacity:.8;">Don’t have an account? <a href="index.php?page=register">Register</a></p>
</section>