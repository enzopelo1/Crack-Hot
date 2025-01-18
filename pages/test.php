<?php
require_once '../config/config.php';

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo_utilisateur'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'index de la question actuelle, le nombre de vies restantes et l'état du test depuis la base de données
$userId = $_SESSION['id_utilisateur'];
$stmt = $pdo->prepare("SELECT question_en_cours, vies, test_completed FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bindParam(1, $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$currentQuestionIndex = $userData['question_en_cours'];
$lives = $userData['vies'];
$testCompleted = $userData['test_completed'];

// Rediriger l'utilisateur vers la page d'accueil s'il a déjà terminé le test
if ($testCompleted) {
    header('Location: ../index.php');
    exit();
}

// Récupérer les questions depuis la base de données
$query = "SELECT * FROM enigmes";
$result = $pdo->query($query);
$questions = [];

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
    <link rel="stylesheet" href="../public/css/test.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">

</head>

<body>

    <div class="progress-container">
        <div class="left-container">
            <div class="question-counter">
                <span id="question-number">1</span>/<?php echo count($questions); ?>
                <div id="lives-container">
                    <img src="../public/img/coeur.png" alt="Heart" class="heart" id="heart-1">
                    <img src="../public/img/coeur.png" alt="Heart" class="heart" id="heart-2">
                    <img src="../public/img/coeur.png" alt="Heart" class="heart" id="heart-3">
                    <img src="../public/img/coeur.png" alt="Heart" class="heart" id="heart-4">
                </div>
                <div id="streak-container"></div>
            </div>
            <div class="progress-bar">
                <div class="progress" id="progress"></div>
            </div>
        </div>
        <div class="right-container">
            <img src="../public/img/chronometre 1.png" alt="Timer Image" class="timer-image">
            <div class="timer"><span id="timer"><?php echo gmdate("i:s", strtotime($questions[0]['temps'])); ?></span></div>
        </div>
    </div>

    <div class="test-container">
        <div id="question-container">
            <!-- La question sera affichée ici -->
        </div>
        <div id="answer-container">
            <p id="error-message" style="color: red;"></p> <!-- Ajoutez cet élément pour afficher le message d'erreur -->
            <input type="text" id="answer" placeholder="Votre réponse...">
        </div>
        <div id="navigation-buttons">
            <button id="hint-button" onclick="showHint()"><img src="../public/img/ampoule.png" alt="bouton indice"></button>
            <button id="submit-button" onclick="submitAnswer()">Valider la réponse</button>
            <button id="next-button"><img src="../public/img/bouton-suivant.png" alt="bouton suivant"></button>
        </div>
    </div>

    <div id="hint-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <img src="../public/img/ampoule.png" alt="Logo Indice" class="popup-logo">
            <span class="close" onclick="closeHintPopup()">&times;</span>
            <p id="hint-text"></p>
        </div>
    </div>

    <div id="rules-popup" class="popup2" style="display: none;">
        <div class="popup-content2">
            <span class="close" onclick="closeInfoPopup()">&times;</span>
            <h2>Règles du Test</h2>
            <p>. Vous allez participer à un test de 20 questions qui portent sur plusieurs catégories différentes.</p>
            <p>. Vous avez pour chaque question 4 chances de réponse représentées sous forme de coeur 💚 dans la barre de progression en haut de l'écran.</p>
            <p>. Un indice est disponible pour chaque question en cliquant sur l'icone 💡, mais sachez qu'il vous coutera des points.</p>
            <p>. Vous avez un temps limité pour chaque question, qui est affiché à droite de la barre de progression.</p>
            <p>. Si le temps de la question est écoulé, votre tentative de réponse est annulée, et vous passerez à la question suivante.</p>
            <p>. Vous pouvez passer à la question suivante en cliquant sur l'icone ⏩, mais il n'y a pas de retour en arrière possible et vous ne mettrez aucun point.</p>
            <p>. Aucun barème n'est donné, mais sachez que chaque tentative de réponse ratée vous coutera des points.</p>
            <p>. Si vous quittez le test, vous reviendrez au même point de passage à votre retour.</p>
            <p>. Bonne chance !</p>
        </div>
    </div>

    <div id="confirmation-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="closeConfirmationPopup()">&times;</span>
            <h2>Confirmation</h2>
            <p>Êtes-vous sûr de vouloir passer à la question suivante ?</p>
            <button onclick="confirmNextQuestion()">Oui</button>
            <button onclick="closeConfirmationPopup()">Non</button>
        </div>
    </div>

    <div id="completion-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="closeCompletionPopup()">&times;</span>
            <h2>Félicitations !</h2>
            <p>Vous avez terminé toutes les questions.</p>
            <button onclick="redirectToHome()">Revenir à l'accueil</button>
        </div>
    </div>

    <div id="completed-popup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="closeCompletedPopup()">&times;</span>
            <h2>Test déjà terminé</h2>
            <p>Vous avez déjà terminé ce test. Vous ne pouvez pas le relancer.</p>
            <button onclick="redirectToHome()">Revenir à l'accueil</button>
        </div>
    </div>

    <div class="bottom-left">
        <button onclick="showInfoPopup()">Voir les règles</button>
    </div>
    <div class="bottom-right">
        <button onclick="goToHome()">Revenir à l'accueil</button>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        questions.forEach(question => {
            const timeParts = question.temps.split(':');
            question.temps = (+timeParts[0] * 60 * 60) + (+timeParts[1] * 60) + (+timeParts[2]); // Convertir le temps en secondes
        });
        const initialLives = <?php echo $lives; ?>; // Charger le nombre de vies initiales
        const userId = <?php echo $userId; ?>; // Charger l'ID de l'utilisateur

        function showImagePopup(src) {
            const imagePopup = document.getElementById('image-popup');
            const imagePopupContent = document.getElementById('image-popup-content');
            imagePopupContent.src = src;
            imagePopup.style.display = 'block';
        }

        function closeImagePopup() {
            const imagePopup = document.getElementById('image-popup');
            imagePopup.style.display = 'none';
        }
    </script>
    <script src="../public/js/test.js"></script>

</body>

</html>