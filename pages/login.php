<?php
require_once '../config/config.php';

session_start();

$error = '';

// Générer les valeurs du CAPTCHA
$a = rand(1, 10);
$b = rand(1, 10);
$captcha_result = $a + $b;

try {
    if (isset($_POST['username'], $_POST['password'], $_POST['captcha'], $_POST['captcha_result'])) {
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];
        $captcha = (int)$_POST['captcha'];
        $expected_captcha_result = (int)$_POST['captcha_result'];

        // Vérification du CAPTCHA
        if ($captcha !== $expected_captcha_result) {
            $error = 'CAPTCHA incorrect. Veuillez réessayer.';
        } else {
            $sql = "SELECT id_utilisateur, password, is_admin FROM utilisateur WHERE pseudo_utilisateur=:username";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stored_password = $row['password'];
                $userId = $row['id_utilisateur'];
                $is_admin = $row['is_admin'];

                // Encrypt the password using SHA-256
                $encrypted_password = hash('sha256', $password);

                if ($encrypted_password === $stored_password) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['pseudo_utilisateur'] = $username;
                    $_SESSION['id_utilisateur'] = $userId;
                    $_SESSION['is_admin'] = $is_admin;
                    $_SESSION['login_success'] = true;

                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Mot de passe incorrect.';
                }
            } else {
                $error = "Nom d'utilisateur incorrect.";
            }
        }
    }
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../public/css/login.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="login-container">
        <div class="login-content">
            <img src="../public/img/img-connexion.png" alt="image du bloc de connexion">
            <div class="login-form">
                <form action="login.php" method="POST">
                    <h2><span style="color: #37DD00">Bonjour</span> !</h2>
                    <h3>Veuillez vous <span style="color: #37DD00">connecter</span> !</h3>

                    <?php if (isset($error)): ?>
                        <div class="error-message">
                            <p><?= htmlspecialchars($error); ?></p>
                        </div>
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
                        <label for="captcha">CAPTCHA : Combien font <?= $a ?> + <?= $b ?> ?</label>
                        <input type="text" id="captcha" name="captcha" required>
                        <input type="hidden" name="captcha_result" value="<?= $captcha_result ?>">
                    </div>

                    <button type="submit">Connexion</button>
                    <div class="form-group2">
                        <label for="remember">Vous n'avez pas de compte ?</label>
                        <a href="../pages/subscribe.php" class="btn-inscription">Inscription</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true): ?>
    <script>
        setTimeout(function() {
            alert('Vous vous êtes bien connecté !');
            window.location.href = '../index.php';
        }, 100);
    </script>
    <?php unset($_SESSION['login_success']); endif; ?>
    
</body>

</html>