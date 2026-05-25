<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE items ADD COLUMN photos TEXT AFTER photo_url");
    echo "Success";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
