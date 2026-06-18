<?php
// Charger la connexion
require "../config/connexion.php";
require "../config/auth.php";

// Check if user is logged in and is admin
if (!estConnecte()) {
    header("Location: ../frontends/connexion.html");
    exit;
}
if (!estAdmin()) {
    header("Location: ../frontends/acces_interdit.html");
    exit;
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $classe_id = $_POST['classe_id'] ?? '';
    $matiere_id = $_POST['matiere_id'] ?? '';

    // Validation basique
    if (empty($classe_id) || empty($matiere_id)) {
        $_SESSION['error'] = "La classe et la matière sont requises";
        header("Location: ../../frontends/ajout_classe_matiere.html");
        exit;
    }

    try {
        // Vérifier si l'association existe déjà
        $check = $bd->prepare("SELECT id FROM classe_matieres WHERE classe_id = ? AND matiere_id = ?");
        $check->execute(array($classe_id, $matiere_id));
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Cette association existe déjà";
            header("Location: ../../frontends/ajout_classe_matiere.html");
            exit;
        }

        // Requête insertion avec préparation
        $q = $bd->prepare("
            INSERT INTO classe_matieres(
                classe_id,
                matiere_id
            )
            VALUES(?,?)
        ");

        $q->execute(array(
            $classe_id,
            $matiere_id
        ));

        // Redirection vers la liste
        header("Location: ../../frontends/liste_classes_matieres.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: clé étrangère invalide)
        if ($e->getCode() == 23000) { // Erreur d'intégrité
            $_SESSION['error'] = "Classe ou matière invalide";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout: " . $e->getMessage();
        }
        header("Location: ../../frontends/ajout_classe_matiere.html");
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers le formulaire
    header("Location: ../../frontends/ajout_classe_matiere.html");
    exit;
}
?>