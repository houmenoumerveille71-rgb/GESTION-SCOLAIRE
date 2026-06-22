<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Connexion à la nouvelle base de données Railway
    $bd = new PDO('mysql:host=thomas.proxy.rlwy.net;port=39219;dbname=railway;charset=utf8mb4', 'root', 'lzOfZqNYQJeNceSDQSLsgeyQeAQxjWtp');
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // En cas d'erreur, on affiche le vrai message pour comprendre ce qui cloche
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
