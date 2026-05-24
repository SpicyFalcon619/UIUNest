<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $listing_id = $_GET['listing_id'] ?? null;
    if (!$listing_id) {
        echo json_encode(['success' => false, 'error' => 'listing_id required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM monthly_bills WHERE listing_id = ? ORDER BY created_at DESC");
        $stmt->execute([$listing_id]);
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($bills as $b) {
            $p_stmt = $pdo->prepare("SELECT * FROM bill_payments WHERE bill_id = ?");
            $p_stmt->execute([$b['bill_id']]);
            $payments = $p_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pay_map = array_map(function($p) {
                return [
                    'id' => $p['payment_id'],
                    'resident' => $p['resident_label'] ?: 'Unknown Resident',
                    'status' => $p['status']
                ];
            }, $payments);

            $result[] = [
                'id' => $b['bill_id'],
                'listingId' => $b['listing_id'],
                'month' => substr($b['bill_month'], 0, 7),
                'electricity' => (float)$b['electricity_amount'],
                'gas' => (float)$b['gas_amount'],
                'water' => (float)$b['water_amount'],
                'internet' => (float)$b['internet_amount'],
                'other' => (float)$b['other_amount'],
                'total' => (float)$b['total_amount'],
                'perPerson' => (float)$b['per_person_amount'],
                'payments' => $pay_map
            ];
        }

        echo json_encode(['success' => true, 'bills' => $result]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $listing_id = $input['listingId'] ?? null;
    $month = $input['month'] ?? '';
    if (strlen($month) === 7) $month .= '-01'; // convert YYYY-MM to YYYY-MM-01 for MySQL DATE
    
    $electricity = $input['electricity'] ?? 0;
    $gas = $input['gas'] ?? 0;
    $water = $input['water'] ?? 0;
    $internet = $input['internet'] ?? 0;
    $other = $input['other'] ?? 0;
    $total = $input['total'] ?? 0;
    $perPerson = $input['perPerson'] ?? 0;
    $occupancy = $input['occupancy'] ?? 3;
    $user_id = $_SESSION['user_id'];

    if (!$listing_id) {
        echo json_encode(['success' => false, 'error' => 'Listing ID required']);
        exit;
    }

    try {
        // Check if bill already exists for this month and listing
        $chk = $pdo->prepare("SELECT bill_id FROM monthly_bills WHERE listing_id = ? AND bill_month = ?");
        $chk->execute([$listing_id, $month]);
        $existing = $chk->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing bill
            $stmt = $pdo->prepare("UPDATE monthly_bills SET electricity_amount=?, gas_amount=?, water_amount=?, internet_amount=?, other_amount=?, total_amount=?, per_person_amount=? WHERE bill_id=?");
            $stmt->execute([$electricity, $gas, $water, $internet, $other, $total, $perPerson, $existing['bill_id']]);
            echo json_encode(['success' => true, 'bill_id' => $existing['bill_id'], 'updated' => true]);
        } else {
            // Insert new bill
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO monthly_bills (listing_id, bill_month, electricity_amount, gas_amount, water_amount, internet_amount, other_amount, total_amount, per_person_amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$listing_id, $month, $electricity, $gas, $water, $internet, $other, $total, $perPerson, $user_id]);
            $bill_id = $pdo->lastInsertId();

            // Insert default bill payments
            $p_stmt = $pdo->prepare("INSERT INTO bill_payments (bill_id, resident_label, status) VALUES (?, ?, 'unpaid')");
            for ($i = 1; $i <= $occupancy; $i++) {
                $p_stmt->execute([$bill_id, "Resident $i"]);
            }
            $pdo->commit();
            
            echo json_encode(['success' => true, 'bill_id' => $bill_id]);
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'PUT') {
    // Mark payment as paid
    $input = json_decode(file_get_contents('php://input'), true);
    $payment_id = $input['paymentId'] ?? null;
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'error' => 'Payment ID required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE bill_payments SET status = 'paid', paid_at = NOW() WHERE payment_id = ?");
        $stmt->execute([$payment_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
