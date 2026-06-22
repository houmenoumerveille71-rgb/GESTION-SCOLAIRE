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
    header("Location: ../../frontends/connexion.html");
    exit;
}
if (!estAdmin()) {
    header("Location: ../../frontends/acces_interdit.html");
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $id = $_POST['id'] ?? '';
    $nom_matiere = $_POST['nom_matiere'] ?? '';

    // Validation basique
    if (empty($id) || empty($nom_matiere)) {
        $_SESSION['error'] = "L'ID et le nom de la matière sont requis";
        header("Location: ../../../frontends/edit_matiere.html?id=" . $id);
        exit;
    }

    try {
        // Requête mise à jour avec préparation
        $q = $bd->prepare("
            UPDATE matieres
            SET nom_matiere = ?
            WHERE id = ?
        ");

        $q->execute(array(
            $nom_matiere,
            $id
        ));

        // Redirection vers la liste
        header("Location: ../../../frontends/liste_matieres.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: nom dupliqué)
        if ($e->getCode() == 23000) { // Erreur d'intégrité (duplicata)
            $_SESSION['error'] = "Une matière avec ce nom existe déjà";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
        header("Location: ../../../frontends/edit_matiere.html?id=" . $id);
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers la liste
    header("Location: ../../../frontends/liste_matieres.html");
    exit;
}
?>
