<?php
// Try to get environment variables (provided by Railway)
$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$db   = getenv('MYSQLDATABASE') ?: 'uiunest';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';
$charset = 'utf8mb4';

// Sometimes Railway uses a single MYSQL_URL string
$mysql_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($mysql_url) {
    $dbopts = parse_url($mysql_url);
    $host = $dbopts["host"] ?? $host;
    $port = $dbopts["port"] ?? $port;
    $user = $dbopts["user"] ?? $user;
    $pass = $dbopts["pass"] ?? $pass;
    $db   = ltrim($dbopts["path"] ?? "/$db", "/");
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    // Temporarily show the full error to debug the Railway connection
    $errMsg = 'Database connection failed. ' . $e->getMessage() . ' | DSN: ' . $dsn . ' | USER: ' . $user;
    echo json_encode(['error' => $errMsg]);
    exit;
}
?>
