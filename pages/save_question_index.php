<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentQuestionIndex = isset($_POST['question_en_cours']) ? (int)$_POST['question_en_cours'] : 0;
    $remainingTime = isset($_POST['temps_restant']) ? $_POST['temps_restant'] : '00:00:00';
    $userId = $_SESSION['id_utilisateur'];

    $stmt = $pdo->prepare("UPDATE utilisateur SET question_en_cours = ?, temps_restant = ? WHERE id_utilisateur = ?");
    $stmt->bindParam(1, $currentQuestionIndex, PDO::PARAM_INT);
    $stmt->bindParam(2, $remainingTime, PDO::PARAM_STR);
    $stmt->bindParam(3, $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour de l\'index de la question et du temps restant']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>