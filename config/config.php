<?php
$servername = "database"; // Remplacez par le nom de votre serveur
$username = "site-crypto"; // Remplacez par votre nom d'utilisateur
$password = "9pUjQp71zSgb"; // Remplacez par votre mot de passe
$dbname = "site-crypto"; // Remplacez par le nom de votre base de données

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Définir le mode d'erreur PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}