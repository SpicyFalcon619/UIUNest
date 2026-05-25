<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT i.*, u.name as seller, z.zone_name as zone 
            FROM items i
            LEFT JOIN users u ON i.seller_id = u.user_id
            LEFT JOIN zones z ON i.zone_id = z.zone_id
            ORDER BY i.created_at DESC
        ");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map keys to match frontend expectations
        $mapped = array_map(function($it) {
            $photos = !empty($it['photos']) ? json_decode($it['photos'], true) : [];
            $mainPhoto = !empty($photos) ? $photos[0] : (!empty($it['photo_url']) ? $it['photo_url'] : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400"><rect width="600" height="400" fill="%23e2e8f0"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="%2394a3b8" text-anchor="middle" dominant-baseline="middle">No Photo Available</text></svg>');
            return [
                'id' => $it['item_id'] ?? null,
                'title' => $it['title'] ?? '',
                'category' => $it['category'] ?? '',
                'condition' => $it['item_condition'] ?? '',
                'price' => isset($it['asking_price']) ? (float)$it['asking_price'] : 0,
                'zone' => $it['zone'] ?? '',
                'zoneId' => $it['zone_id'] ?? null,
                'linkedListingId' => $it['listing_id'] ?? null,
                'description' => $it['description'] ?? '',
                'photo' => $mainPhoto,
                'photos' => $photos,
                'seller' => $it['seller'] ?? '',
                'seller_id' => $it['seller_id'] ?? null,
                'status' => $it['status'] ?? 'available'
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
    $photo_url = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400"><rect width="600" height="400" fill="%23e2e8f0"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="%2394a3b8" text-anchor="middle" dominant-baseline="middle">No Photo Available</text></svg>';
    $photos_json = '[]';

    if (isset($_POST['photos'])) {
        $photos_arr = json_decode($_POST['photos'], true);
        if (is_array($photos_arr) && count($photos_arr) > 0) {
            $photo_url = $photos_arr[0];
            $photos_json = $_POST['photos'];
        }
    } else {
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
                $photos_json = json_encode([$photo_url]);
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO items (seller_id, zone_id, listing_id, category, title, description, item_condition, asking_price, photo_url, photos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $seller_id, $zone_id, $listing_id ?: null, $category, $title, $description, $condition, $asking_price, $photo_url, $photos_json
        ]);
        
        echo json_encode(['success' => true, 'item_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing ID']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE items SET title = ?, asking_price = ?, item_condition = ? WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([
            trim($input['title']),
            (float)$input['price'],
            $input['condition'],
            $input['id'],
            $_SESSION['user_id']
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing ID']);
        exit;
    }
    
    try {
        // Also delete associated offers or let cascade handle it?
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([$input['id'], $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
