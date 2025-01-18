<?php
include '../config/config.php'; // Inclure la connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hacher le mot de passe
    $mail = $_POST['mail'];
    $age = $_POST['old'];

    // Préparer la requête SQL
    $sql = "INSERT INTO utilisateur (pseudo_utilisateur, passeword_utilisateur, age_utilisateur, adresse_mail_utilisateur)
            VALUES (:username, :password, :age, :mail)";
    $stmt = $pdo->prepare($sql);

    try {
        // Exécuter la requête
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':age' => $age,
            ':mail' => $mail
        ]);
        echo "Inscription réussie !";
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
