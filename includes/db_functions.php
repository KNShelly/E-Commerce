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
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1";
        
        if ($category_id) {
            $sql .= " AND pc.category_id = :category_id";
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        // Avoid binding LIMIT which can fail with native prepares in MySQL
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $pdo->prepare($sql);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        // No binding for LIMIT; it's safely inlined as an integer
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

// Search active products by name/description with optional category filter
function get_products_search($query, $category_id = null, $limit = null) {
    global $pdo;
    if (!db_has_connection()) return [];

    try {
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1 AND (
                    p.name LIKE :likeq OR p.description LIKE :likeq OR p.sku LIKE :likeq
                )";

        if ($category_id) {
            $sql .= " AND pc.category_id = :category_id";
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $pdo->prepare($sql);
        $like = '%' . $query . '%';
        $stmt->bindParam(':likeq', $like, PDO::PARAM_STR);
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error searching products: " . $e->getMessage());
        return [];
    }
}

// Get featured products: prioritize items with a sale_price, then newest active items
function get_featured_products($limit = 6) {
    global $pdo;
    if (!db_has_connection()) return [];

    try {
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                FROM products p 
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1
                GROUP BY p.id
                ORDER BY (p.sale_price IS NOT NULL) DESC, p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching featured products: " . $e->getMessage());
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
                       COUNT(r.id) as review_count,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
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
                       COUNT(r.id) as review_count,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
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

// Get related products based on shared categories
function get_related_products($product_id, $limit = 4) {
    global $pdo;
    if (!db_has_connection()) return [];

    try {
        // Find products that share any category with the given product
        // Exclude the current product and only include active products
        $sql = "SELECT p.*, 
                       COALESCE(p.sale_price, p.price) as display_price,
                       GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1
                  AND p.id <> :product_id
                  AND pc.category_id IN (
                        SELECT pc2.category_id FROM product_category pc2 WHERE pc2.product_id = :product_id
                  )
                GROUP BY p.id
                ORDER BY p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: if no category match, show latest active products excluding current
        if (!$items) {
            $sql2 = "SELECT p.*, 
                            COALESCE(p.sale_price, p.price) as display_price,
                            (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                     FROM products p
                     WHERE p.is_active = 1 AND p.id <> :product_id
                     ORDER BY p.created_at DESC";
            if ($limit) {
                $sql2 .= " LIMIT " . (int)$limit;
            }
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt2->execute();
            return $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $items;
    } catch (PDOException $e) {
        error_log("Error fetching related products: " . $e->getMessage());
        return [];
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

// Additional user/role management helpers
function get_role_id_by_name($role_name) {
    global $pdo;
    if (!db_has_connection()) return null;
    try {
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
        $stmt->bindParam(':name', $role_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    } catch (PDOException $e) {
        error_log("Error getting role id: " . $e->getMessage());
        return null;
    }
}

function assign_user_role_by_name($user_id, $role_name) {
    $role_id = get_role_id_by_name($role_name);
    if ($role_id === null) return false;
    return assign_user_role($user_id, $role_id);
}

function remove_user_role_by_name($user_id, $role_name) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $sql = "DELETE ur FROM user_roles ur\n                JOIN roles r ON ur.role_id = r.id\n                WHERE ur.user_id = :user_id AND r.name = :role_name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':role_name', $role_name, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error removing user role: " . $e->getMessage());
        return false;
    }
}

function set_user_active($user_id, $is_active) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = :is_active WHERE id = :id");
        $active = $is_active ? 1 : 0;
        $stmt->bindParam(':is_active', $active, PDO::PARAM_INT);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating user active: " . $e->getMessage());
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

// Get a single category by slug
function get_category_by_slug($slug) {
    global $pdo;
    if (!db_has_connection()) return null;

    try {
        $sql = "SELECT * FROM categories WHERE slug = :slug LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching category by slug: " . $e->getMessage());
        return null;
    }
}

// Ensure core top-level categories exist; insert any that are missing
function ensure_core_categories_seeded() {
    global $pdo;
    if (!db_has_connection()) return false;

    $core = [
        ['name' => 'Electronics',    'slug' => 'electronics',    'description' => 'Cutting-edge technology'],
        ['name' => 'Home & Living',  'slug' => 'home-living',    'description' => 'Elevate your space'],
        ['name' => 'Fashion',        'slug' => 'fashion',        'description' => 'Timeless style'],
        ['name' => 'Beauty',         'slug' => 'beauty',         'description' => 'Care and cosmetics'],
        ['name' => 'Accessories',    'slug' => 'accessories',    'description' => 'Complete your look'],
        ['name' => 'Shoes',          'slug' => 'shoes',          'description' => 'Footwear for every occasion'],
    ];

    try {
        foreach ($core as $cat) {
            // Skip if the slug already exists
            $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
            $stmt->bindParam(':slug', $cat['slug'], PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                continue;
            }

            // Insert missing category as top-level (parent_id NULL)
            $ins = $pdo->prepare('INSERT INTO categories (name, slug, description, parent_id) VALUES (:name, :slug, :description, NULL)');
            $ins->execute([
                ':name' => $cat['name'],
                ':slug' => $cat['slug'],
                ':description' => $cat['description'],
            ]);
        }
        return true;
    } catch (PDOException $e) {
        error_log('Error seeding core categories: ' . $e->getMessage());
        return false;
    }
}

// Get category IDs for a product
function get_product_category_ids($product_id) {
    global $pdo;
    if (!db_has_connection()) return [];

    try {
        $sql = "SELECT category_id FROM product_category WHERE product_id = :pid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':pid', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id'));
    } catch (PDOException $e) {
        error_log("Error fetching product category ids: " . $e->getMessage());
        return [];
    }
}

// Replace product's categories with given IDs
function set_product_categories($product_id, $category_ids) {
    global $pdo;
    if (!db_has_connection()) return false;

    // Normalize input to unique integers
    $category_ids = array_values(array_unique(array_map('intval', (array)$category_ids)));

    try {
        $pdo->beginTransaction();

        // Clear existing links
        $del = $pdo->prepare("DELETE FROM product_category WHERE product_id = :pid");
        $del->bindParam(':pid', $product_id, PDO::PARAM_INT);
        $del->execute();

        // Insert new links
        if (!empty($category_ids)) {
            $ins = $pdo->prepare("INSERT INTO product_category (product_id, category_id) VALUES (:pid, :cid)");
            foreach ($category_ids as $cid) {
                $ins->execute([':pid' => $product_id, ':cid' => $cid]);
            }
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Error setting product categories: " . $e->getMessage());
        return false;
    }
}

// ==================== CART FUNCTIONS ====================

// Get or create cart for user
function get_user_cart($user_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        // First try to get existing cart
        // Match schema: carts has no status column; fetch latest cart for user
        $sql = "SELECT * FROM carts WHERE user_id = :user_id ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cart) {
            // Create new cart (schema: no status column)
            $sql = "INSERT INTO carts (user_id) VALUES (:user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $cart_id = $pdo->lastInsertId();
                return get_cart_by_id($cart_id);
            }
        }
        
        // Always return cart with items
        return $cart ? get_cart_by_id((int)$cart['id']) : null;
    } catch (PDOException $e) {
        error_log("Error getting user cart: " . $e->getMessage());
        return null;
    }
}

// Get or create cart for current session (guest cart)
function get_session_cart() {
    global $pdo;
    if (!db_has_connection()) return null;
    if (!isset($_SESSION)) { session_start(); }

    try {
        // Ensure a session cart token exists
        if (empty($_SESSION['cart_token'])) {
            $_SESSION['cart_token'] = bin2hex(random_bytes(16));
        }
        $token = $_SESSION['cart_token'];

        // Try to get existing cart by token
        $sql = "SELECT * FROM carts WHERE token = :token ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            // Create new cart with token for guest
            $sql = "INSERT INTO carts (token) VALUES (:token)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $cart_id = (int)$pdo->lastInsertId();
                return get_cart_by_id($cart_id);
            }
        }

        return $cart ? get_cart_by_id((int)$cart['id']) : null;
    } catch (PDOException $e) {
        error_log("Error getting session cart: " . $e->getMessage());
        return null;
    }
}

// Get cart by token with items
function get_cart_by_token($token) {
    global $pdo;
    if (!db_has_connection()) return null;

    try {
        $sql = "SELECT c.*, 
                       ci.id as item_id, ci.quantity, ci.price AS unit_price, ci.product_id AS product_id,
                       p.name as product_name, p.slug as product_slug,
                       COALESCE(p.sale_price, p.price) as current_price,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
                FROM carts c
                LEFT JOIN cart_items ci ON c.id = ci.cart_id
                LEFT JOIN products p ON ci.product_id = p.id
                WHERE c.token = :token";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) return null;

        $cart = [
            'id' => $results[0]['id'],
            'user_id' => $results[0]['user_id'],
            'token' => $results[0]['token'],
            'created_at' => $results[0]['created_at'],
            'updated_at' => $results[0]['updated_at'],
            'items' => []
        ];

        foreach ($results as $row) {
            if ($row['item_id']) {
                $cart['items'][] = [
                    'id' => $row['item_id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'product_slug' => $row['product_slug'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'current_price' => $row['current_price'],
                    'image_path' => $row['image_path'],
                    'subtotal' => $row['quantity'] * $row['unit_price']
                ];
            }
        }

        return $cart;
    } catch (PDOException $e) {
        error_log("Error fetching cart by token: " . $e->getMessage());
        return null;
    }
}

// Get cart by ID with items
function get_cart_by_id($cart_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    
    try {
        // cart_items uses 'price' column in schema. Alias as unit_price for compatibility.
        $sql = "SELECT c.*, 
                       ci.id as item_id, ci.quantity, ci.price AS unit_price, ci.product_id AS product_id,
                       p.name as product_name, p.slug as product_slug,
                       COALESCE(p.sale_price, p.price) as current_price,
                       (SELECT pi.file_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY `order` ASC, id ASC LIMIT 1) AS image_path
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
            'created_at' => $results[0]['created_at'],
            'updated_at' => $results[0]['updated_at'],
            'items' => []
        ];
        
        foreach ($results as $row) {
            if ($row['item_id']) {
                $cart['items'][] = [
                    'id' => $row['item_id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'product_slug' => $row['product_slug'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'current_price' => $row['current_price'],
                    'image_path' => $row['image_path'],
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
            // Schema uses 'price' column, not 'unit_price'
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, price) 
                    VALUES (:cart_id, :product_id, :quantity, :price)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':price', $unit_price, PDO::PARAM_STR);
            return $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Error adding to cart: " . $e->getMessage());
        return false;
    }
}

// Add item to session (guest) cart
function add_to_cart_guest($product_id, $quantity = 1) {
    global $pdo;
    if (!db_has_connection()) return false;

    try {
        $cart = get_session_cart();
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
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, price) 
                    VALUES (:cart_id, :product_id, :quantity, :price)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':price', $unit_price, PDO::PARAM_STR);
            return $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Error adding to guest cart: " . $e->getMessage());
        return false;
    }
}

// Remove item from session cart
function remove_cart_item_guest($item_id) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $cart = get_session_cart();
        if (!$cart) return false;
        $sql = "DELETE FROM cart_items WHERE id = :item_id AND cart_id = :cart_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error removing guest cart item: " . $e->getMessage());
        return false;
    }
}

// Update quantity for a session cart item
function update_cart_item_quantity_guest($item_id, $quantity) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $cart = get_session_cart();
        if (!$cart) return false;
        $qty = max(1, (int)$quantity);
        $sql = "UPDATE cart_items SET quantity = :quantity WHERE id = :item_id AND cart_id = :cart_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':quantity', $qty, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating guest cart item quantity: " . $e->getMessage());
        return false;
    }
}

// Remove a cart item (safely scoped to the user's cart)
function remove_cart_item($user_id, $item_id) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $cart = get_user_cart($user_id);
        if (!$cart) return false;
        $sql = "DELETE FROM cart_items WHERE id = :item_id AND cart_id = :cart_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error removing cart item: " . $e->getMessage());
        return false;
    }
}

// Update quantity for a cart item (min quantity = 1)
function update_cart_item_quantity($user_id, $item_id, $quantity) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $cart = get_user_cart($user_id);
        if (!$cart) return false;
        $qty = max(1, (int)$quantity);
        $sql = "UPDATE cart_items SET quantity = :quantity WHERE id = :item_id AND cart_id = :cart_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':quantity', $qty, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating cart item quantity: " . $e->getMessage());
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

// Currency formatting helper (defaults to KSh)
function format_currency($amount) {
    $currency = get_setting('currency', 'KSh');
    // Normalize common variations to a display-friendly symbol/prefix
    $prefix = ($currency === 'KSh' || $currency === 'KES') ? 'KSh ' : ($currency === 'USD' ? '$' : $currency . ' ');
    return $prefix . number_format((float)$amount, 2);
}

function calculate_cart_totals($cart, $delivery_distance_km = null) {
    $subtotal = 0.0;
    if (!isset($cart['items']) || !is_array($cart['items'])) {
        return [
            'subtotal' => 0.0,
            'tax' => 0.0,
            'delivery' => 0.0,
            'total' => 0.0
        ];
    }
    foreach ($cart['items'] as $item) {
        $subtotal += (float)$item['subtotal'];
    }
    $tax_rate = (float)get_setting('tax_rate', '0');
    // Delivery fee settings (defaults suitable for KSh)
    $base_fee = (float)get_setting('delivery_base_fee', '150');
    $per_km = (float)get_setting('delivery_per_km', '50');
    $free_threshold = (float)get_setting('free_delivery_threshold', '0');
    $tax = $subtotal * $tax_rate;
    // Compute delivery fee by distance if provided; else fall back to base fee or free threshold
    if ($subtotal >= $free_threshold) {
        $delivery = 0.0;
    } else {
        if ($delivery_distance_km !== null && is_numeric($delivery_distance_km)) {
            $km = max(0.0, (float)$delivery_distance_km);
            $delivery = $base_fee + ($per_km * $km);
        } else {
            // No distance provided; charge base fee
            $delivery = $base_fee;
        }
    }
    $total = $subtotal + $tax + $delivery;
    return compact('subtotal','tax','delivery','total');
}

function create_order_from_cart($user_id, $address_id, $delivery_distance_km = null) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        // Get cart
        $cart = get_user_cart($user_id);
        if (!$cart || empty($cart['items'])) return false;
        $totals = calculate_cart_totals($cart, $delivery_distance_km);

        $pdo->beginTransaction();
        // Create order compatible with schema (requires order_number and shipping/tax columns)
        $order_number = 'ORD-' . date('YmdHis') . '-' . random_int(1000, 9999);
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, billing_address_id, shipping_address_id, total, tax, shipping, status) 
                               VALUES (:user_id, :order_number, :billing_address_id, :shipping_address_id, :total, :tax, :shipping, 'pending')");
        $ok = $stmt->execute([
            ':user_id' => $user_id,
            ':order_number' => $order_number,
            ':billing_address_id' => $address_id,
            ':shipping_address_id' => $address_id,
            ':total' => (float)$totals['total'],
            ':tax' => (float)$totals['tax'],
            ':shipping' => (float)$totals['delivery']
        ]);
        if (!$ok) {
            $pdo->rollBack();
            return false;
        }
        $order_id = (int)$pdo->lastInsertId();

        // Insert order items
        $oi = $pdo->prepare("INSERT INTO order_items (order_id, product_id, sku, name, quantity, price, total) 
                             VALUES (:order_id, :product_id, :sku, :name, :quantity, :price, :total)");
        foreach ($cart['items'] as $item) {
            $product_id = isset($item['product_id']) ? (int)$item['product_id'] : null;
            if (!$product_id && !empty($item['product_slug'])) {
                $prod = get_product_by_slug($item['product_slug']);
                $product_id = $prod ? (int)$prod['id'] : null;
            }
            if (!$product_id) {
                $pdo->rollBack();
                return false;
            }
            $oi->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':sku' => isset($item['sku']) ? $item['sku'] : null,
                ':name' => isset($item['product_name']) ? $item['product_name'] : null,
                ':quantity' => (int)$item['quantity'],
                ':price' => (float)$item['unit_price'],
                ':total' => (float)$item['subtotal']
            ]);
        }

        // Touch cart updated_at to reflect checkout
        $upd = $pdo->prepare("UPDATE carts SET updated_at = CURRENT_TIMESTAMP WHERE id = :id");
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

// Update order status safely to a known value
function update_order_status($order_id, $new_status) {
    global $pdo;
    if (!db_has_connection()) return false;
    // Only allow statuses present in schema
    $allowed = ['pending','processing','shipped','completed','cancelled','refunded'];
    if (!in_array($new_status, $allowed, true)) return false;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// ==================== PAYMENT FUNCTIONS ====================

// Create a payment record for an order
function create_payment($order_id, $method, $amount, $currency = 'USD', $status = 'pending', $transaction_id = null) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $stmt = $pdo->prepare("INSERT INTO payments (order_id, method, amount, currency, status, transaction_id) 
                               VALUES (:order_id, :method, :amount, :currency, :status, :transaction_id)");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':method', $method, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':currency', $currency, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        if ($transaction_id === null || $transaction_id === '') {
            $stmt->bindValue(':transaction_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
        }
        return $stmt->execute() ? (int)$pdo->lastInsertId() : false;
    } catch (PDOException $e) {
        error_log("Error creating payment: " . $e->getMessage());
        return false;
    }
}

// Get the latest payment for an order
function get_latest_payment_for_order($order_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = :order_id ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching payment: " . $e->getMessage());
        return null;
    }
}

// Update payment status and optional transaction id
function update_payment_status($payment_id, $new_status, $transaction_id = null) {
    global $pdo;
    if (!db_has_connection()) return false;
    $allowed = ['pending','completed','failed','refunded'];
    if (!in_array($new_status, $allowed, true)) return false;
    try {
        $stmt = $pdo->prepare("UPDATE payments SET status = :status, transaction_id = :tx WHERE id = :id");
        $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        if ($transaction_id === null || $transaction_id === '') {
            $stmt->bindValue(':tx', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':tx', $transaction_id, PDO::PARAM_STR);
        }
        $stmt->bindParam(':id', $payment_id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating payment: " . $e->getMessage());
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

        // Normalize SKU and sale_price: treat empty string as NULL to avoid unique '' collisions
        $skuVal = ($sku === null || trim($sku) === '') ? null : trim($sku);
        $saleVal = ($sale_price === null || $sale_price === '' ) ? null : $sale_price;

        if ($skuVal === null) {
            $stmt->bindValue(':sku', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':sku', $skuVal, PDO::PARAM_STR);
        }

        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':price', $price, PDO::PARAM_STR);
        if ($saleVal === null) {
            $stmt->bindValue(':sale_price', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':sale_price', $saleVal, PDO::PARAM_STR);
        }
        $stmt->bindValue(':stock', (int)$stock, PDO::PARAM_INT);
        
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

// ==================== PRODUCT IMAGE FUNCTIONS ====================
function add_product_image($product_id, $file_path, $alt_text = null, $order = 0) {
    global $pdo;
    if (!db_has_connection()) return false;
    try {
        $sql = "INSERT INTO product_images (product_id, file_path, alt_text, `order`) VALUES (:product_id, :file_path, :alt_text, :order)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':file_path', $file_path, PDO::PARAM_STR);
        $stmt->bindParam(':alt_text', $alt_text, PDO::PARAM_STR);
        $stmt->bindParam(':order', $order, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error adding product image: " . $e->getMessage());
        return false;
    }
}

function get_product_images($product_id) {
    global $pdo;
    if (!db_has_connection()) return [];
    try {
        $sql = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY `order` ASC, id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product images: " . $e->getMessage());
        return [];
    }
}

function get_primary_image_path($product_id) {
    global $pdo;
    if (!db_has_connection()) return null;
    try {
        $sql = "SELECT file_path FROM product_images WHERE product_id = :product_id ORDER BY `order` ASC, id ASC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['file_path'] : null;
    } catch (PDOException $e) {
        error_log("Error fetching primary image: " . $e->getMessage());
        return null;
    }
}
?>
