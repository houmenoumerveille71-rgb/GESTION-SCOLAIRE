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

// Démarrer la session (déjà fait dans connexion.php, mais on s'assure)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Démarrer la session (déjà fait dans connexion.php, mais on s'assure)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $nom = $_POST['nom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validation basique
    if (empty($nom)) {
        $_SESSION['error'] = "Le nom est requis";
        header("Location: ../../frontends/ajout_enseignant.html");
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email invalide";
        header("Location: ../../frontends/ajout_enseignant.html");
        exit;
    }

    try {
        // Requête insertion avec préparation
        $q = $bd->prepare("
            INSERT INTO enseignants(
                nom,
                telephone,
                email,
                classe_id
            )
            VALUES(?,?,?,?)
        ");

        $q->execute(array(
            $nom,
            $telephone,
            $email,
            $_POST['classe_id']
        ));

        // Redirection vers la liste
        header("Location: ../../frontends/liste_enseignants.html");
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs (ex: email dupliqué)
        if ($e->getCode() == 23000) { // Erreur d'intégrité (duplicata)
            $_SESSION['error'] = "Un enseignant avec cet email existe déjà";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout: " . $e->getMessage();
        }
        header("Location: ../../frontends/ajout_enseignant.html");
        exit;
    }
} else {
    // Accès direct en GET - rediriger vers le formulaire
    header("Location: ../../frontends/ajout_enseignant.html");
    exit;
}
?>
