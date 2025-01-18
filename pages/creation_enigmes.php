<?php
require_once '../config/config.php';

session_start();

// Vérifiez si l'utilisateur est connecté et s'il est administrateur
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $reponse = htmlspecialchars($_POST['reponse']);
    $texte_question = htmlspecialchars($_POST['texte_question']);
    $image_question = !empty($_POST['img']) ? htmlspecialchars($_POST['img']) : null;  // Utilisation du chemin d'image fourni
    $temps = (int)$_POST['temps'];
    $indice = htmlspecialchars($_POST['indice']);
    $categorie = htmlspecialchars($_POST['categorie']);

    // Insérer les données dans la table `enigmes`
    $sql = "INSERT INTO enigmes (reponse, texte_question, img, temps, indice, categorie) 
            VALUES (:reponse, :texte_question, :img, :temps, :indice, :categorie)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':reponse', $reponse);
        $stmt->bindParam(':texte_question', $texte_question);
        $stmt->bindParam(':img', $image_question);
        $stmt->bindParam(':temps', $temps, PDO::PARAM_INT);
        $stmt->bindParam(':indice', $indice);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->execute();

        // Message de succès
        $successMessage = "L'énigme a été créée avec succès !";
        echo "<script>alert('Votre énigme a été créée avec succès !');</script>";
    } catch (PDOException $e) {
        // Gestion des erreurs
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création d'une énigme</title>
    <link rel="stylesheet" href="../public/css/creation_enigmes.css">
    <link rel="stylesheet" href="../public/css/app.css">
    <link rel="icon" href="../public/img/logo_rond.png" type="image/png">
    <?php if ($successMessage): ?>
        <script>
            alert('<?php echo $successMessage; ?>');
        </script>
    <?php endif; ?>
</head>

<body>
    <?php include '../pages/header.php'; ?>
    <?php include '../pages/sidenav.php'; ?>

    <div class="progress-container">
        <div class="left-container">
            <div class="text-container">
                <h1>Nouvelle énigme</h1>
                <p class="solo-text" id="category-text">Ajout</p>
            </div>
        </div>
        <div class="right-container">
            <img src="../public/img/creation_enigme.png" alt="creation Image" class="creation-image">
        </div>
    </div>

    <div class="form-container">
        <form action="" method="POST" enctype="multipart/form-data" class="form">

            <label for="reponse">Réponse :</label>
            <input type="text" id="reponse" name="reponse" required placeholder="Entrez la réponse" class="form-input">

            <label for="texte_question">Intitulé de la question :</label>
            <textarea id="texte_question" name="texte_question" required placeholder="Entrez le texte de la question" class="form-textarea"></textarea>

            <label for="img">Image de la question :</label>
            <input type="text" id="img" name="img" required placeholder="Chemin de votre image" class="form-input">

            <label for="temps">Durée pour résoudre la question (en secondes) :</label>
            <input type="number" id="temps" name="temps" required placeholder="Entrez le temps en secondes" class="form-input" min="1">

            <label for="indice">Indice :</label>
            <textarea id="indice" name="indice" required placeholder="Entrez l'indice" class="form-textarea"></textarea>

            <label for="categorie">Catégorie :</label>
            <input type="text" id="categorie" name="categorie" required placeholder="Entrez la catégorie" class="form-input">

            <button type="submit" class="form-button">Créer l'énigme</button>
        </form>
    </div>
</body>

</html>
