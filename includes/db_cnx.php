<?php
$host = getenv('MYSQL_HOST') ?: 'db';
$db   = getenv('MYSQL_DATABASE') ?: 'app_db';
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: 'root';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
