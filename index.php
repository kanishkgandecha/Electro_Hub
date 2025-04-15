<?php
$page_title = "ElectroHUT - Home";
session_start();
require_once 'functions.php';
require_once 'header.php';

$conn = connectDB();
$query = "SELECT * FROM categories";
$stmt = $conn->query($query);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];

if ($search_query) {
    $query = "SELECT * FROM products WHERE name LIKE :search OR description LIKE :search";
    $stmt = $conn->prepare($query);
    $stmt->execute(['search' => "%$search_query%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
    if ($category_id) {
        $query = "SELECT * FROM products 
                  WHERE category_id = :category_id 
                  ORDER BY id ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute(['category_id' => $category_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM products";
        $stmt = $conn->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<style>
    .hero-section {
        min-height: 80vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        background: linear-gradient(to top, rgba(5, 18, 29, 0.7), rgba(5, 18, 29, 0.4)), 
                    url('images/2.jpg') center/cover;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    .hero-title {
        font-size: 4rem;
        letter-spacing: -0.05em;
        margin-bottom: 1.5rem;
    }

    .hero-subtitle {
        font-size: 1.5rem;
        max-width: 600px;
        margin: 0 auto 2rem;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        .hero-subtitle {
            font-size: 1.2rem;
        }
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        padding: 2rem 0;
    }

    .card-product {
        background: #0a1a2d;
        border: 1px solid #1a2d3d;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .card-product:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.3);
    }

    .product-image {
        height: 240px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .card-product:hover .product-image {
        transform: scale(1.05);
    }
</style>

<section class="hero-section py-5">
    <div class="container text-center hero-content">
        <h1 class="hero-title text-white animate__animated animate__fadeInDown">
            <span class="text-accent">Electro</span>HUT
        </h1>
        <p class="hero-subtitle text-light animate__animated animate__fadeIn animate__delay-1s">
            Where Innovation Meets Exceptional Value
        </p>
        <a href="#products" class="btn btn-accent btn-lg rounded-pill px-5 py-3 hero-cta animate__animated animate__fadeInUp animate__delay-1s">
            <i class="bi bi-lightning-charge me-2"></i>Shop New Arrivals
        </a>
    </div>
</section>

<section id="categories" class="py-5">
    <div class="container">
        <h2 class="mb-4 text-accent">Browse Categories</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($categories as $category): ?>
                <a href="index.php?category=<?= $category['id'] ?>" 
                   class="category-badge text-decoration-none px-4 py-2">
                    <?= htmlspecialchars($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="card card-product">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             class="product-image" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-3"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted mb-4"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h4 text-accent">₹<?= number_format($product['price'], 2) ?></span>
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-accent btn-sm rounded-pill">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info w-50 mx-auto">No products found</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>