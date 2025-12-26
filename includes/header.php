<?php
// Shared header integrating FoodMart template assets while keeping our app routing
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E‑Commerce</title>
    <?php
      // Determine base path so includes work from /admin and root pages
      $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
      $base = $in_admin ? '..' : '.';
    ?>
    <!-- Global font to match homepage -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Template vendor styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Font Awesome icons (global) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Local template styles (mapped to our assets folder) -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/vendor.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <!-- Light theme to align all pages with homepage look -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/theme-light.css">
    <?php if ($in_admin): ?>
      <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/admin.css">
    <?php endif; ?>
  </head>
  <body>
    <header class="header-area">
      <div class="container-fluid py-3">
        <div class="d-flex align-items-center justify-content-between">
          <div class="main-logo">
            <a href="<?php echo $base; ?>/index.php?page=home" class="text-decoration-none">
              <img src="<?php echo $base; ?>/assets/images/logo.png" alt="logo" class="img-fluid" style="max-height:40px;" onerror="this.style.display='none'">
              <span class="fw-bold ms-2">E‑Commerce</span>
            </a>
          </div>
          <div class="flex-grow-1 d-none d-lg-block px-3">
            <form id="search-form" class="input-group w-100" method="get" action="<?php echo $base; ?>/index.php">
              <input type="hidden" name="page" value="products">
              <span class="input-group-text bg-light border-0">All Categories</span>
              <input type="text" name="q" class="form-control bg-light" placeholder="Search products" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
              <button class="btn btn-dark" type="submit">Search</button>
            </form>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
              <i class="fa fa-bars"></i>
            </button>
          </div>
          <div class="collapse d-lg-block w-100" id="mainNav">
            <?php include __DIR__ . '/navbar.php'; ?>
          </div>
        </div>
      </div>
    </header>
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="container mt-3">
        <div class="alert alert-primary">
          <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
      </div>
    <?php endif; ?>