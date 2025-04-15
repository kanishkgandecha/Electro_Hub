<?php
$page_title = "My Orders - ElectroHUT";
session_start();
require_once 'functions.php';

if (!isAuthenticated()) {
    redirectWithMessage('login.php', 'Please login to view your orders.', 'warning');
}

$conn = connectDB();
$stmt = $conn->prepare("
    SELECT 
        o.*, 
        COUNT(oi.id) AS item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<?php include 'header.php'; ?>

<section class="orders-page py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-accent mb-0">Order History</h1>
            <a href="index.php" class="btn btn-outline-accent">
                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
            </a>
        </div>

        <div class="card bg-darker border-dark">
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="empty-orders text-center py-5">
                        <i class="bi bi-box-seam display-4 text-muted mb-4"></i>
                        <h4 class="mb-3">No Orders Found</h4>
                        <p class="text-muted">Start shopping to see your orders here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover text-white align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                        
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= $order['item_count'] ?></td>
                                        <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] === 'completed' ? 'success' : 
                                                ($order['status'] === 'shipped' ? 'primary' :
                                                ($order['status'] === 'processing' ? 'warning' : 'secondary')) 
                                            ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>