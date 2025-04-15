
<div class="product-card card border-0 h-100">
    <div class="badge-container position-absolute top-0 end-0 p-2">
        <?php if ($product['discount'] > 0): ?>
            <span class="badge bg-danger">-<?= $product['discount'] ?>%</span>
        <?php endif; ?>
        <?php if ($product['is_new']): ?>
            <span class="badge bg-success">New</span>
        <?php endif; ?>
    </div>
    
    <div class="product-img-container">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
        <div class="product-actions">
            <button class="btn btn-sm btn-accent rounded-circle quick-view" data-product-id="<?= $product['id'] ?>">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-accent rounded-circle add-to-wishlist" data-product-id="<?= $product['id'] ?>">
                <i class="bi bi-heart"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0"><?= htmlspecialchars($product['name']) ?></h5>
            <div class="rating small text-warning">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-half"></i>
            </div>
        </div>
        
        <p class="text-muted small mb-2"><?= htmlspecialchars(substr($product['description'], 0, 60)) ?>...</p>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <?php if ($product['discount'] > 0): ?>
                    <span class="text-danger fw-bold">₹<?= number_format($product['price'] * (1 - $product['discount']/100), 2) ?></span>
                    <span class="text-decoration-line-through text-muted small ms-2">₹<?= number_format($product['price'], 2) ?></span>
                <?php else: ?>
                    <span class="fw-bold">₹<?= number_format($product['price'], 2) ?></span>
                <?php endif; ?>
            </div>
            <button class="btn btn-sm btn-accent add-to-cart" 
                    data-product-id="<?= $product['id'] ?>"
                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                    data-product-price="<?= $product['price'] ?>">
                <i class="bi bi-cart-plus"></i>
            </button>
        </div>
    </div>
    <a href="product.php?id=<?= $product['id'] ?>" class="stretched-link"></a>
</div>