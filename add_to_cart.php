<?php
session_start();
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    if (!isset($input['product_id']) || !is_numeric($input['product_id'])) {
        throw new Exception("Invalid product ID", 400);
    }
    
    $product_id = (int)$input['product_id'];
    $color = sanitizeInput($input['color'] ?? 'Default');
    $storage = sanitizeInput($input['storage'] ?? 'Default');
    $quantity = max(1, min(10, (int)($input['quantity'] ?? 1)));

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found", 404);
    }

    $required = ['name', 'price', 'image_url'];
    foreach ($required as $field) {
        if (!isset($product[$field])) {
            throw new Exception("Missing product data: $field", 500);
        }
    }

    $_SESSION['cart'] ??= [];

    $item_key = "prod_{$product_id}_" . md5($color . '_' . $storage);

    if (isset($_SESSION['cart'][$item_key])) {
        $_SESSION['cart'][$item_key]['quantity'] = min(
            $_SESSION['cart'][$item_key]['quantity'] + $quantity,
            10
        );
    } else {
        $_SESSION['cart'][$item_key] = [
            'product_id' => (int)$product_id,
            'name'       => (string)$product['name'],
            'price'      => (float)$product['price'],
            'image'      => (string)$product['image_url'],
            'quantity'   => max(1, min(10, (int)$quantity)),
            'color'      => (string)sanitizeInput($color),
            'storage'    => (string)sanitizeInput($storage)
        ];
    }

    $cart_count = count($_SESSION['cart']);
    $cart_total = array_reduce($_SESSION['cart'], fn($t, $i) => $t + ($i['price'] * $i['quantity']), 0);

    echo json_encode([
        'success' => true,
        'cartCount' => $cart_count,
        'cartTotal' => $cart_total,
        'itemName' => $product['name']
    ]);

} catch (Exception $e) {

    unset($_SESSION['cart']);
    
    $_SESSION['checkout_error'] = [
        'message' => 'Invalid cart data - cart has been reset',
        'debug' => [
            'error' => $e->getMessage(),
            'cart_dump' => $_SESSION['cart'] ?? null
        ]
    ];
    
    header("Location: cart.php");
    exit();
}