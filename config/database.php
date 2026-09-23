<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ayo_piket";

try {
    $koneksi = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
