<?php
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$message = null;
// Load top-level categories for selection
if (db_has_connection()) {
  // Seed core categories if missing so admin can select them
  ensure_core_categories_seeded();
}
$allCategories = db_has_connection() ? get_categories(null) : [];
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

  // Ensure slug uniqueness by appending a numeric suffix if needed (check across ALL products)
  if (db_has_connection() && $slug) {
    $baseSlug = $slug;
    $suffix = 2;
    $checkSlug = function($s) use ($pdo) {
      $stmt = $pdo->prepare("SELECT 1 FROM products WHERE slug = :slug LIMIT 1");
      $stmt->bindParam(':slug', $s, PDO::PARAM_STR);
      $stmt->execute();
      return (bool)$stmt->fetchColumn();
    };
    while ($checkSlug($slug)) {
      $slug = $baseSlug . '-' . $suffix;
      $suffix++;
    }
  }

  if ($name && $slug && $price > 0) {
    try {
      // Initialize new product id to avoid undefined variable warnings
      $newId = false;
      // Treat empty SKU as NULL to avoid unique '' collisions
      $sku = ($sku === '') ? null : $sku;

      // Preflight: if SKU provided, ensure it is unique to avoid insert failure
      if ($sku !== null) {
        $stmtSku = $pdo->prepare("SELECT 1 FROM products WHERE sku = :sku LIMIT 1");
        $stmtSku->bindParam(':sku', $sku, PDO::PARAM_STR);
        $stmtSku->execute();
        if ($stmtSku->fetchColumn()) {
          $message = 'SKU already exists. Please use a unique SKU.';
        } else {
          $newId = add_product($sku, $name, $slug, $description, $price, $sale_price, $stock);
        }
      } else {
        $newId = add_product($sku, $name, $slug, $description, $price, $sale_price, $stock);
      }
      if ($newId) {
        // Assign selected categories
        $catIds = isset($_POST['category_ids']) ? (array)$_POST['category_ids'] : [];
        set_product_categories((int)$newId, $catIds);
        // Handle image uploads if provided
        if (!empty($_FILES['images']) && isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
          $uploadDir = __DIR__ . '/../assets/images/products';
          if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
          }
          // Preserve product name for alt text
          $productName = $name;
          $orderIndex = 0;
          // Iterate through multiple files
          $count = count($_FILES['images']['name']);
          for ($i = 0; $i < $count; $i++) {
            $origName = $_FILES['images']['name'][$i] ?? '';
            $type = $_FILES['images']['type'][$i] ?? '';
            $tmp  = $_FILES['images']['tmp_name'][$i] ?? '';
            $err  = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $_FILES['images']['size'][$i] ?? 0;
            if ($err !== UPLOAD_ERR_OK || !$tmp) { continue; }

            // Basic validation: size and MIME type
            if ($size > 5 * 1024 * 1024) { continue; }
            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
            $mime  = $finfo ? finfo_file($finfo, $tmp) : $type;
            if ($finfo) { finfo_close($finfo); }
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime])) { continue; }
            $ext = $allowed[$mime];

            // Sanitize base name and create unique filename
            $base = preg_replace('/[^a-zA-Z0-9-_]/', '-', pathinfo($origName, PATHINFO_FILENAME));
            $unique = $newId . '-' . time() . '-' . $i . '-' . bin2hex(random_bytes(4));
            $filename = $base ? (strtolower($base) . '-' . $unique . '.' . $ext) : ($unique . '.' . $ext);
            $dest = $uploadDir . '/' . $filename;
            if (@move_uploaded_file($tmp, $dest)) {
              // Save relative path for web usage
              $webPath = 'assets/images/products/' . $filename;
              add_product_image((int)$newId, $webPath, $productName, $orderIndex);
              $orderIndex++;
            }
          }
        }
        $message = 'Product added successfully (ID ' . $newId . ').';
      } else {
        // Preserve earlier specific messages (e.g., duplicate SKU) if set
        if (!$message) {
          $message = 'Failed to add product.';
        }
      }
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
  <form method="post" enctype="multipart/form-data" style="max-width:600px;display:grid;gap:.75rem;">
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
    <fieldset style="border:1px solid #1f2937;border-radius:8px;padding:.75rem;">
      <legend style="padding:0 .5rem;">Categories</legend>
      <?php if (!empty($allCategories)): ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem;">
          <?php foreach ($allCategories as $cat): ?>
            <label style="display:flex;align-items:center;gap:.5rem;">
              <input type="checkbox" name="category_ids[]" value="<?php echo (int)$cat['id']; ?>">
              <span><?php echo htmlspecialchars($cat['name']); ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted">No categories found.</div>
      <?php endif; ?>
    </fieldset>
    <label>Images (you can select multiple)
      <input type="file" name="images[]" multiple accept="image/*" style="width:100%;padding:.5rem;border-radius:6px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb;" />
    </label>
    <button class="btn" type="submit">Save</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>