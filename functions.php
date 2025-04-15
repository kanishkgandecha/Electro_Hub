<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function redirectWithMessage($page, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $page");
    exit();
}

function displayFlashMessage() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!empty($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info'; 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        return "<div class='alert alert-$type'>$message</div>";
    }
    return '';
}

function sanitizeInput($data) {
    if (!isset($data) || $data === null) {
        return '';
    }
    return htmlspecialchars(stripslashes(trim($data)));
}

function connectDB() {
    $host = "localhost";
    $dbname = "electronics_store";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
            PDO::ATTR_EMULATE_PREPARES => false  
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}


function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function getColorHex($colorName) {
    $colorMap = [
        'Black' => '#000000',
        'White' => '#FFFFFF',
        'Silver' => '#C0C0C0',
        'Gold' => '#FFD700',
        'Space Gray' => '#657383',
        'Rose Gold' => '#B76E79',
        'Blue' => '#0000FF',
        'Red' => '#FF0000',
        'Green' => '#00FF00',
        'Yellow' => '#FFFF00',
        'Pink' => '#FFC0CB',
        'Midnight Green' => '#004953',
        'Purple' => '#800080',
        'Starlight' => '#F0F0F0',
        'Graphite' => '#41424C',
        'Sierra Blue' => '#69ABCE',
        'Alpine Green' => '#5F8D7F',
        'Default' => '#CCCCCC'
    ];
    
    return $colorMap[$colorName] ?? '#CCCCCC'; 
}

// Add to functions.php
function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
            'total' => 0,
            'count' => 0
        ];
    }
}

function addToCart($productId, $quantity = 1, $color = 'Default', $storage = 'Default') {
    initializeCart();
    
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return false;
    }

    $itemKey = md5("{$productId}_{$color}_{$storage}");

    if (isset($_SESSION['cart']['items'][$itemKey])) {
        $_SESSION['cart']['items'][$itemKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart']['items'][$itemKey] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image_url'],
            'quantity' => $quantity,
            'color' => $color,
            'storage' => $storage
        ];
    }

    updateCartTotals();
    return true;
}

function updateCartTotals() {
    $_SESSION['cart']['total'] = array_reduce($_SESSION['cart']['items'], function($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
    
    $_SESSION['cart']['count'] = count($_SESSION['cart']['items']);
}

function debugCart() {
    error_log("CART CONTENTS: " . print_r($_SESSION['cart'] ?? 'No cart', true));
}

function sanitizeCart(array &$cart) {
    foreach ($cart as $key => &$item) {
        $item = [
            'product_id' => (int)($item['product_id'] ?? 0),
            'name'       => (string)($item['name'] ?? 'Unknown Product'),
            'price'      => (float)($item['price'] ?? 0.0),
            'image'      => (string)($item['image'] ?? 'default.jpg'),
            'quantity'   => max(1, min(10, (int)($item['quantity'] ?? 1))),
            'color'      => (string)($item['color'] ?? 'Default'),
            'storage'    => (string)($item['storage'] ?? 'Default')
        ];

        if ($item['product_id'] < 1 || $item['price'] <= 0) {
            unset($cart[$key]);
        }
    }
    $cart = array_values($cart); 
}
?>


