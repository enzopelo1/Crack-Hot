<?php
session_start();
require_once '../config/config.php';

$error_message = '';
$success_message = ''; // Variable pour le message de succès

// Vérifier si l'utilisateur est connecté, mais uniquement après la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['id_utilisateur'])) {
        $error_message = "Vous devez être connecté pour rejoindre un clan.";
    } else {
        $nom_clan = $_POST['clan_name'];
        $password_clan = $_POST['clan_password'];

        // Vérifier si l'utilisateur appartient déjà à un clan
        $sql_check_user_clan = "SELECT id_clan FROM posseder WHERE id_utilisateur = :id_utilisateur";
        $stmt_check_user_clan = $pdo->prepare($sql_check_user_clan);
        $stmt_check_user_clan->execute([':id_utilisateur' => $_SESSION['id_utilisateur']]);
        $user_clan = $stmt_check_user_clan->fetch(PDO::FETCH_ASSOC);

        if ($user_clan) {
            // Si l'utilisateur appartient déjà à un clan
            $error_message = "Vous faites déjà partie d'un clan. Veuillez quitter votre clan actuel avant de rejoindre un nouveau.";
        } else {
            // Vérifier si le clan existe et si le mot de passe est correct
            $sql_check = "SELECT c.id_clan, c.password_clan, c.nom_clan 
                          FROM clan c 
                          WHERE c.nom_clan = :nom_clan";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([':nom_clan' => $nom_clan]);
            $clan = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($clan && password_verify($password_clan, $clan['password_clan'])) {
                // Insérer l'utilisateur dans le clan
                $sql_join = "INSERT INTO posseder (id_utilisateur, id_clan) 
                             VALUES (:id_utilisateur, :id_clan)";
                $stmt_join = $pdo->prepare($sql_join);
                $stmt_join->execute([
                    ':id_utilisateur' => $_SESSION['id_utilisateur'],
                    ':id_clan' => $clan['id_clan']
                ]);

                $_SESSION['clan_joined'] = true;
                $success_message = $clan['nom_clan']; // Nom du clan pour la popup
            } else {
                $error_message = "Nom du clan ou mot de passe incorrect.";
            }
        }
    }
}

$clan_name = isset($_GET['clan_name']) ? htmlspecialchars($_GET['clan_name']) : '';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre un Clan</title>
    <link rel="stylesheet" href="../public/css/clan_join.css"> <!-- Le fichier CSS -->
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>

    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="subscribe-container">
        <div class="subscribe-content">
            <img src="../public/img/clan_creation_background.png" alt="image du bloc de création de clan">
            <div class="subscribe-form">
                <form action="clan_join.php" method="POST">
                    <h2><span style="color: #37DD00">Bienvenue</span> !</h2>
                    <h2>Rejoignez un Clan</h2>
                    <h3>Veuillez remplir les informations ci-dessous pour <span style="color: #37DD00">rejoindre un clan</span> !</h3>

                    <!-- Afficher le message d'erreur uniquement après avoir soumis le formulaire -->
                    <?php if (!empty($error_message)): ?>
                        <p style="color: red; font-size: 0.9em;"><?= htmlspecialchars($error_message) ?></p>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="clan_name">Nom du Clan</label>
                        <input type="text" id="clan_name" name="clan_name" value="<?= $clan_name ?>" required>
                        </div>
                    <div class="form-group">
                        <label for="clan_password">Mot de passe</label>
                        <input type="password" id="clan_password" name="clan_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit">Rejoindre le Clan</button>
                    </div>
                </form>
                <p style="color: #37DD00; font-size: 0.9em; margin-top: 10px;">
                    Vous n'avez pas de clan ? <a href="clan_creation.php" style="color: #37DD00; text-decoration: underline;">Créez-en un ici</a>.
                </p>
            </div>
        </div>
    </div>

    <!-- Popup -->
    <div id="popup-background" class="popup-background"></div>
    <div id="popup" class="popup">
        <h2>Félicitations !</h2>
        <p id="popup-clan-name">Vous avez rejoint le clan !</p>
        <button onclick="closePopup()">Ok</button>
    </div>

    <script>
        // Afficher la popup de succès si l'utilisateur a rejoint un clan
        <?php if ($success_message): ?>
            document.getElementById('popup-clan-name').innerText = 'Félicitations ! Vous avez rejoint le clan ' + '<?= htmlspecialchars($success_message) ?>';
            document.getElementById('popup').style.display = 'block';
            document.getElementById('popup-background').style.display = 'block';
        <?php endif; ?>

        // Fonction pour fermer la popup
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
            window.location.href = 'clan.php'; // Rediriger l'utilisateur après avoir fermé la popup
        }
    </script>

</body>

</html>
