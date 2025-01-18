<?php
require_once '../config/config.php';

session_start();

// Vérifiez si l'ID de l'utilisateur est passé en paramètre
if (!isset($_GET['id'])) {
    die("ID de l'utilisateur non spécifié.");
}

$id_utilisateur = $_GET['id'];

// Récupération des détails des notes de l'utilisateur à partir de la base de Conversion
$sql = "SELECT 
            u.pseudo_utilisateur,
            n.Id_score,
            n.Note_final,
            n.Note_Decodage,
            n.Note_Recherche,
            n.Note_Logique,
            n.Note_Conversion,
            n.Note_Association,
            n.id_utilisateur,
            SUM(ru.temps) AS Temps_Total,
            SUM(ru.mauvaise_reponse) AS Total_Mauvaises_Reponses,
            MAX(ru.indice) AS Indice
        FROM 
            utilisateur u
        LEFT JOIN 
            Note n ON u.id_utilisateur = n.id_utilisateur
        LEFT JOIN 
            reponse_utilisateur ru ON n.id_utilisateur = ru.id_utilisateur
        WHERE 
            u.id_utilisateur = :id_utilisateur
        GROUP BY 
            n.Id_score, n.id_utilisateur, u.pseudo_utilisateur";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé.");
}

// Récupération du nombre de catégories dans la table enigmes
$sql_categories = "SELECT categorie, COUNT(*) as count FROM enigmes GROUP BY categorie";
$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total des notes avec les points supplémentaires
function calculateTotal($note, $categories, $type) {
    $count = 0;
    foreach ($categories as $category) {
        if ($category['categorie'] == $type) {
            $count = $category['count'];
            break;
        }
    }
    return ($count * 5);
}

$total_decodage = calculateTotal($user['Note_Decodage'], $categories, 'Décodage');
$total_recherche = calculateTotal($user['Note_Recherche'], $categories, 'Recherche');
$total_logique = calculateTotal($user['Note_Logique'], $categories, 'Logique');
$total_conversion = calculateTotal($user['Note_Conversion'], $categories, 'Conversion');
$total_association = calculateTotal($user['Note_Association'], $categories, 'Association');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link rel="stylesheet" href="../public/css/details.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="username-container">
        <a href="javascript:history.back()" class="back-button">
            <img src="../public/img/retour.png" alt="retour arrière Icon" class="retour-icon">
        </a>
        <h1 class="username"><?= htmlspecialchars($user['pseudo_utilisateur']) ?></h1>
    </div>

    <div class="stats-container">
        <div class="stat-item">
            Temps total: 
            <?php 
                $minutes = floor($user['Temps_Total'] / 60);
                $seconds = $user['Temps_Total'] % 60;
                echo htmlspecialchars($minutes . ' min ' . $seconds);
            ?>
        </div>
        <div class="stat-item">Mauvaises réponses: <?= htmlspecialchars($user['Total_Mauvaises_Reponses']) ?></div>
        <div class="stat-item">Indices utilisés: <?= htmlspecialchars($user['Indice']) ?></div>
       
    </div>

    <div class="score-container">
        <div class="scores">
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Decodage']) ?>/<?= htmlspecialchars($total_decodage) ?></span>
                <span class="label">Décodage</span>
            </div>
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Recherche']) ?>/<?= htmlspecialchars($total_recherche) ?></span>
                <span class="label">Recherche</span>
            </div>
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Logique']) ?>/<?= htmlspecialchars($total_logique) ?></span>
                <span class="label">Logique</span>
            </div>
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Conversion']) ?>/<?= htmlspecialchars($total_conversion) ?></span>
                <span class="label">Conversion</span>
            </div>
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Association']) ?>/<?= htmlspecialchars($total_association) ?></span>
                <span class="label">Association</span>
            </div>
            <div class="circle-score">
                <span class="score"><?= htmlspecialchars($user['Note_Decodage'] + $user['Note_Recherche'] + $user['Note_Logique'] + $user['Note_Conversion'] + $user['Note_Association']) ?>/<?= htmlspecialchars($total_decodage + $total_recherche + $total_logique + $total_conversion + $total_association) ?></span>
                <span class="label">Total</span>
            </div>
        </div>

        <div class="global-score">
            <div class="circle-chart">
                <canvas id="globalChart" width="200" height="200"></canvas>
            </div>
            
            <script>
                const totalScore = <?= htmlspecialchars($user['Note_Decodage'] + $user['Note_Recherche'] + $user['Note_Logique'] + $user['Note_Conversion'] + $user['Note_Association']) ?>;
                const ctx = document.getElementById('globalChart').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Décodage', 'Recherche', 'Logique', 'Conversion', 'Association'],
                        datasets: [{
                            label: 'Scores',
                            data: [
                                <?= htmlspecialchars($user['Note_Decodage']) ?>, 
                                <?= htmlspecialchars($user['Note_Recherche']) ?>, 
                                <?= htmlspecialchars($user['Note_Logique']) ?>, 
                                <?= htmlspecialchars($user['Note_Conversion']) ?>, 
                                <?= htmlspecialchars($user['Note_Association']) ?>, 
                            ],
                            backgroundColor: ['#37DD00', '#1AB800', '#145C00', '#5D5DFF', '#6633FF']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                display: false,
                                
                                color: '#fff',
                                font: {
                                    weight: 'bold',
                                    size: 16
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            </script>
        </div>
    </div>
</body>

</html>