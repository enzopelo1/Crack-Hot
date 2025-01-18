<?php
require_once '../config/config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['pseudo_utilisateur'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

$userId = $_SESSION['id_utilisateur'];

$query = "SELECT streak FROM utilisateur WHERE id_utilisateur = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$streak = $stmt->fetchColumn();

if ($streak !== false) {
    echo json_encode(['success' => true, 'streak' => $streak]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la récupération de la streak']);
}
?>