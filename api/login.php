<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] === 'suspended') {
            echo json_encode(['success' => false, 'error' => 'Account is suspended.']);
            exit;
        }
        // Exclude password hash from session/output
        unset($user['password_hash']);
        // Format to match JS prototype keys where needed, e.g. user_id -> id
        $user['id'] = $user['user_id'];
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user'] = $user;
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
