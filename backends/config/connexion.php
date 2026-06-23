<?php
// Headers CORS pour autoriser les requêtes depuis GitHub Pages
header("Access-Control-Allow-Origin: https://houmenoumerveille71-rgb.github.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");

// Répondre aux requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
