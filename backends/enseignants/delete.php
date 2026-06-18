<?php

// Charger la connexion
require "../config/connexion.php";
require "../config/auth.php";

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!estConnecte()) {
    header("Location: ../frontends/connexion.html");
    exit;
}
if (!estAdmin()) {
    header("Location: ../frontends/acces_interdit.html");
    exit;
}

// Vérifier si l'id est fourni en GET
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Requête suppression avec préparation
        $q = $bd->prepare("DELETE FROM enseignants WHERE id = ?");
        $q->execute(array($id));

        // Redirection vers la liste
        header("Location: ../../frontends/liste_enseignants.html");
        exit;

    } catch (Exception $e) {
        // En cas d'erreur, on redirige quand même avec un message en session si possible
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        header("Location: ../../frontends/liste_enseignants.html");
        exit;
    }
} else {
    // Aucun id fourni, rediriger vers la liste
    header("Location: ../../frontends/liste_enseignants.html");
    exit;
}
?>