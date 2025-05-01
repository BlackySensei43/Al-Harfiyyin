<?php
ob_start();

$db_host = '';
$db_user = '';
$db_pass = '';
$db_name = '';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8 for Arabic support
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES 'utf8mb4'");
$conn->query("SET CHARACTER SET utf8mb4");
$conn->query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");

// Set timezone to Egypt
date_default_timezone_set('Africa/Cairo');

function sanitize($data) {
    global $conn;
    if ($data === null) {
        return '';
    }
    return $conn->real_escape_string(htmlspecialchars(strip_tags($data)));
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
