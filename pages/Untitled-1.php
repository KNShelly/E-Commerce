<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';
$items = db_has_connection() ? get_products(12) : [];
?>

<!-- Removed standalone document skeleton: using shared header/footer -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joyful Commerce</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #f97316;
            --secondary-light: #fdba74;
            --accent: #10b981;
            --light: #f8fafc;
            --dark: #1e293b;
            --text: #334155;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background-color: #fefefe;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .hero-gradient-2 {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
        }
        
        .hero-gradient-3 {
            background: linear-gradient(135deg, var(--accent) 0%, #34d399 100%);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .sale-price {
            color: var(--secondary);
            text-decoration: line-through;
            font-size: 0.9rem;
            margin-left: 8px;
        }
        
        .swiper-pagination-bullet {
            background-color: white;
            opacity: 0.7;
            width: 10px;
            height: 10px;
        }
        
        .swiper-pagination-bullet-active {
            background-color: var(--accent);
            opacity: 1;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--accent);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .rating {
            color: #fbbf24;
            font-size: 0.9rem;
        }
        
        .discount-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--secondary);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .hero-content {
            color: white;
        }
        
        .hero-content h2 {
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        .hero-image {
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }
    </style>
<!-- End of page-local styles -->
<!-- Body tag removed: page is a partial included by index.php -->
    <section class="container py-5">
        <!-- Hero Section with Swiper -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="main-swiper swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="p-4 p-md-5 rounded-4 hero-gradient">
                                <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                                    <div class="hero-content flex-grow-1">
                                        <h2 class="display-5 fw-bold">Tech Essentials</h2>
                                        <p class="mb-4">Discover the latest laptops, phones, audio gear, and accessories at unbeatable prices.</p>
                                        <a href="index.php?page=product" class="btn btn-light btn-lg px-4 py-2 fw-bold">Shop Tech <i class="fas fa-arrow-right ms-2"></i></a>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" 
                                             class="img-fluid hero-image" alt="Tech" style="max-height: 300px;"
                                             onerror="this.src='assets/images/placeholder1.jpg'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="p-4 p-md-5 rounded-4 hero-gradient-2">
                                <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                                    <div class="hero-content flex-grow-1">
                                        <h2 class="display-5 fw-bold">Home & Living</h2>
                                        <p class="mb-4">Transform your space with smart home devices, kitchenware, and beautiful decor.</p>
                                        <a href="index.php?page=product" class="btn btn-light btn-lg px-4 py-2 fw-bold">Shop Home <i class="fas fa-arrow-right ms-2"></i></a>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" 
                                             class="img-fluid hero-image" alt="Home" style="max-height: 300px;"
                                             onerror="this.src='assets/images/placeholder2.jpg'">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="p-4 p-md-5 rounded-4 hero-gradient-3">
                                <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                                    <div class="hero-content flex-grow-1">
                                        <h2 class="display-5 fw-bold">Fashion & Style</h2>
                                        <p class="mb-4">Express yourself with trendy apparel, footwear, and accessories for everyone.</p>
                                        <a href="index.php?page=product" class="btn btn-light btn-lg px-4 py-2 fw-bold">Shop Fashion <i class="fas fa-arrow-right ms-2"></i></a>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" 
                                             class="img-fluid hero-image" alt="Fashion" style="max-height: 300px;"
                                             onerror="this.src='assets/images/placeholder3.jpg'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>

        <!-- Featured Products Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title">Featured Products</h2>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row g-4">
            <?php if ($items): ?>
                <?php foreach ($items as $p): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100">
                            <div class="position-relative">
                                <?php if (!empty($p['image_path'])): ?>
                                    <img class="card-img-top" src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
                                         onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80'">
                                <?php else: ?>
                                    <img class="card-img-top" src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Product">
                                <?php endif; ?>
                                
                                <?php 
                                $category_label = (!empty($p['categories']) && is_array($p['categories'])) ? ($p['categories'][0] ?? null) : ($p['category'] ?? null);
                                if ($category_label): ?>
                                    <span class="category-badge"><?php echo htmlspecialchars($category_label); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                    <span class="discount-badge">SALE</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title mb-2 fw-bold"><?php echo htmlspecialchars($p['name']); ?></h5>
                                
                                <?php if (isset($p['rating'])): ?>
                                    <div class="rating mb-2">
                                        <?php
                                        $fullStars = floor($p['rating']);
                                        $halfStar = ($p['rating'] - $fullStars) >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        if ($halfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        }
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                        <span class="text-muted ms-1">(<?php echo $p['rating']; ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['sale_price']); ?></span>
                                        <span class="sale-price"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php else: ?>
                                        <span class="price-tag"><?php echo format_currency((float)($p['display_price'] ?? $p['price'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text text-muted small flex-grow-1"><?php 
                                    echo isset($p['description']) && !empty($p['description']) 
                                        ? (strlen($p['description']) > 100 ? substr($p['description'], 0, 100) . '...' : $p['description'])
                                        : 'High-quality product with excellent features and durability.';
                                ?></p>
                                
                                <div class="d-flex gap-2 mt-auto">
                                    <form method="post" action="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="flex-grow-1">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>" />
                                        <input type="hidden" name="quantity" value="1" />
                                        <button class="btn btn-primary w-100" type="submit" name="add_to_cart" value="1">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
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
                <!-- Fallback products when database is not available -->
                <?php 
                $fallback_products = [
                    ['name' => 'Wireless Headphones', 'price' => 79.99, 'sale_price' => 59.99, 'rating' => 4.5, 'category' => 'Audio'],
                    ['name' => 'Smart Watch Series 5', 'price' => 199.99, 'rating' => 4.8, 'category' => 'Wearables'],
                    ['name' => 'Organic Cotton T-Shirt', 'price' => 24.99, 'rating' => 4.3, 'category' => 'Fashion'],
                    ['name' => 'Bluetooth Speaker', 'price' => 49.99, 'sale_price' => 39.99, 'rating' => 4.2, 'category' => 'Audio'],
                    ['name' => 'Stainless Steel Water Bottle', 'price' => 19.99, 'rating' => 4.7, 'category' => 'Home'],
                    ['name' => 'Fitness Tracker', 'price' => 89.99, 'rating' => 4.4, 'category' => 'Wearables'],
                    ['name' => 'Laptop Backpack', 'price' => 45.99, 'sale_price' => 35.99, 'rating' => 4.6, 'category' => 'Accessories'],
                    ['name' => 'Desk Lamp with Wireless Charger', 'price' => 59.99, 'rating' => 4.5, 'category' => 'Home'],
                ];
                ?>
                
                <?php foreach ($fallback_products as $index => $p): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img class="card-img-top" src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Product">
                                <span class="category-badge"><?php echo htmlspecialchars($p['category']); ?></span>
                                <?php if (isset($p['sale_price'])): ?>
                                    <span class="discount-badge">SALE</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title mb-2 fw-bold"><?php echo $p['name']; ?></h5>
                                
                                <div class="rating mb-2">
                                    <?php
                                    $fullStars = floor($p['rating']);
                                    $halfStar = ($p['rating'] - $fullStars) >= 0.5;
                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                    
                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    if ($halfStar) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    }
                                    for ($i = 0; $i < $emptyStars; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                    <span class="text-muted ms-1">(<?php echo $p['rating']; ?>)</span>
                                </div>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <?php if (isset($p['sale_price'])): ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['sale_price']); ?></span>
                                        <span class="sale-price"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php else: ?>
                                        <span class="price-tag"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text text-muted small flex-grow-1">High-quality product with excellent features and durability. Perfect for everyday use.</p>
                                
                                <div class="d-flex gap-2 mt-auto">
                                    <a class="btn btn-primary flex-grow-1" href="index.php?page=product&id=<?php echo $index + 1; ?>">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </a>
                                    <a class="btn btn-outline-primary" href="index.php?page=product&id=<?php echo $index + 1; ?>">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper
        const swiper = new Swiper('.main-swiper', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
        });
    </script>
</body>
<!-- End of partial -->