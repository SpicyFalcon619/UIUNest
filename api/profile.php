<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // Fetch user basic info
        $stmt = $pdo->prepare("SELECT name, email, phone, university_id, gender FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch preferences
        $prefStmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $prefStmt->execute([$user_id]);
        $prefs = $prefStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'preferences' => $prefs ?: null
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? '';

    try {
        if ($type === 'basic') {
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $uni_id = trim($input['university_id'] ?? '');

            if (!$name || !$email) {
                echo json_encode(['success' => false, 'error' => 'Name and email are required']);
                exit;
            }

            // Check if email exists for other users
            $emailChk = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $emailChk->execute([$email, $user_id]);
            if ($emailChk->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Email is already taken by another account']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, university_id = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $phone, $uni_id, $user_id]);
            
            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;

            echo json_encode(['success' => true]);

        } elseif ($type === 'prefs') {
            $sleep = $input['sleep_schedule'] ?? 'flexible';
            $study = (int)($input['study_hours'] ?? 0);
            $diet = $input['diet'] ?? 'non_veg';
            $guest = $input['guest_policy'] ?? 'restricted';
            $smoking = (int)($input['smoking_tolerance'] ?? 0);
            $pref_gender = $input['preferred_gender'] ?? 'any';
            $cleanliness = (int)($input['cleanliness_score'] ?? 3);
            $noise = $input['noise_tolerance'] ?? 'moderate';

            // Check if prefs exist
            $chk = $pdo->prepare("SELECT pref_id FROM user_preferences WHERE user_id = ?");
            $chk->execute([$user_id]);
            
            if ($chk->fetch()) {
                $stmt = $pdo->prepare("UPDATE user_preferences SET sleep_schedule=?, study_hours=?, diet=?, guest_policy=?, smoking_tolerance=?, preferred_gender=?, cleanliness_score=?, noise_tolerance=? WHERE user_id=?");
                $stmt->execute([$sleep, $study, $diet, $guest, $smoking, $pref_gender, $cleanliness, $noise, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, sleep_schedule, study_hours, diet, guest_policy, smoking_tolerance, preferred_gender, cleanliness_score, noise_tolerance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $sleep, $study, $diet, $guest, $smoking, $pref_gender, $cleanliness, $noise]);
            }
            
            // Update session data so match score is recalculated instantly
            $_SESSION['user']['sleep'] = $sleep;
            $_SESSION['user']['diet'] = $diet;
            $_SESSION['user']['guest'] = $guest;
            $_SESSION['user']['smoking'] = $smoking;
            $_SESSION['user']['noise'] = $noise;
            $_SESSION['user']['cleanliness'] = $cleanliness;

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid update type']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
