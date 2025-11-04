<?php
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && db_has_connection()) {
  $sku = trim($_POST['sku'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $slug_input = trim($_POST['slug'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = isset($_POST['price']) ? (float)($_POST['price']) : 0;
  $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
  $stock = isset($_POST['stock']) ? (int)($_POST['stock']) : 0;

  // Generate slug from name if not provided
  $slug = $slug_input !== '' ? $slug_input : strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
  $slug = trim($slug, '-');

  if ($name && $slug && $price > 0) {
    try {
      $newId = add_product($sku, $name, $slug, $description, $price, $sale_price, $stock);
      $message = $newId ? 'Product added successfully (ID ' . $newId . ').' : 'Failed to add product.';
    } catch (Throwable $e) {
      $message = 'Error: ' . $e->getMessage();
    }
  } else {
    $message = 'Please provide a valid name, slug, and price.';
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
  <form method="post" style="max-width:600px;display:grid;gap:.75rem;">
    <label>SKU
      <input type="text" name="sku" placeholder="e.g. LAPTOP-001" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Name
      <input type="text" name="name" required style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Slug
      <input type="text" name="slug" placeholder="auto-from-name if blank" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Price
      <input type="number" step="0.01" min="0.01" name="price" required style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Sale Price (optional)
      <input type="number" step="0.01" min="0" name="sale_price" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Stock
      <input type="number" step="1" min="0" name="stock" value="0" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <label>Description
      <textarea name="description" rows="3" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;"></textarea>
    </label>
    <button class="btn" type="submit">Save</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>