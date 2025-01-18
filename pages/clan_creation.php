<?php
session_start();
require_once '../config/config.php';

$error_message = '';

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_clan = $_POST['clan_name'];
    $password_clan = $_POST['clan_password'];

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id_utilisateur'])) {
        $error_message = "Vous devez être connecté pour créer un clan.";
    } else {
        // Vérifier si le nom du clan existe déjà
        $sql_check = "SELECT COUNT(*) FROM clan WHERE nom_clan = :nom_clan";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nom_clan' => $nom_clan]);
        $clan_exists = $stmt_check->fetchColumn();

        if ($clan_exists > 0) {
            $error_message = "Le nom du clan est déjà pris. Veuillez en choisir un autre.";
        } else {
            // Sécuriser le mot de passe avec password_hash
            $hashed_password = password_hash($password_clan, PASSWORD_BCRYPT);

            try {
                $sql_insert = "INSERT INTO clan (nom_clan, password_clan, date_creation) 
                            VALUES (:nom_clan, :password_clan, :date_creation)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':nom_clan' => $nom_clan,
                    ':password_clan' => $hashed_password,
                    ':date_creation' => date('Y-m-d') // Ceci va seulement insérer la date (YYYY-MM-DD)
                ]);

                $_SESSION['clan_created'] = true;
                $_SESSION['nom_clan'] = $nom_clan; // Stocker le nom du clan pour l'affichage dans la popup

                // Redirection avec message via JS
                echo "<script>
                    setTimeout(function() {
                        alert('Félicitations ! Vous avez créé le clan \"' + '". $nom_clan . "' + '\" !');
                        window.location.href = 'clan.php';
                    }, 100);
                </script>";
                exit();
            } catch (PDOException $e) {
                $error_message = "Erreur lors de la création du clan : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de Clan</title>
    <link rel="stylesheet" href="../public/css/subscribe.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>
<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="subscribe-container">
        <div class="subscribe-content">
            <img src="../public/img/clan_creation_background.png" alt="image du bloc de création de clan">
            <div class="subscribe-form">
                <form id="clanCreationForm" action="" method="POST">
                    <h2><span style="color: #37DD00">Bienvenue</span> !</h2>
                    <h2>Créez votre Clan</h2>
                    <h3>Veuillez remplir les informations ci-dessous pour <span style="color: #37DD00">créer votre clan</span> !</h3>

                    <!-- Affichage des erreurs -->
                    <div id="error-message" style="color: red; font-size: 1.1em; display: <?= !empty($error_message) ? 'block' : 'none' ?>;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>

                    <div class="form-group">
                        <label for="clan_name">Nom du Clan</label>
                        <input type="text" id="clan_name" name="clan_name" required>
                    </div>
                    <div class="form-group">
                        <label for="clan_password">Mot de passe</label>
                        <input type="password" id="clan_password" name="clan_password" required>
                    </div>
                    <div class="form-group">
                        <label for="creation_date">Date de création</label>
                        <input type="date" id="creation_date" name="creation_date" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <button type="submit" id="create-clan-button">Créer le Clan</button>
                    </div>
                    <p style="color: #37DD00; font-size: 0.9em; margin-top: 10px;">
                        Vous voulez rejoindre un clan ? <a href="clan_join.php" style="color: #37DD00; text-decoration: underline;">Cliquez ici</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
