<?php
ob_start();
session_start();
require_once 'functions.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentication required', 401);
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        throw new Exception('Invalid cart data structure');
    }

    sanitizeCart($_SESSION['cart']);
    
    if (empty($_SESSION['cart'])) {
        throw new Exception('Cart became empty after sanitization');
    }
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid security token', 403);
    }

    $required = [
        'first_name', 'last_name', 'email', 'phone',
        'address', 'city', 'state', 'postal_code', 'country',
        'payment_method'
    ];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field", 400);
        }
    }

    $conn = connectDB();
    $conn->beginTransaction();

    $deliveryInfo = [
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name' => sanitizeInput($_POST['last_name']),
        'email' => sanitizeInput($_POST['email']),
        'phone' => sanitizeInput($_POST['phone']),
        'address' => sanitizeInput($_POST['address']),
        'city' => sanitizeInput($_POST['city']),
        'state' => sanitizeInput($_POST['state']),
        'postal_code' => sanitizeInput($_POST['postal_code']),
        'country' => sanitizeInput($_POST['country'])
    ];

    $total = array_reduce($_SESSION['cart'], function($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);

    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, total_amount, payment_method,
            delivery_info, payment_status, item_count
        ) VALUES (
            :user_id, :total, :method,
            :delivery, :status, :count
        )
    ");
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':total' => $total,
        ':method' => sanitizeInput($_POST['payment_method']),
        ':delivery' => json_encode($deliveryInfo),
        ':status' => 'pending',
        ':count' => count($_SESSION['cart'])
    ]);

    $orderId = $conn->lastInsertId();

    $itemStmt = $conn->prepare("
        INSERT INTO order_items (
            order_id, product_id, name,
            quantity, price, color, storage, image
        ) VALUES (
            :order_id, :product_id, :name,
            :quantity, :price, :color, :storage, :image
        )
    ");

    foreach ($_SESSION['cart'] as $key => $item) {
        $requiredKeys = [
            'product_id' => 'int',
            'name' => 'string',
            'price' => 'float',
            'quantity' => 'int',
            'color' => 'string',
            'storage' => 'string',
            'image' => 'string'
        ];
        foreach ($requiredKeys as $reqKey => $type) {
            if (!isset($item[$reqKey])) {
                throw new Exception("Invalid cart item ($key): Missing $reqKey");
            }
    
            switch ($type) {
                case 'int':
                    if (!is_int($item[$reqKey])) {
                        throw new Exception("Invalid type for $reqKey in item $key");
                    }
                    break;
                case 'float':
                    if (!is_float($item[$reqKey]) && !is_numeric($item[$reqKey])) {
                        throw new Exception("Invalid type for $reqKey in item $key");
                    }
                    break;
                case 'string':
                    if (!is_string($item[$reqKey])) {
                        throw new Exception("Invalid type for $reqKey in item $key");
                    }
                    break;
            }
        }

        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':name' => $item['name'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price'],
            ':color' => $item['color'],
            ':storage' => $item['storage'],
            ':image' => $item['image']
        ]);
    }

    if (isset($_POST['save_info']) && $_POST['save_info'] === 'on') {
        $updateStmt = $conn->prepare("
        UPDATE users SET
            first_name = :fname,
            last_name = :lname,
            phone = :phone,
            address = :address,
            city = :city,
            state = :state,
            postal_code = :zip,
            country = :country
        WHERE id = :uid
    ");
    
    $updateStmt->execute([
        ':fname' => sanitizeInput($_POST['first_name']),
        ':lname' => sanitizeInput($_POST['last_name']),
        ':phone' => sanitizeInput($_POST['phone']),
        ':address' => sanitizeInput($_POST['address']),
        ':city' => sanitizeInput($_POST['city']),
        ':state' => sanitizeInput($_POST['state']),
        ':zip' => sanitizeInput($_POST['postal_code']),
        ':country' => sanitizeInput($_POST['country']),
        ':uid' => $_SESSION['user_id']
    ]);
    }

    $conn->commit();

    unset($_SESSION['cart']);
    $_SESSION['order_id'] = $orderId;

    ob_end_clean();
    header("Location: order-confirmation.php");
    exit();

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['checkout_error'] = $e->getMessage();

    ob_end_clean();
    header("Location: checkout.php");
    exit();
}