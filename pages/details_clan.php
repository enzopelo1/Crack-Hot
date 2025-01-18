<?php
require_once '../config/config.php';

session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit();
}

// Vérifiez si l'utilisateur est administrateur
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Récupération de l'ID du clan depuis l'URL
$clan_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$clan_id) {
    echo "ID de clan non spécifié.";
    exit();
}

// Vérification de l'existence du clan
$sql_clan = "SELECT nom_clan FROM clan WHERE id_clan = :id_clan";
$stmt_clan = $pdo->prepare($sql_clan);
$stmt_clan->execute(['id_clan' => $clan_id]);
$clan = $stmt_clan->fetch(PDO::FETCH_ASSOC);

if (!$clan) {
    // Redirection ou message d'erreur
    header('Location: clan.php');
    exit();
}

$sql_members = "
    SELECT u.id_utilisateur, u.pseudo_utilisateur, u.age_utilisateur, n.note_final AS score
    FROM utilisateur u
    JOIN posseder p ON u.id_utilisateur = p.id_utilisateur
    JOIN Note n ON u.id_utilisateur = n.id_utilisateur
    WHERE p.id_clan = :id_clan
    ORDER BY n.note_final DESC";
$stmt_members = $pdo->prepare($sql_members);
$stmt_members->execute(['id_clan' => $clan_id]);
$members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Clan</title>
    <link rel="stylesheet" href="../public/css/details_clan.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="progress-container">
        <div class="left-container">
        
        <div class="text-container">
                <div class="back-and-title">
                    <a href="javascript:history.back()" class="back-button">
                        <img src="../public/img/retour.png" alt="retour arrière Icon" class="retour-icon">
                    </a>
                    <h1 class="titre-nav">Détails de l'équipe</h1>
                </div>
                <p class="solo-text"><span class="highlight-clan"><?= htmlspecialchars($clan['nom_clan']) ?></span></p>
            </div>
            <div class="icons-container">
                <a href="clan_join.php?clan_name=<?= urlencode($clan['nom_clan']) ?>">
                    <img id="create-clan-button" src="../public/img/create_clan_button.png" alt="Group Icon">
                </a>
            </div>
        </div>
        <div class="right-container">
            <img src="../public/img/clan_icon.png" alt="classment Image" class="classment-image">
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pseudo</th>
                    <th>Âge</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['pseudo_utilisateur']) ?>
                        <?php if ($is_admin): ?>
                            <a href="details.php?id=<?= $member['id_utilisateur'] ?>">
                                <img src="../public/img/loupe.png" alt="Loupe Icon" class="loupe-icon">
                            </a>
                        <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($member['age_utilisateur']) ?></td>
                        <td><?= htmlspecialchars($member['score']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('create-clan-button').addEventListener('click', function() {
                window.location.href = 'clan_join.php'; // Redirection vers la page de création de clan
            });
    </script>
</body>

</html>