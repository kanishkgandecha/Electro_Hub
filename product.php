<?php
$page_title = "Product Details";
session_start();
require_once 'functions.php';

$conn = connectDB();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id < 1) {
    header("Location: index.php");
    exit();
}

$query = "SELECT p.*, c.name AS category_name 
          FROM products p
          JOIN categories c ON p.category_id = c.id
          WHERE p.id = :product_id";
$stmt = $conn->prepare($query);
$stmt->execute(['product_id' => $product_id]); 
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    redirectWithMessage('index.php', 'Product not found.', 'danger');
}

$colors = json_decode($product['colors'], true) ?: ['Black'];
$storage_options = json_decode($product['storage_options'], true) ?: ['64GB'];
$specs = json_decode($product['specifications'], true) ?: [];
$images = json_decode($product['gallery_images'], true) ?: [$product['image_url']];

$query = "SELECT * FROM products WHERE category_id = :category_id AND id = :product_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    'category_id' => $product['category_id'],
    'product_id' => $product_id
]);
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php foreach ($products as $product): ?>
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card card-product h-100">
            <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>" 
               class="btn btn-sm btn-outline-accent">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
<?php endforeach; ?>

<?php include 'header.php'; ?>

<nav aria-label="breadcrumb" class="bg-darker py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </div>
</nav>

<section class="py-5">
    <div class="container">
        <div class="row g-5">

            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image mb-3">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="img-fluid rounded-3" id="main-product-image">
                    </div>
                    <div class="thumbnail-container d-flex flex-wrap gap-2">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 data-image="<?= htmlspecialchars($image) ?>">
                                <img src="<?= htmlspecialchars($image) ?>" 
                                     alt="Thumbnail <?= $index + 1 ?>" 
                                     class="img-thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="product-details">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                            <div class="d-flex align-items-center mb-3">
                                <div class="rating me-3">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-half text-warning"></i>
                                </div>
                                <a href="#reviews" class="text-muted">(42 reviews)</a>
                            </div>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm rounded-circle add-to-wishlist" 
                                data-product-id="<?= $product['id'] ?>">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    
                    <?php if ($product['discount'] > 0): ?>
                        <div class="price mb-4">
                            <span class="h3 fw-bold text-accent">₹<?= number_format($product['price'] * (1 - $product['discount']/100), 2) ?></span>
                            <span class="text-decoration-line-through text-muted ms-2">₹<?= number_format($product['price'], 2) ?></span>
                            <span class="badge bg-danger ms-2">Save <?= $product['discount'] ?>%</span>
                        </div>
                    <?php else: ?>
                        <div class="price mb-4">
                            <span class="h3 fw-bold text-accent">₹<?= number_format($product['price'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <p class="mb-4"><?= htmlspecialchars($product['description']) ?></p>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Color: <span id="selected-color"><?= $colors[0] ?></span></h6>
                        <div class="color-options d-flex flex-wrap gap-2">
                            <?php foreach ($colors as $color): ?>
                                <div class="color-option">
                                    <input type="radio" name="color" id="color-<?= sanitizeInput(strtolower($color)) ?>" 
                                           value="<?= sanitizeInput($color) ?>" class="color-radio" 
                                           <?= $color === $colors[0] ? 'checked' : '' ?>>
                                    <label for="color-<?= sanitizeInput(strtolower($color)) ?>" 
                                           class="color-label d-flex align-items-center gap-2">
                                        <span class="color-box" style="background-color: <?= getColorHex($color) ?>;"></span>
                                        <span><?= sanitizeInput($color) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Storage: <span id="selected-storage"><?= $storage_options[0] ?></span></h6>
                        <div class="storage-options d-flex flex-wrap gap-2">
                            <?php foreach ($storage_options as $storage): ?>
                                <div class="storage-option">
                                    <input type="radio" name="storage" id="storage-<?= sanitizeInput(strtolower($storage)) ?>" 
                                           value="<?= sanitizeInput($storage) ?>" class="storage-radio" 
                                           <?= $storage === $storage_options[0] ? 'checked' : '' ?>>
                                    <label for="storage-<?= sanitizeInput(strtolower($storage)) ?>" 
                                           class="storage-label">
                                        <?= sanitizeInput($storage) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Quantity</h6>
                        <div class="quantity-selector d-flex align-items-center">
    <button type="button" class="btn btn-outline-secondary quantity-minus">-</button>
    <input type="number" name="quantity" class="form-control text-center quantity-input" 
           value="1" min="1" max="10" required>
    <button type="button" class="btn btn-outline-secondary quantity-plus">+</button>
</div>
                    </div>

                    <div href="add_to_cart.php"class="d-grid gap-3 mb-5">
                        <button class="btn btn-accent btn-lg py-3 fw-bold" id="add-to-cart-btn">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline-accent btn-lg py-3 fw-bold" id="buy-now-btn">
                            <i class="bi bi-lightning me-2"></i> Buy Now
                        </button>
                    </div>

                    <div class="product-meta border-top pt-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>In Stock: <?= $product['stock_quantity'] ?> units</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-truck text-primary me-2"></i>
                            <span>Free shipping on orders over ₹5000</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-arrow-repeat text-info me-2"></i>
                            <span>30-day return policy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">Specifications</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">Reviews</button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                    <div class="tab-pane fade show active" id="specs" role="tabpanel">
                        <div class="row">
                            <?php foreach ($specs as $category => $items): ?>
                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-3"><?= htmlspecialchars($category) ?></h5>
                                    <table class="table table-sm">
                                        <tbody>
                                            <?php foreach ($items as $key => $value): ?>
                                                <tr>
                                                    <th width="40%"><?= htmlspecialchars($key) ?></th>
                                                    <td><?= htmlspecialchars($value) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="description" role="tabpanel">
                        <p><?= nl2br(htmlspecialchars($product['long_description'])) ?></p>
                    </div>
                    
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center mb-4">
                                    <div class="display-4 fw-bold mb-2">4.2</div>
                                    <div class="rating mb-3">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-half text-warning"></i>
                                    </div>
                                    <p class="text-muted">Based on 42 reviews</p>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Leave a Review</h6>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">Rating</label>
                                            <div class="rating-input">
                                                <i class="bi bi-star-fill" data-rating="1"></i>
                                                <i class="bi bi-star-fill" data-rating="2"></i>
                                                <i class="bi bi-star-fill" data-rating="3"></i>
                                                <i class="bi bi-star-fill" data-rating="4"></i>
                                                <i class="bi bi-star-fill" data-rating="5"></i>
                                                <input type="hidden" name="rating" id="rating-value" value="0">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="review-text" class="form-label">Review</label>
                                            <textarea class="form-control" id="review-text" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-accent">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="review-list">
                                    <div class="review mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="fw-bold mb-0">John D.</h6>
                                            <small class="text-muted">2 days ago</small>
                                        </div>
                                        <div class="rating small text-warning mb-2">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <p>This product exceeded my expectations. The build quality is excellent and it performs even better than advertised.</p>
                                    </div>
                                    
                                    <div class="review mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="fw-bold mb-0">Sarah M.</h6>
                                            <small class="text-muted">1 week ago</small>
                                        </div>
                                        <div class="rating small text-warning mb-2">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </div>
                                        <p>Great product overall, but the battery life could be better. Everything else works perfectly.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
    <div class="col-12">
        <h3 class="fw-bold mb-4">You May Also Like</h3>
        <div class="row g-4">
            <?php foreach ($related_products as $related_product): ?> 
                <div class="col-md-6 col-lg-3">
                    <?php 
                        $product = $related_product; 
                        include 'components/product-card.php'; 
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const mainImg = document.getElementById('main-product-image');
            mainImg.src = this.getAttribute('data-image');
            
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('selected-color').textContent = this.value;
        });
    });
    
    document.querySelectorAll('.storage-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('selected-storage').textContent = this.value;
        });
    });

    const quantityInput = document.querySelector('.quantity-input');
    document.querySelector('.quantity-minus').addEventListener('click', () => {
        if (quantityInput.value > 1) quantityInput.value--;
    });
    document.querySelector('.quantity-plus').addEventListener('click', () => {
        if (quantityInput.value < 10) quantityInput.value++;
    });

    document.getElementById('add-to-cart-btn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

        try {
            const response = await fetch('cart_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    product_id: <?= $product['id'] ?>,
                    quantity: document.querySelector('.quantity-input').value,
                    color: document.querySelector('input[name="color"]:checked')?.value || 'Default',
                    storage: document.querySelector('input[name="storage"]:checked')?.value || 'Default'
                })
            });

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message);
        }

        document.querySelectorAll('.cart-counter').forEach(el => {
            el.textContent = data.cartCount;
            el.classList.add('animate-bounce');
            setTimeout(() => el.classList.remove('animate-bounce'), 1000);
        });

        alert('Product added to cart!');
    } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i> Add to Cart';
        }
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast show position-fixed bottom-0 end-0 m-3 bg-${type}`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-white">${message}</div>
            <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

    document.querySelectorAll('.rating-input i').forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            document.getElementById('rating-value').value = rating;
            
            document.querySelectorAll('.rating-input i').forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('text-warning');
                } else {
                    s.classList.remove('text-warning');
                    s.classList.add('text-muted');
                }
            });
        });
    });
});
</script>