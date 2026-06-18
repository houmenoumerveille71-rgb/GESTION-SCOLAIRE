<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $bd = new PDO('mysql:host=localhost;dbname=gestions_ecole', 'root', '');
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion à la base de données");
}
?>
