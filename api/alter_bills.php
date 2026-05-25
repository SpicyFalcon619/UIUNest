<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE monthly_bills ADD COLUMN custom_fees TEXT NULL AFTER other_amount");
    echo "Column custom_fees added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column custom_fees already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Add foreign key cascade for bill_payments if not exists?
// Actually I'll just delete them manually in the API if needed to be safe across different MariaDB versions.
?>
