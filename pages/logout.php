<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <link rel="stylesheet" href="../public/css/login.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">

</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="login-container">
        <div class="login-content">
            <img src="../public/img/img-connexion.png" alt="image du bloc de déconnexion">
            <div class="login-form">
                <h2><span style="color: #37DD00">Au revoir</span> !</h2>
                <h3>Êtes-vous sûr de vouloir vous <span style="color: #37DD00">déconnecter</span> ?</h3>

                <form action="logout.php" method="POST">
                    <button type="submit">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>