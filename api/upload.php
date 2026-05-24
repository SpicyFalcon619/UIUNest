<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
        exit;
    }

    $type = $_POST['type'] ?? 'general'; // verification, listing, item
    $allowedTypes = ['verification', 'listing', 'item'];
    
    if (!in_array($type, $allowedTypes)) {
        $type = 'general';
    }

    $uploadDir = '../uploads/' . $type . 's/';
    
    // Ensure the directory exists (it should, but just in case)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Basic validation
    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($fileExt, $allowedExts)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.']);
        exit;
    }

    // Generate unique name
    $newFileName = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Return the relative path from the root to be saved in DB
        $relativePath = 'uploads/' . $type . 's/' . $newFileName;
        echo json_encode(['success' => true, 'path' => $relativePath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
