<?php
// Charger la connexion
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
        header("Location: ../../frontends/connexion.html");
        exit;
    }
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $nom_matiere = $_POST['nom_matiere'] ?? '';

    // Validation basique
    if (empty($nom_matiere)) {
        $_SESSION['error'] = "Le nom de la matière est requis";
        header("Location: ../../../frontends/ajout_matiere.html");
        exit;
    }

    try {
        // Requête insertion avec préparation
        $q = $bd->prepare("
            INSERT INTO matieres(
                nom_matiere
            )
            VALUES(?)
        ");

        $q->execute(array(
            $nom_matiere
        ));

        // Redirection vers la liste
        header("Location: ../../../frontends/liste_matieres.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: nom dupliqué)
        if ($e->getCode() == 23000) { // Erreur d'intégrité (duplicata)
            $_SESSION['error'] = "Une matière avec ce nom existe déjà";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout: " . $e->getMessage();
        }
        header("Location: ../../../frontends/ajout_matiere.html");
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers le formulaire
    header("Location: ../../../frontends/ajout_matiere.html");
    exit;
}
?>
