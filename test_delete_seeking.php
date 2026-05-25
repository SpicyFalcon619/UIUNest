<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$_SESSION['user_id'] = 2; // Test user
$_SERVER['REQUEST_METHOD'] = 'DELETE';

// Mock php://input
$inputData = json_encode(['id' => 1]);
// We can't easily mock php://input if it's already read, but we can override $_POST? 
// No, the script reads php://input. Let's just execute it by calling curl to the local server.
