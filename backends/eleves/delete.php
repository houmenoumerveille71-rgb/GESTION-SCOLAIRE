<?php
require "../config/connexion.php";
require "../config/auth.php";

// Check if user is logged in
if (!estConnecte()) {
    // If it's an AJAX request, return JSON error; else redirect to login
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    } else {
        header("Location: ../frontends/connexion.html");
        exit;
    }
}

// Supprimer un élève
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Basic validation
    if (!is_numeric($id) || $id <= 0) {
        echo "ID invalide";
        exit;
    }
    
    $q = $bd->prepare("DELETE FROM eleves WHERE id = ?");
    $success = $q->execute(array($id));
    
    if ($success) {
        header("Location: ../../frontends/liste.html");
        exit;
    } else {
        echo "Erreur suppression";
    }
} else {
    echo "ID manquant";
}
?>