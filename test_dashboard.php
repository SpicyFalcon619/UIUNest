<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$_SESSION['user_id'] = 2; // Assuming the user is 2 based on test_seeking output
require 'api/dashboard.php';
