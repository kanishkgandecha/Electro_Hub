<?php
session_start();
require_once 'functions.php';

if (!isAuthenticated()) {
    die('Unauthorized');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    die('Invalid order ID');
}

try {
    $conn = connectDB();

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die('Order not found');
    }

    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $delivery_info = json_decode($order['delivery_info'], true);

    ?>
    <div class="row">
        <div class="col-md-6">
            <h6>Order Summary</h6>
            <table class="table table-sm">
                <tr>
                    <th>Order ID:</th>
                    <td><?= $order['id'] ?></td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge bg-<?= 
                            $order['status'] === 'completed' ? 'success' : 
                            ($order['status'] === 'processing' ? 'warning' : 'secondary') 
                        ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Payment Method:</th>
                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </table>
            
            <h6 class="mt-4">Delivery Information</h6>
            <address>
                <strong><?= htmlspecialchars($delivery_info['name']) ?></strong><br>
                <?= htmlspecialchars($delivery_info['address']) ?><br>
                Phone: <?= htmlspecialchars($delivery_info['phone']) ?>
            </address>
        </div>
        
        <div class="col-md-6">
            <h6>Order Items</h6>
            <div class="list-group">
                <?php foreach ($items as $item): ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                                <div class="mt-1">₹<?= number_format($item['price'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading order details.</div>';
}
?>