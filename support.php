<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_complet = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $categorie = $_POST['category'] ?? '';
    $message = $_POST['message'] ?? '';

    $stmt = $conn->prepare("INSERT INTO support (nom_complet, email, categorie, message) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        echo "Erreur SQL : " . $conn->error;
        exit;
    }

    $stmt->bind_param("ssss", $nom_complet, $email, $categorie, $message);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Erreur : " . $stmt->error;
    }
    exit;
}
?>
