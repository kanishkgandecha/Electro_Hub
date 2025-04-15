<?php
session_start();
require_once 'functions.php';

if (!isAuthenticated()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized']);
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['username']) || empty($input['email'])) {
    jsonResponse(['success' => false, 'message' => 'Username and email are required']);
}

if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email format']);
}

$passwordChanged = !empty($input['new_password']);

if ($passwordChanged) {
    if ($input['new_password'] !== $input['confirm_new_password']) {
        jsonResponse(['success' => false, 'message' => 'New passwords do not match']);
    }
    
    if (empty($input['current_password'])) {
        jsonResponse(['success' => false, 'message' => 'Current password is required to change password']);
    }
}

try {
    $conn = connectDB();
    

    if ($passwordChanged) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($input['current_password'], $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Current password is incorrect']);
        }
    }
    

    if ($passwordChanged) {
        $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([
            $input['username'],
            $input['email'],
            $hashedPassword,
            $_SESSION['user_id']
        ]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([
            $input['username'],
            $input['email'],
            $_SESSION['user_id']
        ]);
    }

    $_SESSION['user_name'] = $input['username'];
    
    jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (PDOException $e) {
    error_log("Profile update error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred']);
}
?>