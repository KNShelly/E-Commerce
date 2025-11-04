<?php
// Database helper functions for the comprehensive e-commerce system

// Check if database connection exists
function db_has_connection() {
    global $pdo;
    return isset($pdo) && $pdo instanceof PDO;
}

// ==================== PRODUCT FUNCTIONS ====================

// Get all active products with category information
function get_products($limit = null, $category_id = null) {
    global $pdo;
    if (!db_has_connection()) return [];
    
    try {
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1";
        
        if ($category_id) {
            $sql .= " AND pc.category_id = :category_id";
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $pdo->prepare($sql);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

// Get product by ID with full details
function get_product_by_id($id) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id AND r.approved = 1
                WHERE p.id = :id AND p.is_active = 1
                GROUP BY p.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return null;
    }
}

// Get product by slug
function get_product_by_slug($slug) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id AND r.approved = 1
                WHERE p.slug = :slug AND p.is_active = 1
                GROUP BY p.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product by slug: " . $e->getMessage());
        return null;
    }
}

// ==================== USER FUNCTIONS ====================

// Get user by email
function get_user_by_email($email) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        $sql = "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as roles
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.email = :email
                GROUP BY u.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}

// Create new user
function create_user($name, $email, $password) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Split provided full name into first_name and last_name
        $full = trim($name);
        $parts = preg_split('/\s+/', $full);
        $first_name = $parts && count($parts) > 0 ? $parts[0] : $full;
        $last_name = $parts && count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $sql = "INSERT INTO users (email, password, first_name, last_name) VALUES (:email, :password, :first_name, :last_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $user_id = $pdo->lastInsertId();
            // Assign customer role by default
            assign_user_role($user_id, 2); // Role ID 2 is customer
            return $user_id;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

// Assign role to user
function assign_user_role($user_id, $role_id) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error assigning user role: " . $e->getMessage());
        return false;
    }
}

// Check if user has role
function user_has_role($user_id, $role_name) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $sql = "SELECT COUNT(*) FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = :user_id AND r.name = :role_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':role_name', $role_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking user role: " . $e->getMessage());
        return false;
    }
}

// ==================== CATEGORY FUNCTIONS ====================

// Get all categories
function get_categories($parent_id = null) {
    global $pdo;
    if (!db_has_connection()) return [];
    
    try {
        $sql = "SELECT * FROM categories WHERE 1=1";
        
        if ($parent_id === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = :parent_id";
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $pdo->prepare($sql);
        if ($parent_id !== null) {
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

// ==================== CART FUNCTIONS ====================

// Get or create cart for user
function get_user_cart($user_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        // First try to get existing cart
        $sql = "SELECT * FROM carts WHERE user_id = :user_id AND status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cart) {
            // Create new cart
            $sql = "INSERT INTO carts (user_id, status) VALUES (:user_id, 'active')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $cart_id = $pdo->lastInsertId();
                return get_cart_by_id($cart_id);
            }
        }
        
        return $cart;
    } catch (PDOException $e) {
        error_log("Error getting user cart: " . $e->getMessage());
        return null;
    }
}

// Get cart by ID with items
function get_cart_by_id($cart_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        $sql = "SELECT c.*, 
                       ci.id as item_id, ci.quantity, ci.unit_price,
                       p.name as product_name, p.slug as product_slug,
                       COALESCE(p.sale_price, p.price) as current_price
                FROM carts c
                LEFT JOIN cart_items ci ON c.id = ci.cart_id
                LEFT JOIN products p ON ci.product_id = p.id
                WHERE c.id = :cart_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) return null;
        
        $cart = [
            'id' => $results[0]['id'],
            'user_id' => $results[0]['user_id'],
            'status' => $results[0]['status'],
            'created_at' => $results[0]['created_at'],
            'updated_at' => $results[0]['updated_at'],
            'items' => []
        ];
        
        foreach ($results as $row) {
            if ($row['item_id']) {
                $cart['items'][] = [
                    'id' => $row['item_id'],
                    'product_name' => $row['product_name'],
                    'product_slug' => $row['product_slug'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'current_price' => $row['current_price'],
                    'subtotal' => $row['quantity'] * $row['unit_price']
                ];
            }
        }
        
        return $cart;
    } catch (PDOException $e) {
        error_log("Error fetching cart: " . $e->getMessage());
        return null;
    }
}

// Add item to cart
function add_to_cart($user_id, $product_id, $quantity = 1) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $cart = get_user_cart($user_id);
        if (!$cart) return false;
        
        $product = get_product_by_id($product_id);
        if (!$product) return false;
        
        $unit_price = $product['sale_price'] ?? $product['price'];
        
        // Check if item already exists in cart
        $sql = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $sql = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':id', $existing_item['id'], PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Add new item
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price) 
                    VALUES (:cart_id, :product_id, :quantity, :unit_price)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':unit_price', $unit_price, PDO::PARAM_STR);
            return $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Error adding to cart: " . $e->getMessage());
        return false;
    }
}

// ==================== SETTINGS FUNCTIONS ====================

// Get setting value
function get_setting($key, $default = null) {
    global $pdo;
    if (!db_has_connection()) return $default;
    
    try {
        $sql = "SELECT value FROM settings WHERE `key` = :key";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    } catch (PDOException $e) {
        error_log("Error fetching setting: " . $e->getMessage());
        return $default;
    }
}

// ==================== ADDRESS FUNCTIONS ====================

function get_user_addresses($user_id) {
    global $pdo;
    if (!db_has_connection()) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user addresses: " . $e->getMessage());
        return [];
    }
}

function create_address($user_id, $label, $line1, $line2, $city, $state, $postal_code, $country, $is_default = 0) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        if ($is_default) {
            // Clear existing defaults
            $clear = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
            $clear->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $clear->execute();
        }
        $sql = "INSERT INTO addresses (user_id, label, line1, line2, city, state, postal_code, country, is_default) 
                VALUES (:user_id, :label, :line1, :line2, :city, :state, :postal_code, :country, :is_default)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':label', $label, PDO::PARAM_STR);
        $stmt->bindParam(':line1', $line1, PDO::PARAM_STR);
        $stmt->bindParam(':line2', $line2, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':postal_code', $postal_code, PDO::PARAM_STR);
        $stmt->bindParam(':country', $country, PDO::PARAM_STR);
        $stmt->bindParam(':is_default', $is_default, PDO::PARAM_INT);
        return $stmt->execute() ? (int)$pdo->lastInsertId() : false;
    } catch (PDOException $e) {
        error_log("Error creating address: " . $e->getMessage());
        return false;
    }
}

// ==================== ORDER / CHECKOUT FUNCTIONS ====================

function calculate_cart_totals($cart) {
    $subtotal = 0.0;
    if (!isset($cart['items']) || !is_array($cart['items'])) {
        return [
            'subtotal' => 0.0,
            'tax' => 0.0,
            'shipping' => 0.0,
            'total' => 0.0
        ];
    }
    foreach ($cart['items'] as $item) {
        $subtotal += (float)$item['subtotal'];
    }
    $tax_rate = (float)get_setting('tax_rate', '0');
    $shipping_rate = (float)get_setting('shipping_rate', '0');
    $free_threshold = (float)get_setting('free_shipping_threshold', '0');
    $tax = $subtotal * $tax_rate;
    $shipping = $subtotal >= $free_threshold ? 0.0 : $shipping_rate;
    $total = $subtotal + $tax + $shipping;
    return compact('subtotal','tax','shipping','total');
}

function create_order_from_cart($user_id, $address_id) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        // Get cart
        $cart = get_user_cart($user_id);
        if (!$cart || empty($cart['items'])) return false;
        $totals = calculate_cart_totals($cart);

        $pdo->beginTransaction();
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status, total) VALUES (:user_id, 'pending', :total)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total', $totals['total']);
        if (!$stmt->execute()) {
            $pdo->rollBack();
            return false;
        }
        $order_id = (int)$pdo->lastInsertId();

        // Insert order items
        $oi = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                             VALUES (:order_id, :product_id, :quantity, :unit_price)");
        foreach ($cart['items'] as $item) {
            $product_id = null;
            // Recover product_id by slug/name if not included
            $prod = get_product_by_slug($item['product_slug']);
            $product_id = $prod ? (int)$prod['id'] : null;
            if (!$product_id) {
                $pdo->rollBack();
                return false;
            }
            $oi->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => (int)$item['quantity'],
                ':unit_price' => (float)$item['unit_price']
            ]);
        }

        // Update cart status
        $upd = $pdo->prepare("UPDATE carts SET status = 'ordered' WHERE id = :id");
        $upd->bindParam(':id', $cart['id'], PDO::PARAM_INT);
        $upd->execute();

        $pdo->commit();
        return $order_id;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}
// ==================== ADMIN FUNCTIONS ====================

// Add new product (admin)
function add_product($sku, $name, $slug, $description, $price, $sale_price = null, $stock = 0) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $sql = "INSERT INTO products (sku, name, slug, description, price, sale_price, stock, is_active) 
                VALUES (:sku, :name, :slug, :description, :price, :sale_price, :stock, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':sku', $sku, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':sale_price', $sale_price, PDO::PARAM_STR);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        
        return $stmt->execute() ? $pdo->lastInsertId() : false;
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

// Update product (admin)
function update_product($id, $data) {
    global $pdo;
    if (!db_has_connection()) return false;
    
    try {
        $allowed_fields = ['sku', 'name', 'slug', 'description', 'price', 'sale_price', 'stock', 'is_active'];
        $set_clauses = [];
        $params = [':id' => $id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $set_clauses[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        
        if (empty($set_clauses)) return false;
        
        $sql = "UPDATE products SET " . implode(', ', $set_clauses) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}
?>