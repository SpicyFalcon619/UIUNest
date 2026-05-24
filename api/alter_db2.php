<?php
require 'db.php';

try {
    $pdo->exec("ALTER TABLE bill_payments ADD COLUMN resident_label VARCHAR(100) NULL");
    echo "Column resident_label added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column resident_label already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
