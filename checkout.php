<?php
$page_title = "Checkout - ElectroHUT";
session_start();
require_once 'functions.php';

if (isset($_SESSION['checkout_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['checkout_error'].'</div>';
    unset($_SESSION['checkout_error']);
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cartTotal = array_reduce($_SESSION['cart'], function($total, $item) {
    return $total + ($item['price'] * $item['quantity']);
}, 0);

$cartCount = count($_SESSION['cart']);


$user = null;
if (isAuthenticated()) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'header.php';
?>

<section class="checkout-section py-5 bg-darker">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-accent mb-4">
                    <div class="card-header bg-accent text-dark">
                        <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Checkout</h4>
                    </div>
                    
                    <div class="card-body bg-darker">
                        <form id="checkout-form" action="process_payment.php" method="post" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <h5 class="mb-3 text-white">Shipping Information</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name*</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= $user ? htmlspecialchars($user['first_name'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter your first name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name*</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= $user ? htmlspecialchars($user['last_name'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter your last name.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="email" class="form-label">Email*</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= $user ? htmlspecialchars($user['email'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="phone" class="form-label">Phone*</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= $user ? htmlspecialchars($user['phone'] ?? '') : '' ?>" 
                                           pattern="\+?[0-9]{10,15}" required>
                                    <small class="text-muted">Format: 10-15 digits, may include '+' prefix</small>
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Address*</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required><?= $user ? htmlspecialchars($user['address'] ?? '') : '' ?></textarea>
                                    <div class="invalid-feedback">Please enter your address.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="city" class="form-label">City*</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= $user ? htmlspecialchars($user['city'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter your city.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="state" class="form-label">State*</label>
                                    <input type="text" class="form-control" id="state" name="state" 
                                           value="<?= $user ? htmlspecialchars($user['state'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter your state/province.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="postal_code" class="form-label">Postal Code*</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= $user ? htmlspecialchars($user['postal_code'] ?? '') : '' ?>" required>
                                    <div class="invalid-feedback">Please enter your postal/ZIP code.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="country" class="form-label">Country*</label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="India" <?= ($user && ($user['country'] ?? '') === 'India') ? 'selected' : '' ?>>India</option>
                                        <option value="United States" <?= ($user && ($user['country'] ?? '') === 'United States') ? 'selected' : '' ?>>United States</option>
                                        <option value="United Kingdom" <?= ($user && ($user['country'] ?? '') === 'United Kingdom') ? 'selected' : '' ?>>United Kingdom</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your country.</div>
                                </div>
                            </div>
                            
                            <hr class="my-4 border-accent">
                            
                            <h5 class="mb-3 text-white">Payment Method</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card payment-option bg-darker border-secondary">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit-card" value="credit_card" checked required>
                                        <label class="form-check-label" for="credit-card">
                                            <i class="bi bi-credit-card fs-4 me-2"></i> Credit/Debit Card
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card payment-option bg-darker border-secondary">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required>
                                        <label class="form-check-label" for="paypal">
                                            <i class="bi bi-paypal fs-4 me-2"></i> PayPal
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card payment-option bg-darker border-secondary">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" required>
                                        <label class="form-check-label" for="cod">
                                            <i class="bi bi-cash fs-4 me-2"></i> Cash on Delivery
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card payment-option bg-darker border-secondary">
                                        <input class="form-check-input" type="radio" name="payment_method" id="upi" value="upi" required>
                                        <label class="form-check-label" for="upi">
                                            <i class="bi bi-phone fs-4 me-2"></i> UPI Payment
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="credit-card-fields" class="mt-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="card-number" class="form-label">Card Number*</label>
                                        <input type="text" class="form-control" id="card-number" name="card_number" 
                                               placeholder="1234 5678 9012 3456" data-payment-required="credit_card">
                                        <div class="invalid-feedback">Please enter your card number.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="card-expiry" class="form-label">Expiry Date*</label>
                                        <input type="text" class="form-control" id="card-expiry" name="card_expiry" 
                                               placeholder="MM/YY" data-payment-required="credit_card">
                                        <div class="invalid-feedback">Please enter card expiry date.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="card-cvc" class="form-label">CVC*</label>
                                        <input type="text" class="form-control" id="card-cvc" name="card_cvc" 
                                               placeholder="123" data-payment-required="credit_card">
                                        <div class="invalid-feedback">Please enter CVC code.</div>
                                    </div>
                                    <div class="col-12">
                                        <label for="card-name" class="form-label">Name on Card*</label>
                                        <input type="text" class="form-control" id="card-name" name="card_name" data-payment-required="credit_card">
                                        <div class="invalid-feedback">Please enter name on card.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="upi-fields" class="mt-4" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="upi-id" class="form-label">UPI ID*</label>
                                        <input type="text" class="form-control" id="upi-id" name="upi_id" 
                                               placeholder="yourname@upi" data-payment-required="upi">
                                        <div class="invalid-feedback">Please enter your UPI ID.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4 border-accent">
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="save-info" name="save_info" checked>
                                <label class="form-check-label" for="save-info">Save this information for next time</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="cart.php" class="btn btn-outline-accent">
                                    <i class="bi bi-arrow-left me-2"></i> Back to Cart
                                </a>
                                <button type="submit" class="btn btn-accent px-4">
                                    <i class="bi bi-lock me-2"></i> Complete Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-accent">
                    <div class="card-header bg-accent text-dark">
                        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h4>
                    </div>
                    
                    <div class="card-body bg-darker">
                        <div class="order-items mb-3">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-secondary">
        <div>
            <span class="fw-bold"><?= htmlspecialchars($item['name']) ?></span>
            <small class="d-block text-muted">Qty: <?= $item['quantity'] ?></small>
            <?php if ($item['color'] !== 'Default'): ?>
                <small class="d-block text-muted">Color: <?= htmlspecialchars($item['color']) ?></small>
            <?php endif; ?>
            <?php if ($item['storage'] !== 'Default'): ?>
                <small class="d-block text-muted">Storage: <?= htmlspecialchars($item['storage']) ?></small>
            <?php endif; ?>
        </div>
        <div>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
    </div>
<?php endforeach; ?>
                        </div>
                        
                        <div class="border-top border-secondary pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>₹0.00</span>
                            </div>
                            
                            <div class="d-flex justify-content-between fw-bold fs-5 border-top border-secondary pt-3">
                                <span>Total</span>
                                <span>₹<?= number_format($cartTotal, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardFields = document.getElementById('credit-card-fields');
    const upiFields = document.getElementById('upi-fields');

    function togglePaymentFields() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        creditCardFields.style.display = selectedMethod === 'credit_card' ? 'block' : 'none';
        upiFields.style.display = selectedMethod === 'upi' ? 'block' : 'none';
    }
    
    paymentMethods.forEach(method => method.addEventListener('change', togglePaymentFields));
    togglePaymentFields();

    document.getElementById('checkout-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

        try {
            const response = await fetch('process_payment.php', {
                method: 'POST',
                body: new FormData(e.target),
                redirect: 'manual'
            });

            if (response.type === 'opaqueredirect') {
                window.location.href = 'order-confirmation.php';
            } else {
                const result = await response.text();
                throw new Error('Payment failed');
            }
        } catch (error) {
            window.location.reload(); 
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-lock me-2"></i> Complete Order';
        }
    });
});
</script>

<?php include 'footer.php'; ?>