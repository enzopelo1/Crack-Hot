<?php
require_once './config/config.php';

session_start();



// Récupérer l'état du test depuis la base de données
$userId = $_SESSION['id_utilisateur'];
$stmt = $pdo->prepare("SELECT test_completed FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bindParam(1, $userId, PDO::PARAM_INT);
$stmt->execute();
$testCompleted = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="./public/css/homepage.css">
    <link rel="stylesheet" href="./public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">

</head>

<body>

    <?php include './pages/header.php'; ?>
    <?php include './pages/sidenav.php'; ?>

    <div class="home-container">
        <h2>Bienvenue</h2>
        <p style="color:white;">Ceci est un test pour rentrer dans l’entreprise R&MI, qui recrute des esprits curieux et analytiques à travers des énigmes captivantes. Serez-vous à la hauteur ?</p>
        <button id="start-test-button" class="btn btn-primary">LANCER LE TEST</button>
    </div>

    <div id="rules-popup" class="popup" style="display: none;">
        <div class="popup-content">
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
            <button id="close-popup-button" onclick="closeRulesPopup()">J'ai compris</button>
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

    <script>
        document.getElementById('start-test-button').addEventListener('click', function() {
            document.getElementById('rules-popup').style.display = 'block';
        });

        function closeRulesPopup() {
            document.getElementById('rules-popup').style.display = 'none';
            window.location.href = 'pages/test.php';
        }
    </script>

</body>

</html>