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
    $eleve_id = $_POST['eleve_id'] ?? '';
    $matiere_id = $_POST['matiere_id'] ?? '';
    $note = $_POST['note'] ?? '';
    $semestre = $_POST['semestre'] ?? '';
    $annee_scolaire = $_POST['annee_scolaire'] ?? '';

    // Validation basique
    if (empty($id) || empty($eleve_id) || empty($matiere_id) || empty($note) || empty($semestre) || empty($annee_scolaire)) {
        $_SESSION['error'] = "Tous les champs sont requis";
        header("Location: ../../frontends/edit_note.html?id=" . $id);
        exit;
    }

    // Validation de la note (doit être un nombre entre 0 et 20)
    if (!is_numeric($note) || $note < 0 || $note > 20) {
        $_SESSION['error'] = "La note doit être un nombre entre 0 et 20";
        header("Location: ../../frontends/edit_note.html?id=" . $id);
        exit;
    }

    // Validation du semestre
    $semestres_valides = array('Semestre 1', 'Semestre 2');
    if (!in_array($semestre, $semestres_valides)) {
        $_SESSION['error'] = "Semestre invalide";
        header("Location: ../../frontends/edit_note.html?id=" . $id);
        exit;
    }

    // Validation de l'année scolaire (format simple: ex: 2024-2025)
    if (!preg_match('/^\d{4}-\d{4}$/', $annee_scolaire)) {
        $_SESSION['error'] = "L'année scolaire doit être au format AAAA-AAAA (ex: 2024-2025)";
        header("Location: ../../frontends/edit_note.html?id=" . $id);
        exit;
    }

    try {
        // Requête mise à jour avec préparation
        $q = $bd->prepare("
            UPDATE notes
            SET eleve_id = ?, matiere_id = ?, note = ?, semestre = ?, annee_scolaire = ?
            WHERE id = ?
        ");

        $q->execute(array(
            $eleve_id,
            $matiere_id,
            $note,
            $semestre,
            $annee_scolaire,
            $id
        ));

        // Redirection vers la liste
        header("Location: ../../frontends/liste_notes.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs
        $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
        header("Location: ../../frontends/edit_note.html?id=" . $id);
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers la liste
    header("Location: ../../frontends/liste_notes.html");
    exit;
}
?>
