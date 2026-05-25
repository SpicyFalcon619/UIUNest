<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 2; // the test user
$_SERVER['REQUEST_METHOD'] = 'DELETE';

// We cannot write to php://input. So we'll inject into $_POST or just mock the file?
// Let's create a temporary file and mock file_get_contents. 
// Instead of mocking, let's just make a curl request WITH session cookie.
