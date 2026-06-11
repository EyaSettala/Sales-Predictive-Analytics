
<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['nom']);
    $username = trim($_POST['pseudo']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['tel']);
    $dob = $_POST['dob'];
    $password = $_POST['mdp'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($dob) || empty($password)) {
        echo "Tous les champs sont obligatoires.";
        exit;
    }

    if ($password !== $confirmPassword) {
        echo "Les mots de passe ne correspondent pas.";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->bind_result($existing_username, $existing_email);
        $check->fetch();

        if ($username === $existing_username) {
            echo "Ce pseudo est déjà utilisé.";
        } elseif ($email === $existing_email) {
            echo "Cette adresse email est déjà utilisée.";
        } else {
            echo "Pseudo ou email déjà utilisé.";
        }
        exit;
    }

    $insert = $conn->prepare("INSERT INTO users (fullname, username, email, phone, dob, password) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssss", $fullname, $username, $email, $phone, $dob, $passwordHash);

    if ($insert->execute()) {
        echo "success";
    } else {
        echo "Erreur lors de l'inscription. Veuillez réessayer.";
    }
    exit;
}
?>
