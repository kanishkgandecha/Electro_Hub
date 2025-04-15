<?php
session_start();
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['itemKey']) || !isset($input['quantity'])) {
        throw new Exception('Missing parameters', 400);
    }

    $quantity = max(1, min(10, (int)$input['quantity']));

    if (!isset($_SESSION['cart'][$input['itemKey']])) {
        throw new Exception('Item not found in cart', 404);
    }

    $_SESSION['cart'][$input['itemKey']]['quantity'] = $quantity;

    $cartTotal = 0;
    $cartCount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
        $cartCount += $item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'cartTotal' => $cartTotal,
        'cartCount' => $cartCount
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}