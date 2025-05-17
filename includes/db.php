<?php
$host = 'localhost';
$db   = 'sistema_ovinos_db';
$user = 'root';
$pass = ''; // o tu contraseña si le pusiste una

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error en la conexión: " . $e->getMessage());
}
