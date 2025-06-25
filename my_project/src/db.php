<?php
function parseEnvFile($filepath) {
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
    return $env;
}

$dotenv = parseEnvFile(__DIR__ . '/../.env');

$host = $dotenv['DB_HOST'] ?? '127.0.0.1';
$db   = $dotenv['DB_DATABASE'] ?? '';
$user = $dotenv['DB_USERNAME'] ?? '';
$pass = $dotenv['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Помилка підключення до бази: " . $e->getMessage());
}
