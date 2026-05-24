<?php
require 'db.php';
try {
    $stmt = $pdo->query("DELETE FROM monthly_bills WHERE bill_month = '0000-00-00'");
    echo "Deleted bad rows: " . $stmt->rowCount() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
