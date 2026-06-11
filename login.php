<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $login = trim($_POST['pseudo']);
    $password = $_POST['password'];

    // Vérifier si l'utilisateur existe (par username ou email)
    $stmt = $conn->prepare("SELECT fullname, username, password FROM users WHERE username = ? OR email = ?");
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Vérification du mot de passe
        if (password_verify($password, $user['password'])) {
            // Stocker username dans la session au lieu de id
            $_SESSION['user_id'] = $user['username']; // On utilise username comme identifiant
            $_SESSION['pseudo'] = $user['username'];
            echo "success";
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Pseudo ou email incorrect.";
    }

    $stmt->close();
    $conn->close();
}
?>