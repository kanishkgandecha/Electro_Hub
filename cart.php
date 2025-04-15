<?php
$page_title = "Shopping Cart";
session_start();
require_once 'functions.php';

$cartTotal = 0;
$cartCount = 0;
$cartItems = $_SESSION['cart'] ?? [];

if (!empty($cartItems)) {
    $cartTotal = array_reduce($cartItems, function ($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
    $cartCount = count($cartItems);
}

$discount = 0;
if (isset($_SESSION['applied_coupon'])) {
    $discount = $_SESSION['applied_coupon']['discount'];
}
?>
<?php include 'header.php'; ?>

<nav aria-label="breadcrumb" class="bg-darker py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </div>
</nav>

<section class="py-5 bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-dark mb-4">
                    <div class="card-header bg-dark-blue text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Your Cart</h4>
                            <span class="badge bg-accent rounded-pill"><?= $cartCount ?> items</span>
                        </div>
                    </div>
                    <div class="card-body bg-darker">
                        <?php if (empty($cartItems)): ?>
                            <div class="empty-cart text-center py-5">
                                <i class="bi bi-cart-x display-4 text-muted mb-4"></i>
                                <h4 class="mb-3">Your cart is empty</h4>
                                <p class="text-muted mb-4">Browse our products and add some items to your cart</p>
                                <a href="products.php" class="btn btn-accent px-4">Continue Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Details</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cartTotal = 0;
                                        foreach ($_SESSION['cart'] ?? [] as $key => $item):
                                            $subtotal = $item['price'] * $item['quantity'];
                                            $cartTotal += $subtotal;
                                        ?>
                                            <tr data-index="<?= htmlspecialchars($key) ?>">
                                                <td>
                                                    <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                        <div class="text-muted small">
                                                            ₹<?= number_format($item['price'], 2) ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($item['color'] !== 'Default'): ?>
                                                        <div><strong>Color:</strong> <?= htmlspecialchars($item['color']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($item['storage'] !== 'Default'): ?>
                                                        <div><strong>Storage:</strong> <?= htmlspecialchars($item['storage']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>₹<?= number_format($item['price'], 2) ?></td>
                                                <td>
                                                    <input type="number" class="form-control quantity-input" 
                                                           value="<?= $item['quantity'] ?>" min="1" max="10"
                                                           data-index="<?= htmlspecialchars($key) ?>">
                                                </td>
                                                <td>₹<?= number_format($subtotal, 2) ?></td>
                                                <td>
                                                    <button class="btn btn-outline-danger btn-sm remove-item" 
                                                            data-index="<?= htmlspecialchars($key) ?>" 
                                                            aria-label="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($cartItems)): ?>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <button class="btn btn-outline-danger" id="clear-cart-btn">
                            <i class="bi bi-trash me-2"></i>Clear Cart
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-sm border-dark">
                    <div class="card-header bg-dark-blue text-white py-3">
                        <h4 class="mb-0">Order Summary</h4>
                    </div>
                    <div class="card-body bg-darker text-light">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Have a coupon?</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code">
                                <button class="btn btn-accent" type="button" id="apply-coupon-btn">Apply</button>
                            </div>
                            <div id="coupon-message" class="small mt-2"></div>
                        </div>
                        <div class="border-top border-secondary pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>₹<span id="subtotal-amount"><?= number_format($cartTotal, 2) ?></span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>₹<span id="shipping-amount">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Discount</span>
                                <span class="text-danger">-₹<span id="discount-amount"><?= number_format($discount, 2) ?></span></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 border-top border-secondary pt-3">
                                <span>Total</span>
                                <span>₹<span id="total-amount"><?= number_format($cartTotal - $discount, 2) ?></span></span>
                            </div>
                        </div>
                        <a href="checkout.php" class="btn btn-accent w-100 mt-4 py-3 fw-bold" id="checkout-btn" <?= $cartCount === 0 ? 'disabled' : '' ?>>
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async function () {
            const newQuantity = parseInt(this.value);
            if (newQuantity < 1 || newQuantity > 10) {
                alert('Quantity must be between 1-10');
                this.value = this.dataset.oldValue;
                return;
            }
            const index = this.getAttribute('data-index');
            try {
                const response = await fetch('update_cart_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ index, quantity: newQuantity })
                });
                const result = await response.json();
                if (result.success) {
                    const row = this.closest('tr');
                    const price = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('₹', ''));
                    const subtotal = price * newQuantity;
                    row.querySelector('td:nth-child(5)').textContent = `₹${subtotal.toFixed(2)}`;
                    updateCartTotals(result.cartTotal);
                    showToast('Quantity updated', 'success');
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            }
        });
    });

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async function () {
            const index = this.getAttribute('data-index');
            const row = this.closest('tr');
            if (!confirm('Remove this item from cart?')) return;

            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
            this.disabled = true;

            try {
                const response = await fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ index })
                });
                const result = await response.json();

                if (result.success) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updateCartTotals(result.cartTotal);
                        updateCartCounter(result.cartCount);
                        showToast('Item removed from cart', 'success');

                        if (result.cartCount === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            } finally {
                this.innerHTML = '<i class="bi bi-trash"></i>';
                this.disabled = false;
            }
        });
    });

    document.getElementById('clear-cart-btn')?.addEventListener('click', function () {
        if (confirm('Are you sure you want to clear your cart?')) {
            fetch('clear_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    });

    document.getElementById('apply-coupon-btn')?.addEventListener('click', async function () {
        const couponCode = document.getElementById('coupon-code').value.trim();
        if (!couponCode) {
            showToast('Please enter a coupon code', 'warning');
            return;
        }

        const originalText = this.textContent;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Applying...';
        this.disabled = true;

        try {
            const response = await fetch('apply_coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ couponCode })
            });
            const result = await response.json();
            const couponMessage = document.getElementById('coupon-message');

            if (result.success) {
                couponMessage.textContent = result.message;
                couponMessage.className = 'small text-success mt-2';
                animateValue('discount-amount', parseFloat(document.getElementById('discount-amount').textContent), result.discount, 500);
                animateValue('total-amount', result.cartTotal, result.cartTotal - result.discount, 500);
                showToast('Coupon applied successfully!', 'success');
            } else {
                couponMessage.textContent = result.message;
                couponMessage.className = 'small text-danger mt-2';
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        } finally {
            this.textContent = originalText;
            this.disabled = false;
        }
    });

    function updateCartTotals(total) {
        animateValue('subtotal-amount', parseFloat(document.getElementById('subtotal-amount').textContent), total, 500);
        animateValue('total-amount', parseFloat(document.getElementById('total-amount').textContent), total, 500);
        const checkoutBtn = document.getElementById('checkout-btn');
        if (total <= 0) {
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('disabled');
        } else {
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('disabled');
        }
    }

    function animateValue(id, start, end, duration) {
        const element = document.getElementById(id);
        if (!element) return;

        const range = end - start;
        let current = start;
        const increment = end > start ? 1 : -1;
        const stepTime = Math.abs(Math.floor(duration / range));
        const timer = setInterval(() => {
            current += increment;
            element.textContent = current.toFixed(2);
            if (current === end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast show position-fixed bottom-0 end-0 mb-3 me-3 bg-${type}`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body text-white">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>

<?php include 'footer.php'; ?>