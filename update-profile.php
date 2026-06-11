<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Non autorisé.";
    exit;
}
$id = $_SESSION['user_id'];

// Sécurisation des données du formulaire
$fullname = trim($_POST['nom']) ?? '';
$username = trim($_POST['pseudo']) ?? '';
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) ?? '';
$phone = trim($_POST['phone']) ?? '';
$dob = $_POST['dob'] ?? '';

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Si l'utilisateur veut changer le mot de passe
$changePassword = !empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword);

if ($changePassword) {
    // Vérifications pour le mot de passe
    if ($newPassword !== $confirmPassword) {
        echo "Le nouveau mot de passe ne correspond pas à la confirmation.";
        exit;
    }

    if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W]/', $newPassword)) {
        echo "Le mot de passe ne respecte pas les règles de sécurité.";
        exit;
    }

    // Vérifier le mot de passe actuel
    $result = $conn->prepare("SELECT password FROM users WHERE username=?");
    $result->bind_param("s", $id);
    $result->execute();
    $result->bind_result($hashedPassword);
    $result->fetch();
    $result->close();

    if (!password_verify($currentPassword, $hashedPassword)) {
        echo "Mot de passe actuel incorrect.";
        exit;
    }
}

// Vérification des doublons (seulement si username ou email sont fournis et non vides)
if (!empty($username) || !empty($email)) {
    $check = $conn->prepare("SELECT username FROM users WHERE (username = ? OR email = ?) AND username != ?");
    $checkUsername = !empty($username) ? $username : $id; // Si username est vide, on utilise l'ancien
    $checkEmail = !empty($email) ? $email : ''; // Si email est vide, on utilise une valeur par défaut
    $check->bind_param("sss", $checkUsername, $checkEmail, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Pseudo ou email déjà utilisé par un autre utilisateur.";
        exit;
    }
    $check->close();
}

// Mise à jour du profil (on met à jour uniquement les champs non vides)
$updateFields = [];
$updateValues = [];
$updateTypes = '';

if (!empty($fullname)) {
    $updateFields[] = "fullname=?";
    $updateValues[] = $fullname;
    $updateTypes .= "s";
}
if (!empty($username)) {
    $updateFields[] = "username=?";
    $updateValues[] = $username;
    $updateTypes .= "s";
}
if (!empty($email)) {
    $updateFields[] = "email=?";
    $updateValues[] = $email;
    $updateTypes .= "s";
}
if (!empty($phone)) {
    $updateFields[] = "phone=?";
    $updateValues[] = $phone;
    $updateTypes .= "s";
}
if (!empty($dob)) {
    $updateFields[] = "dob=?";
    $updateValues[] = $dob;
    $updateTypes .= "s";
}

// Ajouter la condition WHERE
$updateFields[] = "username=?";
$updateValues[] = $id;
$updateTypes .= "s";

// Si des champs sont à mettre à jour, exécuter la requête
if (count($updateFields) > 1) { // Plus de 1 car username=? est toujours présent
    $updateQuery = "UPDATE users SET " . implode(", ", array_slice($updateFields, 0, -1)) . " WHERE " . end($updateFields);
    $update = $conn->prepare($updateQuery);
    $update->bind_param($updateTypes, ...$updateValues);
    $update->execute();
    $update->close();
}

// Mise à jour du mot de passe si nécessaire
if ($changePassword) {
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updatePwd = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $updatePwd->bind_param("ss", $newHash, $id);
    $updatePwd->execute();
    $updatePwd->close();
}

echo "success";
$conn->close();
exit;
?>