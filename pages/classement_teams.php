<?php
require_once '../config/config.php';

session_start();

// Vérifiez si l'utilisateur est connecté et s'il est administrateur
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

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
$sql = "SELECT 
            C.id_clan, 
            C.nom_clan, 
            C.date_creation, 
            AVG(CAST(U.age_utilisateur AS UNSIGNED)) AS age_moyen_clan, 
            AVG(N.Note_final) AS score_moyen_clan 
        FROM 
            clan AS C 
        LEFT JOIN 
            posseder AS P ON C.id_clan = P.id_clan 
        LEFT JOIN 
            utilisateur AS U ON P.id_utilisateur = U.id_utilisateur 
        LEFT JOIN 
            Note AS N ON U.id_utilisateur = N.id_utilisateur
        GROUP BY 
            C.id_clan, C.nom_clan, C.date_creation
        ORDER BY 
            score_moyen_clan DESC"; // Tri par score moyen décroissant
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement</title>
    <link rel="stylesheet" href="../public/css/classement.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="progress-container">
        <div class="left-container">
            <div class="text-container">
                <h1>Classement</h1>
                <p class="solo-text" id="category-text">Équipes</p>
            </div>
            <div class="icons-container">
                <img id="solo-icon" src="../public/img/logo_user.png" alt="Person Icon" onclick="setSolo()">
                <img id="teams-icon" src="../public/img/logo_users.png" alt="Group Icon" onclick="setTeams()">
            </div>
        </div>
        <div class="right-container">
            <img src="../public/img/classement.png" alt="classment Image" class="classment-image">
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Nom</th>
                    <th>Âge moyen</th>
                    <th>Score moyen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clans as $index => $clan): ?>
                    <tr class="<?= $clan['id_clan'] == $clan_utilisateur ? 'highlight-clan' : '' ?>">
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($clan['nom_clan']) ?> 
                        <a href="details_clan.php?id=<?= $clan['id_clan'] ?>">
                                <img src="../public/img/loupe.png" alt="Loupe Icon" class="loupe-icon">
                            </a></td>
                        <td><?= number_format($clan['age_moyen_clan'], 0) ?></td>
                        <td><?= number_format($clan['score_moyen_clan'], 1) ?>/100</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function setSolo() {
            document.getElementById('solo-icon').style.opacity = '1';
            document.getElementById('teams-icon').style.opacity = '0.4';
            window.location.href = 'classements.php'; // Redirection vers classements.php
        }

        function setTeams() {
            document.getElementById('solo-icon').style.opacity = '0.4';
            document.getElementById('teams-icon').style.opacity = '1';
        }

        document.getElementById('solo-icon').addEventListener('click', setSolo);
        document.getElementById('teams-icon').addEventListener('click', setTeams);

        // Set initial state
        setTeams(); // Change to setTeams to keep the current page
    </script>
</body>

</html>