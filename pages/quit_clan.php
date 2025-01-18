<?php
require_once '../config/config.php';
session_start();

if (isset($_POST['id_clan']) && isset($_SESSION['id_utilisateur'])) {
    $id_clan = $_POST['id_clan'];
    $id_utilisateur = $_SESSION['id_utilisateur'];

    // Suppression de l'utilisateur du clan
    $sql = "DELETE FROM posseder WHERE id_clan = :id_clan AND id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_clan' => $id_clan,
        ':id_utilisateur' => $id_utilisateur
    ]);

    // Retourner une réponse pour indiquer le succès (peut être utilisé pour rafraîchir la page)
    echo "success";
}
?>
