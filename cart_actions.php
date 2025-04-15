<?php
session_start();
require 'functions.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (!isset($input['action'])) {
        throw new Exception('No action specified', 400);
    }

    switch ($input['action']) {

        case 'add':
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

            $itemKey = "prod_{$product_id}_" . md5($color . '_' . $storage);

            $_SESSION['cart'] ??= [];
            if (isset($_SESSION['cart'][$itemKey])) {
                $_SESSION['cart'][$itemKey]['quantity'] = min(
                    $_SESSION['cart'][$itemKey]['quantity'] + $quantity,
                    10
                );
            } else {
                $_SESSION['cart'][$itemKey] = [
                    'product_id' => $product_id,
                    'name'      => $product['name'],
                    'price'     => (float)$product['price'],
                    'image'     => $product['image_url'],
                    'quantity'  => $quantity,
                    'color'     => $color,
                    'storage'   => $storage
                ];
            }

            $cartTotal = array_reduce($_SESSION['cart'], fn($t, $i) => $t + ($i['price'] * $i['quantity']), 0);
            
            echo json_encode([
                'success'    => true,
                'cartCount'  => count($_SESSION['cart']),
                'cartTotal'  => $cartTotal,
                'itemName'   => $product['name']
            ]);
            break;

        case 'update':
            if (!isset($input['itemKey'], $input['quantity'])) {
                throw new Exception('Missing parameters', 400);
            }
            
            $quantity = (int)$input['quantity'];
            if ($quantity < 1 || $quantity > 10) {
                throw new Exception('Invalid quantity (1-10 allowed)', 400);
            }

            if (isset($_SESSION['cart'][$input['itemKey']])) {
                $_SESSION['cart'][$input['itemKey']]['quantity'] = $quantity;
                $cartTotal = array_reduce($_SESSION['cart'], fn($t, $i) => $t + ($i['price'] * $i['quantity']), 0);
                echo json_encode([
                    'success'    => true,
                    'cartTotal'  => $cartTotal,
                    'message'   => 'Quantity updated'
                ]);
            } else {
                throw new Exception('Item not found in cart', 404);
            }
            break;

        case 'remove':
            if (!isset($input['itemKey'])) {
                throw new Exception('Missing item key', 400);
            }

            if (!isset($_SESSION['cart'][$input['itemKey']])) {
                throw new Exception('Item not found in cart', 404);
            }

            unset($_SESSION['cart'][$input['itemKey']]);

            $cartTotal = array_reduce($_SESSION['cart'], fn($t, $i) => $t + ($i['price'] * $i['quantity']), 0);
            $cartCount = count($_SESSION['cart']);

            echo json_encode([
                'success'    => true,
                'cartTotal'  => $cartTotal,
                'cartCount'  => $cartCount
            ]);
            break;

        default:
            throw new Exception('Invalid action', 400);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug'   => ($input['action'] === 'add') ? ['productId' => $product_id ?? null] : []
    ]);
}