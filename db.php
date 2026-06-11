<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // ou ton mot de passe
$dbname = 'ang_decision';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
