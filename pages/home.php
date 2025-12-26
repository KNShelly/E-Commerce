<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

// Improved error handling
try {
    $items = db_has_connection() ? get_products(12) : [];
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $items = [];
}

// CSRF protection
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generate_csrf_token() {
    return $_SESSION['csrf_token'];
}

// Cart count simulation
$cart_count = isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0;

// Use shared format_currency from includes/db_functions.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Premium Ecommerce Store - Discover sophisticated products with exceptional quality and service.">
    <meta name="author" content="Premium Store">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>E-Commerce - Premium Shopping Experience</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />

    <style>
        html { scroll-behavior: smooth; }
        :root {
            --primary: #2c3e50;
            --primary-light: #34495e;
            --primary-dark: #1a252f;
            --accent: #b38b59;
            --accent-light: #c8a97e;
            --accent-dark: #8b6d3b;
            --text: #2c3e50;
            --text-light: #7f8c8d;
            --light: #f8f9fa;
            --dark: #1a252f;
            --border: #e9ecef;
            --shadow: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: #ffffff;
            line-height: 1.6;
        }

        /* Header Styles */
        .header-area {
            background: rgba(255, 255, 255, 0.98);
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            transition: all 0.3s ease;
            will-change: box-shadow, transform;
            transform: translateZ(0);
        }
        
        .header-sticky {
            box-shadow: var(--shadow);
        }

        .logo-text {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }

        .nav-link-custom {
            color: var(--text) !important;
            font-weight: 500;
            padding: 0.5rem 1.5rem !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link-custom:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link-custom:hover:after,
        .nav-link-custom.active:after {
            width: 70%;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            color: var(--accent) !important;
        }

        /* Hero Section */
        .hero-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .hero-badge {
            background: rgba(179, 139, 89, 0.9);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero-description {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .hero-btn {
            background: var(--accent);
            border: 2px solid var(--accent);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 0;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-btn:hover {
            background: transparent;
            color: var(--accent);
            transform: translateY(-2px);
        }

        /* Product Cards */
        .product-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 0;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent);
        }

        .product-image {
            height: 300px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .discount-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-tag {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .sale-price {
            color: var(--text-light);
            text-decoration: line-through;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }

        /* Section Styles */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary);
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--accent);
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 3rem;
        }

        /* Categories */
        .category-card {
            position: relative;
            height: 400px;
            overflow: hidden;
            cursor: pointer;
        }

        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .category-card:hover img {
            transform: scale(1.1);
        }

        .category-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 2rem;
        }

        .category-content h4 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Features */
        .feature-card {
            background: white;
            padding: 3rem 2rem;
            text-align: center;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: var(--accent);
            transform: rotate(5deg);
        }

        /* Newsletter */
        .newsletter-section {
            background: var(--primary);
            padding: 5rem 0;
            color: white;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 5rem 0 2rem;
        }

        .footer-logo {
            color: white;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        /* Loading indicator */
        #loading-indicator {
            display: none;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-section {
                min-height: 60vh;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .category-card {
                height: 300px;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-description {
                font-size: 1rem;
            }
            
            .category-card {
                height: 250px;
            }
        }

        /* Utility Classes */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            border-radius: 0;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
            border-radius: 0;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>

<body>
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- ***** Header Area Start ***** -->
    <header class="header-area">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">
                        <span class="logo-text">E‑Commerce</span>
                    </a>
                    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item"><a class="nav-link nav-link-custom active" href="index.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php?page=products">Collections</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-custom" href="#categories">Categories</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-custom" href="#features">Services</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-custom" href="#contact">Contact</a></li>
                        </ul>
                        
                        <div class="d-flex align-items-center">
                            <a href="index.php?page=cart" class="btn btn-outline-primary me-3 position-relative">
                                <i class="fas fa-shopping-bag"></i>
                                <?php if ($cart_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        <?php echo $cart_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <a href="index.php?page=login" class="btn btn-primary">Sign In</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <!-- ***** Header Area End ***** -->

    <!-- ***** Hero Section Start ***** -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <span class="hero-badge">New Collection</span>
                        <h1 class="hero-title">Timeless Elegance, Modern Sophistication</h1>
                        <p class="hero-description">Discover our curated collection of premium products designed for the discerning individual. Experience unparalleled quality and craftsmanship.</p>
                        <a href="index.php?page=products" class="hero-btn">
                            Explore Collection <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                         alt="Premium Collection" 
                         class="img-fluid"
                         loading="lazy">
                </div>
            </div>
        </div>
    </section>
    <!-- ***** Hero Section End ***** -->

    <!-- ***** Featured Products Section ***** -->
    <section class="container py-5 my-5">
        <h2 class="section-title">Curated Selection</h2>
        <p class="section-subtitle">Discover our carefully curated collection of premium products</p>

        <div class="row g-4">
            <?php if ($items): ?>
                <?php foreach ($items as $p): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="product-card">
                            <div class="position-relative overflow-hidden">
                                <?php if (!empty($p['image_path'])): ?>
                                    <img class="product-image w-100" 
                                         src="<?php echo htmlspecialchars($p['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                         loading="lazy" decoding="async" fetchpriority="low">
                                <?php else: ?>
                                    <img class="product-image w-100" 
                                         src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" 
                                         alt="Product placeholder" 
                                         loading="lazy" decoding="async" fetchpriority="low">
                                <?php endif; ?>
                                
                                <?php if (isset($p['category'])): ?>
                                    <span class="product-badge"><?php echo htmlspecialchars($p['category']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                    <span class="discount-badge">SALE</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($p['name']); ?></h5>
                                
                                <div class="rating mb-2">
                                    <?php for ($i = 0; $i < 5; $i++) echo '<i class="far fa-star text-warning"></i>'; ?>
                                    <span class="text-muted ms-1">(0 reviews)</span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['sale_price']); ?></span>
                                        <span class="sale-price"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php else: ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text text-muted small flex-grow-1 mb-3">
                                    <?php echo isset($p['description']) && !empty($p['description']) 
                                        ? (strlen($p['description']) > 100 ? substr($p['description'], 0, 100) . '...' : $p['description'])
                                        : 'Premium quality product with exceptional craftsmanship and attention to detail.'; ?>
                                </p>
                                
                                <div class="d-flex gap-2 mt-auto">
                                    <form method="POST" action="index.php?page=cart" class="flex-grow-1 d-flex gap-2 m-0 p-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" value="1" class="btn btn-primary flex-grow-1 add-to-cart"
                                            data-product-id="<?php echo (int)$p['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($p['name']); ?>"
                                            data-product-price="<?php echo isset($p['sale_price']) ? $p['sale_price'] : $p['price']; ?>">
                                            <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                                        </button>
                                    </form>
                                    <a class="btn btn-outline-primary" href="index.php?page=product&id=<?php echo (int)$p['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback products -->
                <?php 
                $fallback_products = [
                    ['id' => 1, 'name' => 'Premium Wireless Headphones', 'price' => 299.99, 'sale_price' => 249.99, 'rating' => 4.8, 'category' => 'Audio'],
                    ['id' => 2, 'name' => 'Luxury Smart Watch', 'price' => 499.99, 'rating' => 4.9, 'category' => 'Wearables'],
                    ['id' => 3, 'name' => 'Organic Cotton Dress Shirt', 'price' => 89.99, 'rating' => 4.6, 'category' => 'Fashion'],
                    ['id' => 4, 'name' => 'Artisanal Leather Bag', 'price' => 349.99, 'sale_price' => 299.99, 'rating' => 4.7, 'category' => 'Accessories'],
                ];
                ?>
                
                <?php foreach ($fallback_products as $p): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="product-card">
                            <div class="position-relative overflow-hidden">
                                <img class="product-image w-100" 
                                     src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" 
                                     alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                     loading="lazy" decoding="async" fetchpriority="low">
                                <span class="product-badge"><?php echo $p['category']; ?></span>
                                <?php if (isset($p['sale_price'])): ?>
                                    <span class="discount-badge">SALE</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title fw-bold mb-2"><?php echo $p['name']; ?></h5>
                                <div class="rating mb-2">
                                    <?php for ($i = 0; $i < 5; $i++) echo '<i class="far fa-star text-warning"></i>'; ?>
                                    <span class="text-muted ms-1">(0 reviews)</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <?php if (isset($p['sale_price'])): ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['sale_price']); ?></span>
                                        <span class="sale-price"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php else: ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text text-muted small flex-grow-1 mb-3">Premium quality product with exceptional craftsmanship and attention to detail.</p>
                                <div class="d-flex gap-2 mt-auto">
                                    <form method="POST" action="index.php?page=cart" class="flex-grow-1 d-flex gap-2 m-0 p-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" value="1" class="btn btn-primary flex-grow-1 add-to-cart"
                                            data-product-id="<?php echo (int)$p['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($p['name']); ?>"
                                            data-product-price="<?php echo isset($p['sale_price']) ? $p['sale_price'] : $p['price']; ?>">
                                            <i class="fas fa-shopping-bag me-2"></i>Add to Cart
                                        </button>
                                    </form>
                                    <a class="btn btn-outline-primary" href="index.php?page=product&id=<?php echo (int)$p['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ***** Categories Section ***** -->
    <section id="categories" class="container-fluid py-5 my-5 bg-light">
        <div class="container">
            <h2 class="section-title">Collections</h2>
            <p class="section-subtitle">Explore our distinguished product categories</p>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=electronics" class="category-card d-block text-decoration-none">
                        <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Electronics collection" loading="lazy" decoding="async" fetchpriority="low">
                        <div class="category-content">
                            <h4 class="fw-bold">Electronics</h4>
                            <p class="mb-0">Cutting-edge technology</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=home-living" class="category-card d-block text-decoration-none">
                        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Home & Living collection" loading="lazy" decoding="async" fetchpriority="low">
                        <div class="category-content">
                            <h4 class="fw-bold">Home & Living</h4>
                            <p class="mb-0">Elevate your space</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=fashion" class="category-card d-block text-decoration-none">
                        <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Fashion collection" loading="lazy" decoding="async" fetchpriority="low">
                        <div class="category-content">
                            <h4 class="fw-bold">Fashion</h4>
                            <p class="mb-0">Timeless style</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=beauty" class="category-card d-block text-decoration-none">
                        <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Beauty products collection" loading="lazy">
                        <div class="category-content">
                            <h4 class="fw-bold">Beauty</h4>
                            <p class="mb-0">Care and cosmetics</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=accessories" class="category-card d-block text-decoration-none">
                        <img src="https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&q=70"
                             alt="Accessories (Jewelry) collection"
                             loading="lazy"
                             decoding="async" fetchpriority="low"
                             onerror="this.onerror=null;this.src='https://picsum.photos/seed/accessories-jewelry/800/600';">
                        <div class="category-content">
                            <h4 class="fw-bold">Accessories</h4>
                            <p class="mb-0">Complete your look</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="index.php?page=products&category=shoes" class="category-card d-block text-decoration-none">
                        <img src="https://images.pexels.com/photos/298863/pexels-photo-298863.jpeg?auto=compress&cs=tinysrgb&w=800&dpr=1&q=60"
                             alt="Shoes collection"
                             loading="lazy" decoding="async" fetchpriority="low"
                             onerror="this.onerror=null;this.src='https://picsum.photos/seed/shoes-collection/800/600';">
                        <div class="category-content">
                            <h4 class="fw-bold">Shoes</h4>
                            <p class="mb-0">Footwear for every occasion</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ***** Features Section ***** -->
    <section id="features" class="container py-5 my-5">
        <h2 class="section-title">Our Services</h2>
        <p class="section-subtitle">Experience the difference with our premium services</p>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h4 class="fw-bold">Complimentary Shipping</h4>
                    <p class="text-muted">Free express shipping on all orders over $200. Delivered directly to your doorstep with care.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h4 class="fw-bold">Hassle-Free Returns</h4>
                    <p class="text-muted">30-day return policy for all items. Your satisfaction is our priority.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 class="fw-bold">Secure Transactions</h4>
                    <p class="text-muted">Your payment information is protected with enterprise-grade encryption technology.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ***** Newsletter Section ***** -->
    <section class="newsletter-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3">Stay Informed</h2>
                    <p class="mb-4 opacity-90">Subscribe to our newsletter for exclusive offers and new collection previews</p>
                    <form id="newsletter-form" method="POST" class="row g-3 justify-content-center">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="col-lg-8">
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                        </div>
                        <div class="col-lg-4">
                            <button class="btn btn-light btn-lg w-100" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ***** Footer ***** -->
    <footer id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <!-- Removed brand text from footer per request -->
                    <p class="text-light opacity-80">Your destination for premium products and exceptional shopping experiences. Quality and sophistication redefined.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-0" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-0" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-0" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-0" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Collections</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php?page=products" class="text-light opacity-80 text-decoration-none">All Products</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">Featured</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">New Arrivals</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">Limited Edition</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#contact" class="text-light opacity-80 text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">Shipping</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">Returns</a></li>
                        <li><a href="#" class="text-light opacity-80 text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="text-white mb-3">Contact Information</h5>
                    <ul class="list-unstyled text-light opacity-80">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Premium Avenue, Luxury District</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> contact@elegance.com</li>
                    </ul>
                </div>
            </div>
            <div class="text-center py-4 border-top border-secondary mt-4">
                <p class="text-light opacity-60 mb-0">&copy; e commerce 2025</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    
    <script>
        // Sticky Header (throttled for smooth scrolling)
        (function() {
            const header = document.querySelector('.header-area');
            let lastY = 0;
            let ticking = false;
            function updateHeader(y) {
                const shouldStick = y > 100;
                const hasClass = header.classList.contains('header-sticky');
                if (shouldStick && !hasClass) header.classList.add('header-sticky');
                else if (!shouldStick && hasClass) header.classList.remove('header-sticky');
            }
            window.addEventListener('scroll', function() {
                lastY = window.scrollY || window.pageYOffset;
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        updateHeader(lastY);
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
            // Initialize on load
            updateHeader(window.scrollY || window.pageYOffset);
        })();

        // Smooth scrolling via CSS (removed JS animation to reduce main-thread work)

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('form');
                const productName = this.getAttribute('data-product-name');
                const originalText = this.innerHTML;
                if (form) {
                    e.preventDefault();
                    // Build form data for AJAX submit
                    const fd = new FormData(form);
                    fd.set('add_to_cart', '1');
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
                    this.disabled = true;
                    fetch('index.php?page=cart_add', {
                        method: 'POST',
                        body: fd,
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json()).then(data => {
                        if (data && data.ok) {
                            const cartBadge = document.querySelector('.badge.bg-primary') || createCartBadge();
                            const nextCount = parseInt(data.cart_count ?? '0');
                            cartBadge.textContent = nextCount;
                            showToast(`${productName} added to cart!`);
                        } else {
                            showToast(data?.error || 'Unable to add to cart.');
                        }
                    }).catch(() => {
                        showToast('Network error. Please try again.');
                    }).finally(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    });
                    return;
                }
                // Fallback: simulated flow for non-form buttons
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
                this.disabled = true;
                setTimeout(() => {
                    const currentCount = parseInt(document.querySelector('.badge.bg-primary')?.textContent || '0');
                    const cartBadge = document.querySelector('.badge.bg-primary') || createCartBadge();
                    cartBadge.textContent = currentCount + 1;
                    showToast(`${productName} added to cart!`);
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 500);
            });
        });

        function createCartBadge() {
            const badge = document.createElement('span');
            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary';
            document.querySelector('a[href*="cart"]').appendChild(badge);
            return badge;
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header bg-primary text-white">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Newsletter form handling
        document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const button = this.querySelector('button');
            
            if (validateEmail(emailInput.value)) {
                // Show loading state
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
                
                // Simulate API call
                setTimeout(() => {
                    showToast('Thank you for subscribing to our newsletter!');
                    emailInput.value = '';
                    
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 1000);
            } else {
                alert('Please enter a valid email address.');
                emailInput.focus();
            }
        });

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Show loading when links are clicked
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href') && !this.getAttribute('href').startsWith('#')) {
                        document.getElementById('loading-indicator').style.display = 'block';
                    }
                });
            });
            
            // Hide loading when page is fully loaded
            window.addEventListener('load', function() {
                document.getElementById('loading-indicator').style.display = 'none';
            });
        });
    </script>
</body>
</html>