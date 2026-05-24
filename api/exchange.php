<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT i.*, u.name as seller, z.zone_name as zone 
            FROM items i
            JOIN users u ON i.seller_id = u.user_id
            JOIN zones z ON i.zone_id = z.zone_id
            ORDER BY i.created_at DESC
        ");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map keys to match frontend expectations
        $mapped = array_map(function($it) {
            return [
                'id' => $it['item_id'],
                'title' => $it['title'],
                'category' => $it['category'],
                'condition' => $it['item_condition'],
                'price' => (float)$it['asking_price'],
                'zone' => $it['zone'],
                'zoneId' => $it['zone_id'],
                'linkedListingId' => $it['listing_id'],
                'description' => $it['description'],
                'photo' => $it['photo_url'] ?: 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=600',
                'seller' => $it['seller'],
                'seller_id' => $it['seller_id'],
                'status' => $it['status']
            ];
        }, $items);
        
        echo json_encode(['success' => true, 'items' => $mapped]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $asking_price = $_POST['price'] ?? 0;
    $zone_id = $_POST['zone_id'] ?? null;
    $listing_id = $_POST['linkedListingId'] ?? null;
    if ($listing_id === 'null' || $listing_id === '') $listing_id = null;
    $description = $_POST['description'] ?? '';
    $seller_id = $_SESSION['user_id'];
    
    // Default photo
    $photo_url = 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=600';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/exchange/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Clean filename
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['photo']['name']));
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photo_url = 'uploads/exchange/' . $fileName;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO items (seller_id, zone_id, listing_id, category, title, description, item_condition, asking_price, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $seller_id, $zone_id, $listing_id ?: null, $category, $title, $description, $condition, $asking_price, $photo_url
        ]);
        
        echo json_encode(['success' => true, 'item_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
