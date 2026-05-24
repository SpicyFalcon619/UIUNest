<?php
require 'db.php';

try {
    $pdo->exec("ALTER TABLE items ADD COLUMN photo_url VARCHAR(255) NULL");
    echo "Column photo_url added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column photo_url already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
