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

    // Récupération des données
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validation basique
    if (empty($id) || empty($nom)) {
        $_SESSION['error'] = "L'ID et le nom sont requis";
        header("Location: ../../frontends/edit_enseignant.html?id=" . $id);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email invalide";
        header("Location: ../../frontends/edit_enseignant.html?id=" . $id);
        exit;
    }

    try {
        // Requête mise à jour avec préparation
        $q = $bd->prepare("
            UPDATE enseignants
            SET nom = ?, telephone = ?, email = ?, classe_id = ?
            WHERE id = ?
        ");

        $q->execute(array(
            $nom,
            $telephone,
            $email,
            $_POST['classe_id'],
            $id
        ));

        // Redirection vers la liste
        header("Location: ../../../frontends/liste_enseignants.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: email dupliqué)
        if ($e->getCode() == 23000) { // Erreur d'intégrité (duplicata)
            $_SESSION['error'] = "Un enseignant avec cet email existe déjà";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
        header("Location: ../../../frontends/edit_enseignant.html?id=" . $id);
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers la liste
    header("Location: ../../../frontends/liste_enseignants.html");
    exit;
}
?>
