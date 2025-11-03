<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && db_has_connection()) {
  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $description = trim($_POST['description'] ?? '');
  $image_path = null;

  if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (in_array($_FILES['image']['type'], $allowed, true)) {
      $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
      $safe = preg_replace('/[^a-zA-Z0-9-_]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
      $filename = $safe . '_' . time() . '.' . $ext;
      $targetDir = __DIR__ . '/../uploads/';
      if (!is_dir($targetDir)) { @mkdir($targetDir, 0775, true); }
      $target = $targetDir . $filename;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $image_path = 'uploads/' . $filename;
      }
    }
  }

  if ($name && $price > 0) {
    try {
      $newId = add_product($name, $description, $price, $image_path);
      $message = $newId ? 'Product added successfully (ID ' . $newId . ').' : 'Failed to add product.';
    } catch (Throwable $e) {
      $message = 'Error: ' . $e->getMessage();
    }
  } else {
    $message = 'Please provide a valid name and price.';
  }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
  <h2>Add Product</h2>
  <?php if ($message): ?>
    <div style="margin:.75rem 0;padding:.5rem;border:1px solid #1f2937;border-radius:8px;background:#0b1220;">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" style="max-width:600px;display:grid;gap:.75rem;">
    <label>Name <input type="text" name="name" required style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" /></label>
    <label>Price <input type="number" step="0.01" min="0.01" name="price" required style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" /></label>
    <label>Image <input type="file" name="image" accept="image/*" /></label>
    <label>Description <textarea name="description" rows="3" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;"></textarea></label>
    <button class="btn" type="submit">Save</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>