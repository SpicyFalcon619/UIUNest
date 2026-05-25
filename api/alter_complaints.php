<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE complaints ADD COLUMN document_path VARCHAR(255) NULL AFTER description");
    echo "Column added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
