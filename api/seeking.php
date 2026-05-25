<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT s.*, u.name as user_name, u.gender as user_gender, z.zone_name as zone 
            FROM seeking_posts s
            JOIN users u ON s.user_id = u.user_id
            JOIN zones z ON s.zone_id = z.zone_id
            WHERE s.status = 'active'
            ORDER BY s.created_at DESC
        ");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map keys to match frontend expectations
        $mapped = array_map(function($p) {
            return [
                'id' => $p['post_id'],
                'zone' => $p['zone'],
                'zoneId' => $p['zone_id'],
                'propertyType' => $p['property_type'],
                'genderPref' => $p['preferred_gender'],
                'budgetMin' => (float)$p['budget_min'],
                'budgetMax' => (float)$p['budget_max'],
                'moveInDate' => $p['move_in_date'],
                'requirements' => $p['requirements'],
                'user' => $p['user_name'],
                'user_gender' => $p['user_gender'],
                'status' => $p['status']
            ];
        }, $posts);
        
        echo json_encode(['success' => true, 'posts' => $mapped]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $zone_id = $input['zone_id'] ?? null;
    $property_type = $input['propertyType'] ?? '';
    $gender_pref = $_SESSION['user']['gender'];
    $budget_min = $input['budgetMin'] ?? 0;
    $budget_max = $input['budgetMax'] ?? 0;
    $move_in_date = $input['moveInDate'] ?? null;
    $requirements = $input['requirements'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO seeking_posts (user_id, zone_id, budget_min, budget_max, property_type, preferred_gender, move_in_date, requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, $zone_id, $budget_min, $budget_max, $property_type, $gender_pref, $move_in_date, $requirements
        ]);
        
        echo json_encode(['success' => true, 'post_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
