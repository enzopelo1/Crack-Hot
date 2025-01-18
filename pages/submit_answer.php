<?php
require_once '../config/config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['pseudo_utilisateur'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionId = isset($_POST['question_id']) ? $_POST['question_id'] : null;
    $userAnswer = isset($_POST['answer']) ? $_POST['answer'] : null;
    $hintUsed = isset($_POST['hint_used']) ? $_POST['hint_used'] : 0;
    $timeTaken = isset($_POST['time_taken']) ? $_POST['time_taken'] : 0;
    $lives = isset($_POST['lives']) ? $_POST['lives'] : 3;
    $points = isset($_POST['points']) ? $_POST['points'] : 5;
    $wrongAnswers = isset($_POST['mauvaise_reponse']) ? (int)$_POST['mauvaise_reponse'] : 0;
    $userId = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : null;

    if (!$questionId || !$userId) {
        echo json_encode([
            'error' => 'Données manquantes',
            'details' => [
                'question_id' => $questionId,
                'answer' => $userAnswer,
                'user_id' => $userId
            ]
        ]);
        exit();
    }

    // Récupérer la catégorie de l'énigme
    $query = "SELECT reponse, categorie FROM enigmes WHERE id_enigmes = :question_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':question_id', $questionId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $correctAnswer = $result['reponse'];
    $category = $result['categorie'];

    // Récupérer la streak actuelle de l'utilisateur
    $query = "SELECT streak FROM utilisateur WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $currentStreak = $stmt->fetchColumn();

    // Si l'utilisateur passe à la question suivante sans répondre
    if ($userAnswer === '') {
        $query = "INSERT INTO reponse_utilisateur (id_utilisateur, id_enigmes, reponse, indice, temps, points, mauvaise_reponse) VALUES (:user_id, :question_id, 0, :hint_used, :time_taken, 0, :wrong_answers)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':question_id', $questionId);
        $stmt->bindParam(':hint_used', $hintUsed);
        $stmt->bindParam(':time_taken', $timeTaken);
        $stmt->bindParam(':wrong_answers', $wrongAnswers);
        if ($stmt->execute()) {
            updateFinalNote($userId, 0, $category); // Mettre à jour la note finale avec 0 points
            resetLives($userId); // Remettre les vies à 4
            resetStreak($userId); // Réinitialiser la streak
            echo json_encode(['correct' => false]);
        } else {
            echo json_encode(['correct' => false, 'error' => 'Erreur lors de l\'enregistrement de la réponse']);
        }
        exit();
    }

    // Vérifier si la réponse de l'utilisateur est correcte (sans tenir compte de la casse)
    $isCorrect = (strtolower($userAnswer) === strtolower($correctAnswer)) ? 1 : 0;

    if ($isCorrect) {
        // Enregistrer la réponse correcte de l'utilisateur dans la base de données
        $query = "INSERT INTO reponse_utilisateur (id_utilisateur, id_enigmes, reponse, indice, temps, points, mauvaise_reponse) VALUES (:user_id, :question_id, :is_correct, :hint_used, :time_taken, :points, :wrong_answers)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':question_id', $questionId);
        $stmt->bindParam(':is_correct', $isCorrect);
        $stmt->bindParam(':hint_used', $hintUsed);
        $stmt->bindParam(':time_taken', $timeTaken);
        $stmt->bindParam(':points', $points);
        $stmt->bindParam(':wrong_answers', $wrongAnswers);

        if ($stmt->execute()) {
            updateFinalNote($userId, $points, $category); // Mettre à jour la note finale avec les points obtenus
            resetLives($userId); // Remettre les vies à 4
            incrementStreak($userId, $currentStreak); // Incrémenter la streak
            echo json_encode(['correct' => true, 'streak' => $currentStreak + 1]);
        } else {
            echo json_encode(['correct' => false, 'error' => 'Erreur lors de l\'enregistrement de la réponse']);
        }
    } else {
        // Si la réponse est incorrecte et que l'utilisateur n'a plus de vies, enregistrer la réponse incorrecte
        if ($lives <= 1) {
            $points = 0; // Pas de points pour une réponse incorrecte après toutes les vies utilisées
            $query = "INSERT INTO reponse_utilisateur (id_utilisateur, id_enigmes, reponse, indice, temps, points, mauvaise_reponse) VALUES (:user_id, :question_id, :is_correct, :hint_used, :time_taken, :points, :wrong_answers)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':question_id', $questionId);
            $stmt->bindParam(':is_correct', $isCorrect);
            $stmt->bindParam(':hint_used', $hintUsed);
            $stmt->bindParam(':time_taken', $timeTaken);
            $stmt->bindParam(':points', $points);
            $stmt->bindParam(':wrong_answers', $wrongAnswers);

            if ($stmt->execute()) {
                updateFinalNote($userId, $points, $category); // Mettre à jour la note finale avec 0 points
                resetLives($userId); // Remettre les vies à 4
                resetStreak($userId); // Réinitialiser la streak
                echo json_encode(['correct' => false, 'lives' => 0, 'streak' => 0]);
            } else {
                echo json_encode(['correct' => false, 'error' => 'Erreur lors de l\'enregistrement de la réponse']);
            }
        } else {
            resetStreak($userId); // Réinitialiser la streak
            echo json_encode(['correct' => false, 'lives' => $lives - 1, 'points' => $points - 1, 'streak' => 0]);
        }
    }

    // Vérifier si l'utilisateur a répondu à la dernière question
    $query = "SELECT COUNT(*) FROM enigmes";
    $stmt = $pdo->query($query);
    $totalQuestions = $stmt->fetchColumn();

    // Récupérer l'index de la question actuelle depuis la base de données
    $query = "SELECT question_en_cours FROM utilisateur WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $currentQuestionIndex = $stmt->fetchColumn();

    if ($currentQuestionIndex >= $totalQuestions - 1) {
        echo json_encode(['test_completed' => true]); // Indiquer que le test est terminé
    }
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}

function updateFinalNote($userId, $points, $category) {
    global $pdo;

    // Mettre à jour la note finale
    $query = "UPDATE Note SET Note_final = Note_final + :points WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':points', $points, PDO::PARAM_INT);
    $stmt->execute();

    // Mettre à jour la note par catégorie
    $categoryColumn = '';
    switch ($category) {
        case 'Décodage':
            $categoryColumn = 'Note_Decodage';
            break;
        case 'Recherche':
            $categoryColumn = 'Note_Recherche';
            break;
        case 'Logique':
            $categoryColumn = 'Note_Logique';
            break;
        case 'Conversion':
            $categoryColumn = 'Note_Conversion';
            break;
        case 'Association':
            $categoryColumn = 'Note_Association';
            break;
        default:
            return; // Si la catégorie n'est pas reconnue, ne rien faire
    }

    $query = "UPDATE Note SET $categoryColumn = $categoryColumn + :points WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':points', $points, PDO::PARAM_INT);
    $stmt->execute();
}

function resetLives($userId) {
    global $pdo;

    // Remettre les vies à 4
    $query = "UPDATE utilisateur SET vies = 4 WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
}

function incrementStreak($userId, $currentStreak) {
    global $pdo;

    // Incrémenter la streak
    $newStreak = $currentStreak + 1;
    $query = "UPDATE utilisateur SET streak = :streak WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':streak', $newStreak, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
}

function resetStreak($userId) {
    global $pdo;

    // Réinitialiser la streak
    $query = "UPDATE utilisateur SET streak = 0 WHERE id_utilisateur = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
}
?>