<?php
require_once '../config/config.php';

session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions légales</title>
    <link rel="stylesheet" href="../public/css/mentions_legales.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
    <script src="../public/js/mentions_legales.js"></script>
</head>

<body>

    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="form-global">
        <div class="form">
            <h1>Mentions Légales <br>Crack-Hot</h1>
            <p class="sous-titre">Obtenir des ressources et <br> des informations juridiques</p>
        </div>


        <img src="../public/img/mentions_legales/groupe_icon.png" alt="Icon juridique" class="img-mentions">
        <div class="title">
            <h2> Éthique et conformité</h2>
        </div>

        <div class="form-3" style="text-align: center;">
            <div class="content-container">
                <p class="sous-texte">
                    Crack-hot exerce ses activités de façon éthique, intègre et en conformité avec la loi.
                    <span class="toggle-arrow" onclick="toggleContent(1)">&#x25BC;</span>
                </p>
                <div class="hidden-content" id="content-1">
                    <p>
                        Crack-Hot mène ses activités de manière éthique, honnête et dans le plein respect de la loi. Nous pensons que la façon dont nous nous comportons est aussi essentielle au succès de Crack-Hot que la fabrication des meilleurs énigmes au monde.
                    </p>
                </div>
            </div>
        </div>
        <img src="../public/img/mentions_legales/fermer-a-cle.png" alt="Cadna sécurité" class="img-mentions">
        <div class="form-3" style="text-align: center;">
            <p class="sous-texte">Crack-Hot s'engage à protéger votre confidentialité
                <span class="toggle-arrow" onclick="toggleContent(2)">&#x25BC;</span>
            </p>
            <div class="hidden-content" id="content-2">
                <p>
                Consultez notre engagement de confidentialité envers notre clientèle pour découvrir en détail comment nous collectons, utilisons, divulguons, transférons et conservons vos données.                </p>
            </div>
        </div>


    </div>

</body>

</html>