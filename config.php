<?php
session_start();
$host = 'localhost';
$dbname = 'weather_dashboard';
$username = 'root'; // Change if different
$password = ''; // Change if you have a password
try {
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
die("Could not connect to the database: " . $e->getMessage());
}
?>