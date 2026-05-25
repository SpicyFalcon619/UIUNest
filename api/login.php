<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $identifier = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR university_id = ?');
    $stmt->execute([$identifier, $identifier]);
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
        
        // Fetch preferences
        $prefStmt = $pdo->prepare('SELECT * FROM user_preferences WHERE user_id = ?');
        $prefStmt->execute([$user['user_id']]);
        $prefs = $prefStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prefs) {
            $user['sleep'] = $prefs['sleep_schedule'];
            $user['diet'] = $prefs['diet'];
            $user['guest'] = $prefs['guest_policy'];
            $user['smoking'] = (int)$prefs['smoking_tolerance'];
            $user['noise'] = $prefs['noise_tolerance'];
            $user['cleanliness'] = (int)$prefs['cleanliness_score'];
        } else {
            // Defaults if not set
            $user['sleep'] = 'flexible';
            $user['diet'] = 'non_veg';
            $user['guest'] = 'restricted';
            $user['smoking'] = 0;
            $user['noise'] = 'moderate';
            $user['cleanliness'] = 3;
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user'] = $user;
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email/ID or password.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
