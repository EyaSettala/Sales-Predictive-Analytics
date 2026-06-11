<?php
// Démarrer la session et récupérer le nom d'utilisateur
session_start();
$pseudo = isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : 'Utilisateur';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Inclure la connexion à la base de données
include 'db.php';

// Vérifier si $pdo est défini
if (!isset($pdo)) {
    die("Erreur : La connexion à la base de données n'est pas définie. Vérifiez db.php.");
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT username, fullname, email, phone, dob FROM users WHERE username = ?");
$stmt->execute([$pseudo]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucune donnée n'est trouvée, initialiser avec des valeurs par défaut
if (!$user) {
    $user = [
        'username' => $pseudo,
        'fullname' => '',
        'email' => '',
        'phone' => '',
        'dob' => ''
    ];
}
?>