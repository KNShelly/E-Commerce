<?php
/**
 * Reusable PDO-based database helper functions.
 * Assumes $pdo is defined from config/db.php.
 */

if (!isset($pdo)) {
  require_once __DIR__ . '/../config/db.php';
}

function db_has_connection(): bool {
  return isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO;
}

function get_products(int $limit = 12): array {
  if (!db_has_connection()) return [];
  $sql = 'SELECT id, name, description, price, image_path FROM products ORDER BY id DESC LIMIT :limit';
  $stmt = $GLOBALS['pdo']->prepare($sql);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll();
}

function get_product_by_id(int $id): ?array {
  if (!db_has_connection()) return null;
  $stmt = $GLOBALS['pdo']->prepare('SELECT * FROM products WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function add_product(string $name, string $description, float $price, ?string $image_path): int {
  if (!db_has_connection()) return 0;
  $stmt = $GLOBALS['pdo']->prepare(
    'INSERT INTO products (name, description, price, image_path) VALUES (:name, :description, :price, :image_path)'
  );
  $stmt->execute([
    ':name' => $name,
    ':description' => $description,
    ':price' => $price,
    ':image_path' => $image_path,
  ]);
  return (int)$GLOBALS['pdo']->lastInsertId();
}

function update_product(int $id, string $name, string $description, float $price, ?string $image_path): bool {
  if (!db_has_connection()) return false;
  $stmt = $GLOBALS['pdo']->prepare(
    'UPDATE products SET name = :name, description = :description, price = :price, image_path = :image_path WHERE id = :id'
  );
  return $stmt->execute([
    ':id' => $id,
    ':name' => $name,
    ':description' => $description,
    ':price' => $price,
    ':image_path' => $image_path,
  ]);
}

function delete_product(int $id): bool {
  if (!db_has_connection()) return false;
  $stmt = $GLOBALS['pdo']->prepare('DELETE FROM products WHERE id = :id');
  return $stmt->execute([':id' => $id]);
}