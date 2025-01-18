<?php
require_once '../config/config.php';

session_start();

// Vérifiez si l'utilisateur est connecté et s'il est administrateur
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}


// Récupération des données de la base de données avec jointure
$sql = "SELECT u.id_utilisateur, u.pseudo_utilisateur, u.age_utilisateur, n.note_final AS score 
        FROM utilisateur u
        JOIN Note n ON u.id_utilisateur = n.id_utilisateur
        ORDER BY n.note_final DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <p class="solo-text" id="category-text">Solo</p>
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
                    <th>Pseudo</th>
                    <th>Âge</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr class="<?= ($_SESSION['id_utilisateur'] == $user['id_utilisateur']) ? 'highlight-row' : '' ?>">
                        <td><?= $index + 1 ?></td>
                        <td>
                            <?= htmlspecialchars($user['pseudo_utilisateur']) ?>
                            <a href="details.php?id=<?= $user['id_utilisateur'] ?>">
                                <img src="../public/img/loupe.png" alt="Loupe Icon" class="loupe-icon">
                            </a>
                        </td>
                        <td><?= htmlspecialchars($user['age_utilisateur']) ?></td>
                        <td><?= htmlspecialchars($user['score']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function setSolo() {
            document.getElementById('solo-icon').style.opacity = '1';
            document.getElementById('teams-icon').style.opacity = '0.4';
        }

        function setTeams() {
            document.getElementById('solo-icon').style.opacity = '0.4';
            document.getElementById('teams-icon').style.opacity = '1';
            window.location.href = 'classement_teams.php'; // Redirection vers classement_teams.php
        }

        document.getElementById('teams-icon').addEventListener('click', setTeams);

        // Set initial state
        setSolo();
    </script>
</body>

</html>