<?php
// Charger la connexion
require "../config/connexion.php";
require "../config/auth.php";

// Check if user is logged in and is admin
if (!estConnecte()) {
    header("Location: ../../frontends/connexion.html");
    exit;
}
if (!estAdmin()) {
    header("Location: ../../frontends/acces_interdit.html");
    exit;
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $id = $_POST['id'] ?? '';
    $classe_id = $_POST['classe_id'] ?? '';
    $matiere_id = $_POST['matiere_id'] ?? '';

    // Validation basique
    if (empty($id) || empty($classe_id) || empty($matiere_id)) {
        $_SESSION['error'] = "Tous les champs sont requis";
        header("Location: ../../../frontends/edit_classe_matiere.html?id=$id");
        exit;
    }

    try {
        // Vérifier si l'association existe déjà (pour éviter les doublons en mise à jour)
        $check = $bd->prepare("SELECT id FROM classe_matieres WHERE classe_id = ? AND matiere_id = ? AND id != ?");
        $check->execute(array($classe_id, $matiere_id, $id));
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Cette association existe déjà";
            header("Location: ../../../frontends/edit_classe_matiere.html?id=$id");
            exit;
        }

        // Requête mise à jour avec préparation
        $q = $bd->prepare("
            UPDATE classe_matieres SET
                classe_id = ?,
                matiere_id = ?
            WHERE id = ?
        ");

        $q->execute(array(
            $classe_id,
            $matiere_id,
            $id
        ));

        // Redirection vers la liste
        header("Location: ../../../frontends/liste_classes_matieres.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: clé étrangère invalide)
        if ($e->getCode() == 23000) { // Erreur d'intégrité
            $_SESSION['error'] = "Classe ou matière invalide";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
        header("Location: ../../../frontends/edit_classe_matiere.html?id=$id");
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers la liste
    header("Location: ../../../frontends/liste_classes_matieres.html");
    exit;
}
?>
