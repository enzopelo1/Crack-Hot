<?php
require_once '../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $mail = $_POST['mail'];
    $birthdate = $_POST['birthdate'];

    // Calcul de l'âge à partir de la date de naissance
    $birthDate = new DateTime($birthdate);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;

    // Vérification si le nom d'utilisateur existe déjà
    $sql_check_username = "SELECT COUNT(*) FROM utilisateur WHERE pseudo_utilisateur = :username";
    $stmt_check_username = $pdo->prepare($sql_check_username);
    $stmt_check_username->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt_check_username->execute();
    $user_exists = $stmt_check_username->fetchColumn();

    // Vérification si l'adresse mail existe déjà
    $sql_check_mail = "SELECT COUNT(*) FROM utilisateur WHERE adresse_mail_utilisateur = :mail";
    $stmt_check_mail = $pdo->prepare($sql_check_mail);
    $stmt_check_mail->bindParam(':mail', $mail, PDO::PARAM_STR);
    $stmt_check_mail->execute();
    $mail_exists = $stmt_check_mail->fetchColumn();

    if ($user_exists) {
        $error_message = "Le nom d'utilisateur est déjà utilisé.";
    } elseif ($mail_exists) {
        $error_message = "L'adresse mail est déjà utilisée.";
    } else {
        // Enregistrement dans la base de données
        $sql = "INSERT INTO utilisateur (pseudo_utilisateur, password, adresse_mail_utilisateur, age_utilisateur, is_admin) VALUES (:username, :password, :mail, :age, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindParam(':age', $age, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['account_created'] = true;
            header('Location: subscribe.php');
            exit();
        } else {
            $error_message = "Erreur lors de la création du compte.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../public/css/subscribe.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>

    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="subscribe-container">
        <div class="subscribe-content">
            <img src="../public/img/img-connexion.png" alt="image du bloc de d'inscription">
            <div class="subscribe-form">
                <form action="" method="POST">
                    <h2><span style="color: #37DD00">Bonjour</span> !</h2>
                    <h2>Bienvenue sur R&MI</h2>
                    <h3>Veuillez vous <span style="color: #37DD00">inscrire</span> !</h3>

                    <?php if (isset($error_message)): ?>
                        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['account_created']) && $_SESSION['account_created'] == true): ?>
                        <script>
                            setTimeout(function() {
                                alert('Votre compte a bien été créé !');
                                window.location.href = 'login.php';
                            }, 1000);
                        </script>
                        <?php unset($_SESSION['account_created']); ?>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="mail">Adresse mail</label>
                        <input type="email" id="mail" name="mail" required>
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                    <button type="submit">Inscription</button>
                    <div class="form-group2">
                        <label for="remember">Vous avez déjà un compte ?</label>
                        <a href="../pages/login.php" class="se_connecter">Se connecter</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>