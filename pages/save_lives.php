<?php
require_once '../config/config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lives = isset($_POST['lives']) ? (int)$_POST['lives'] : 4;
    $userId = $_SESSION['id_utilisateur'];

    $query = "UPDATE utilisateur SET vies = :lives WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':lives', $lives, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la sauvegarde des vies']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>