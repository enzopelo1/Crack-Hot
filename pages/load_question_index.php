<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

$userId = $_SESSION['id_utilisateur'];

$stmt = $pdo->prepare("SELECT question_en_cours, temps_restant FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bindParam(1, $userId, PDO::PARAM_INT);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result !== false) {
    $remainingTime = timeToSeconds($result['temps_restant']);
    echo json_encode(['success' => true, 'question_en_cours' => (int)$result['question_en_cours'], 'temps_restant' => $remainingTime]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur lors du chargement de l\'index de la question et du temps restant']);
}

function timeToSeconds($time) {
    $parts = explode(':', $time);
    return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
}
?>