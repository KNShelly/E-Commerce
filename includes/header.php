<?php
// Shared header for all pages
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E‑Commerce</title>
    <link rel="stylesheet" href="assets/css/style.css" />
  </head>
  <body>
    <header>
      <div class="nav container">
        <div class="brand">🧱 E‑Commerce</div>
        <?php include __DIR__ . '/navbar.php'; ?>
      </div>
    </header>
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="container" style="margin-top:.75rem;">
        <div class="alert" style="padding:.6rem;border-radius:6px;background:#1f2937;color:#93c5fd;border:1px solid #3b82f6;">
          <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
      </div>
    <?php endif; ?>