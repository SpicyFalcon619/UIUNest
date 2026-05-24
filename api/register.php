<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'student';
    $gender = $input['gender'] ?? 'other';
    $uid = $input['universityId'] ?? null;
    
    // Validate
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, gender, university_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, $role, $gender, $uid]);
        $newId = $pdo->lastInsertId();
        
        $user = [
            'id' => $newId,
            'user_id' => $newId,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'gender' => $gender,
            'university_id' => $uid,
            'status' => 'active'
        ];
        
        $_SESSION['user_id'] = $newId;
        $_SESSION['user'] = $user;
        
        echo json_encode(['success' => true, 'user' => $user]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error.']);
        }
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
