<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['order_id']) || !isset($_SESSION['user_id'])) {
    redirectWithMessage('index.php', 'Invalid order confirmation request.', 'warning');
}

$orderId = $_SESSION['order_id'];

try {
    $conn = connectDB();
    
    $stmt = $conn->prepare("
        SELECT o.*, u.username 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        unset($_SESSION['order_id']);
        redirectWithMessage('index.php', 'Order not found.', 'danger');
    }

    $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $delivery = json_decode($order['delivery_info'], true) ?? [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        $delivery = [];
    }
    unset($_SESSION['order_id']);

} catch (PDOException $e) {
    error_log("Order Confirmation Error: " . $e->getMessage());
    redirectWithMessage('index.php', 'Failed to load order details.', 'danger');
}

$page_title = "Order Confirmation - ElectroHUT";
include 'header.php';
?>


<<section class="confirmation-section py-5 bg-darker">
    <div class="container">
        <div class="card border-accent">
            <div class="card-header bg-accent text-white">
                <h2 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Order Confirmed</h2>
            </div>
            
            <div class="card-body">
                <div class="alert alert-success">
                    <h4 class="alert-heading">Thank you for your order!</h4>
                    <p>Your order #<?= $orderId ?> has been placed successfully.</p>
                    <hr>
                    <p class="mb-0">We've sent a confirmation email to <?= htmlspecialchars($delivery['email'] ?? '') ?></p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h3 class="text-accent">Order Summary</h3>
                        <dl class="row">
                            <dt class="col-sm-4">Order Number:</dt>
                            <dd class="col-sm-8">#<?= $orderId ?></dd>
                            
                            <dt class="col-sm-4">Date:</dt>
                            <dd class="col-sm-8"><?= date('F j, Y H:i', strtotime($order['created_at'])) ?></dd>
                            
                            <dt class="col-sm-4">Total:</dt>
                            <dd class="col-sm-8">₹<?= number_format($order['total_amount'], 2) ?></dd>
                            
                            <dt class="col-sm-4">Payment Method:</dt>
                            <dd class="col-sm-8"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></dd>
                            
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-<?= 
                                    $order['payment_status'] === 'completed' ? 'success' : 'warning'
                                ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            </dd>
                        </dl>
                    </div>
                    
                    <<div class="col-md-6">
                    <h3 class="text-accent">Shipping Information</h3>
                    <address>
                        <strong><?= htmlspecialchars(($delivery['first_name'] ?? '') . ' ' . ($delivery['last_name'] ?? '')) ?></strong><br>
                        <?= htmlspecialchars($delivery['address'] ?? '') ?><br>
                        <?= htmlspecialchars($delivery['city'] ?? '') ?>, <?= htmlspecialchars($delivery['state'] ?? '') ?><br>
                        <?= htmlspecialchars($delivery['postal_code'] ?? '') ?><br>
                        <?= htmlspecialchars($delivery['country'] ?? '') ?><br>
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($delivery['phone'] ?? '') ?>
                    </address>
                </div>

                <hr class="my-4 border-accent">

                <h3 class="text-accent mb-4">Order Items</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-dark-blue text-white">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['product_name']) ?>
                                        <?php if ($item['color']): ?>
                                            <div class="text-muted small">Color: <?= $item['color'] ?></div>
                                        <?php endif; ?>
                                        <?php if ($item['storage']): ?>
                                            <div class="text-muted small">Storage: <?= $item['storage'] ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="3">Subtotal</th>
                                <th>₹<?= number_format($order['total_amount'], 2) ?></th>
                            </tr>
                            <tr>
                                <th colspan="3">Shipping</th>
                                <th>₹0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>₹<?= number_format($order['total_amount'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-darker">
                <div class="d-flex justify-content-between">
                    <a href="products.php" class="btn btn-outline-accent">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <a href="orders.php" class="btn btn-accent">
                        <i class="bi bi-list-check me-2"></i>View Order History
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>