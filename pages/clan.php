<?php
require_once '../config/config.php';
session_start();

// Récupération de l'ID du clan de l'utilisateur connecté
$id_utilisateur = $_SESSION['id_utilisateur'] ?? null; // Récupère l'ID de l'utilisateur s'il est connecté
$clan_utilisateur = null;

if ($id_utilisateur) {
    // Récupérer l'ID du clan de l'utilisateur
    $sql_user_clan = "SELECT c.id_clan
                      FROM clan c
                      JOIN posseder p ON c.id_clan = p.id_clan
                      WHERE p.id_utilisateur = :id_utilisateur";
    $stmt_user_clan = $pdo->prepare($sql_user_clan);
    $stmt_user_clan->execute([':id_utilisateur' => $id_utilisateur]);
    $clan_utilisateur = $stmt_user_clan->fetchColumn();
}

// Récupération des données des clans
$sql = "SELECT c.nom_clan, 
               c.date_creation, 
               COUNT(p.id_utilisateur) AS nombre_membres, 
               FLOOR(AVG(u.age_utilisateur)) AS age_moyen,
               c.id_clan
        FROM clan c
        LEFT JOIN posseder p ON c.id_clan = p.id_clan
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        GROUP BY c.id_clan, c.nom_clan, c.date_creation
        ORDER BY c.date_creation DESC, c.id_clan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$clans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clans</title>
    <link rel="stylesheet" href="../public/css/clan.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="progress-container">
        <div class="left-container">
            <div class="text-container">
                <h1>Clans</h1>
                <p class="solo-text" id="category-text">Liste des équipes</p>
            </div>
            <div class="icons-container">
                <img id="create-clan-button" src="../public/img/create_clan_button.png" alt="Group Icon">
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
                    <th>Nom du Clan</th>
                    <th>Âge Moyen</th>
                    <th>Date de Création</th>
                    <th>Nombre de Membres</th>
                    <th>Quitter</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clans as $clan): ?>
                    <tr class="<?= $clan['id_clan'] == $clan_utilisateur ? 'highlight-clan' : '' ?>">
                        <td>
                            <?= htmlspecialchars($clan['nom_clan']) ?>
                            <a href="details_clan.php?id=<?= $clan['id_clan'] ?>">
                                <img src="../public/img/loupe.png" alt="Loupe Icon" class="loupe-icon">
                            </a>
                        </td>
                        <td><?= htmlspecialchars(intval($clan['age_moyen'])) ?></td>
                        <td><?= htmlspecialchars($clan['date_creation']) ?></td>
                        <td><?= htmlspecialchars($clan['nombre_membres']) ?></td>
                        <td>
                            <?php if ($clan['id_clan'] == $clan_utilisateur): ?>
                                <img src="../public/img/left_clan.png" alt="Quitter" class="quit-clan-btn" onclick="showPopup(<?= $clan['id_clan'] ?>)">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Popup de confirmation pour quitter le clan -->
    <div id="popup" class="popup-overlay">
        <div class="popup-content">
            <p>Voulez-vous vraiment quitter ce clan ?</p>
            <button onclick="quitClan()">Oui, quitter le clan</button>
            <button onclick="closePopup()">Annuler</button>
        </div>
    </div>

    <script>

        document.getElementById('create-clan-button').addEventListener('click', function() {
                window.location.href = 'clan_join.php'; // Redirection vers la page de création de clan
            });

        let currentClanId = null;

        // Affiche la popup de confirmation
        function showPopup(clanId) {
            currentClanId = clanId;
            document.getElementById('popup').style.display = 'flex';
        }

        // Ferme la popup sans rien faire
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        // Gère l'action de quitter le clan
        function quitClan() {
            if (currentClanId) {
                // Effectuer une requête AJAX pour quitter le clan sans recharger la page
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'quit_clan.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Si l'utilisateur quitte le clan, on actualise la page
                        location.reload();
                    }
                };
                xhr.send('id_clan=' + currentClanId);
            }
        }
    </script>
</body>

</html>
