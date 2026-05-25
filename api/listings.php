<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $where = "";
    $params = [];
    if (isset($_GET['id'])) {
        $where = "WHERE l.listing_id = ?";
        $params[] = $_GET['id'];
    }
    // Fetch all listings with basic info
    $stmt = $pdo->prepare("
        SELECT l.*, z.zone_name as zone, u.name as owner_name, u.email as owner_email,
               up.sleep_schedule as sleep, up.diet, up.guest_policy as guest, 
               up.smoking_tolerance as smoking, up.noise_tolerance as noise, up.cleanliness_score as cleanliness
        FROM listings l
        JOIN zones z ON l.zone_id = z.zone_id
        JOIN users u ON l.user_id = u.user_id
        LEFT JOIN user_preferences up ON l.user_id = up.user_id
        $where
    ");
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
    
    // Fetch costs
    $costStmt = $pdo->query("SELECT * FROM utility_costs");
    $costsList = $costStmt->fetchAll();
    $costsByListing = [];
    foreach($costsList as $c) {
        $costsByListing[$c['listing_id']] = [
            'baseRent' => (int)$c['base_rent'],
            'electricityType' => $c['electricity_type'],
            'electricityAmount' => (int)$c['electricity_amount'],
            'gasBill' => (int)$c['gas_bill'],
            'waterBill' => (int)$c['water_bill'],
            'internetCost' => (int)$c['internet_cost'],
            'maintenanceFee' => (int)$c['maintenance_fee'],
            'caretakerFee' => (int)$c['caretaker_fee'],
            'totalMonthly' => (int)$c['total_monthly']
        ];
    }
    
    // Fetch amenities
    $amenStmt = $pdo->query("SELECT * FROM listing_amenities");
    $amenList = $amenStmt->fetchAll();
    $amenByListing = [];
    foreach($amenList as $a) {
        $amenByListing[$a['listing_id']] = [
            'attachedBathroom' => (bool)$a['attached_bathroom'],
            'attachedKitchen' => (bool)$a['attached_kitchen'],
            'isFurnished' => (bool)$a['is_furnished'],
            'rooftopAccess' => (bool)$a['rooftop_access'],
            'parking' => (bool)$a['parking'],
            'powerBackup' => (bool)$a['power_backup'],
            'liftAccess' => (bool)$a['lift_access']
        ];
    }
    
    // Combine
    $result = [];
    foreach($listings as $l) {
        $id = $l['listing_id'];
        $result[] = [
            'id' => $id,
            'title' => $l['title'],
            'zoneId' => (int)$l['zone_id'],
            'zone' => $l['zone'],
            'propertyType' => $l['property_type'],
            'totalRooms' => (int)$l['total_rooms'],
            'currentOccupancy' => (int)$l['current_occupancy'],
            'genderPref' => $l['gender_pref'],
            'address' => $l['address'],
            'isVerified' => (bool)$l['is_verified'],
            'status' => $l['status'],
            'listingType' => $l['listing_type'] === 'full_property' ? 'Landlord Listed' : 'Student Listed',
            'lat' => (float)$l['lat'],
            'lng' => (float)$l['lng'],
            'ownerEmail' => $l['owner_email'],
            'sleep' => $l['sleep'] ?? 'flexible',
            'diet' => $l['diet'] ?? 'non_veg',
            'guest' => $l['guest'] ?? 'restricted',
            'smoking' => isset($l['smoking']) ? (int)$l['smoking'] : 0,
            'noise' => $l['noise'] ?? 'moderate',
            'cleanliness' => isset($l['cleanliness']) ? (int)$l['cleanliness'] : 3,
            'costs' => $costsByListing[$id] ?? null,
            'amenities' => $amenByListing[$id] ?? null,
            'compositeScore' => 4.5, // placeholder
            'reviewCount' => 0,
            'description' => $l['description'],
            'photos' => !empty($l['photos']) ? json_decode($l['photos'], true) : ['https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600']
        ];
    }
    
    echo json_encode(['success' => true, 'listings' => $result]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CREATE new listing
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user']['id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO listings (user_id, zone_id, listing_type, property_type, title, address, lat, lng, gender_pref, total_rooms, current_occupancy, status, is_verified, description, photos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?, ?)");
        
        $lType = $_SESSION['user']['role'] === 'landlord' ? 'full_property' : 'peer_listing';
        $isVerified = $_SESSION['user']['role'] === 'admin' ? 1 : 0; 
        
        $stmt->execute([
            $userId, 
            $input['zoneId'], 
            $lType, 
            $input['propertyType'], 
            $input['title'], 
            $input['address'], 
            $input['lat'], 
            $input['lng'], 
            $_SESSION['user']['gender'], 
            $input['totalRooms'], 
            $input['currentOccupancy'], 
            $isVerified,
            $input['description'] ?? null,
            isset($input['photos']) && is_array($input['photos']) ? json_encode($input['photos']) : null
        ]);
        $listingId = $pdo->lastInsertId();
        
        // Insert costs
        $c = $input['costs'];
        $cStmt = $pdo->prepare("INSERT INTO utility_costs (listing_id, base_rent, electricity_amount, electricity_type, gas_bill, water_bill, internet_cost, maintenance_fee, caretaker_fee, total_monthly) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cStmt->execute([
            $listingId, $c['baseRent'], $c['electricityAmount'], $c['electricityType'], $c['gasBill'], $c['waterBill'], $c['internetCost'], $c['maintenanceFee'] ?? 0, $c['caretakerFee'] ?? 0, $c['totalMonthly']
        ]);
        
        // Insert amenities
        $a = $input['amenities'];
        $aStmt = $pdo->prepare("INSERT INTO listing_amenities (listing_id, attached_bathroom, attached_kitchen, is_furnished, rooftop_access, parking, power_backup, lift_access) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $aStmt->execute([
            $listingId, 
            !empty($a['attachedBathroom'])?1:0, 
            !empty($a['attachedKitchen'])?1:0, 
            !empty($a['isFurnished'])?1:0, 
            !empty($a['rooftopAccess'])?1:0, 
            !empty($a['parking'])?1:0, 
            !empty($a['powerBackup'])?1:0, 
            !empty($a['liftAccess'])?1:0
        ]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'listingId' => $listingId]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user']['id'];
    $role = $_SESSION['user']['role'];
    $listingId = $input['id'] ?? null;
    
    if (!$listingId) {
        echo json_encode(['success' => false, 'error' => 'Missing listing ID']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM listings WHERE listing_id = ?");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }
    if ($listing['user_id'] != $userId && $role !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $upd = $pdo->prepare("UPDATE listings SET zone_id=?, property_type=?, title=?, address=?, lat=?, lng=?, total_rooms=?, current_occupancy=?, description=? WHERE listing_id=?");
        $upd->execute([
            $input['zoneId'], 
            $input['propertyType'], 
            $input['title'], 
            $input['address'], 
            $input['lat'], 
            $input['lng'], 
            $input['totalRooms'], 
            $input['currentOccupancy'], 
            $input['description'] ?? null,
            $listingId
        ]);
        
        $c = $input['costs'];
        $cUpd = $pdo->prepare("UPDATE utility_costs SET base_rent=?, electricity_amount=?, electricity_type=?, gas_bill=?, water_bill=?, internet_cost=?, maintenance_fee=?, caretaker_fee=?, total_monthly=? WHERE listing_id=?");
        $cUpd->execute([
            $c['baseRent'], $c['electricityAmount'], $c['electricityType'], $c['gasBill'], $c['waterBill'], $c['internetCost'], $c['maintenanceFee'] ?? 0, $c['caretakerFee'] ?? 0, $c['totalMonthly'], $listingId
        ]);
        
        $a = $input['amenities'];
        $aUpd = $pdo->prepare("UPDATE listing_amenities SET attached_bathroom=?, attached_kitchen=?, is_furnished=?, rooftop_access=?, parking=?, power_backup=?, lift_access=? WHERE listing_id=?");
        $aUpd->execute([
            !empty($a['attachedBathroom'])?1:0, 
            !empty($a['attachedKitchen'])?1:0, 
            !empty($a['isFurnished'])?1:0, 
            !empty($a['rooftopAccess'])?1:0, 
            !empty($a['parking'])?1:0, 
            !empty($a['powerBackup'])?1:0, 
            !empty($a['liftAccess'])?1:0,
            $listingId
        ]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing ID']);
        exit;
    }
    
    $id = $_GET['id'];
    $userId = $_SESSION['user']['id'];
    $role = $_SESSION['user']['role'];
    
    $stmt = $pdo->prepare("SELECT user_id FROM listings WHERE listing_id = ?");
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }
    
    if ($listing['user_id'] != $userId && $role !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $del = $pdo->prepare("DELETE FROM listings WHERE listing_id = ?");
    $del->execute([$id]);
    
    echo json_encode(['success' => true]);
}
?>
