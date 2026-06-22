<?php
// Charger la connexion
require "../config/connexion.php";

// 1. Correction de la Notice : On vérifie si la session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if (isset($_POST['ajouter'])) {
    // Récupération sécurisée des données (évite les warnings si un champ est vide)
    $matricule        = isset($_POST['matricule']) ? trim($_POST['matricule']) : '';
    $nom              = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $prenom           = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $sexe             = isset($_POST['sexe']) ? trim($_POST['sexe']) : '';
    $date_naissance   = isset($_POST['date_naissance']) ? trim($_POST['date_naissance']) : '';
    $nom_parent       = isset($_POST['nom_parent']) ? trim($_POST['nom_parent']) : '';
    $telephone_parent = isset($_POST['telephone_parent']) ? trim($_POST['telephone_parent']) : '';
    $adresse          = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';
    $code_classe      = isset($_POST['code_classe']) ? trim($_POST['code_classe']) : ''; // C'est ici que le warning se produisait

    // Validation rapide
    if (empty($nom) || empty($prenom) || empty($code_classe)) {
        echo '<div class="alert alert-danger">Erreur : Le nom, le prénom et la classe sont obligatoires.</div>';
        exit;
    }

    try {
        // Démarrer transaction
        $bd->beginTransaction();

        // Requête insertion élève (Vérifie bien que ta table 'eleves' contient exactement ces colonnes)
        $q = $bd->prepare("
            INSERT INTO eleves (
                matricule,
                nom,
                prenom,
                sexe,
                date_naissance,
                nom_parent,
                telephone_parent,
                adresse,
                code_classe
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $q->execute([
            $matricule,
            $nom,
            $prenom,
            $sexe,
            $date_naissance,
            $nom_parent,
            $telephone_parent,
            $adresse,
            $code_classe
        ]);

        $eleve_id = $bd->lastInsertId();

        // Récupérer les frais scolaires par défaut de la classe (Basé sur ton phpMyAdmin : 'montant_scolarite')
        $fraisQuery = $bd->prepare("SELECT * FROM classes WHERE code_classe = ?");
        $fraisQuery->execute([$code_classe]);
        $frais_scolaire = $fraisQuery->fetchColumn();

        // Si le montant n'est pas trouvé, on met 0 par défaut pour éviter un plantage
        if ($frais_scolaire === false) {
            $frais_scolaire = 0;
        }

        // Déterminer l'année scolaire en cours
        $annee_scolaire = date('Y');

        // Insérer ou mettre à jour les frais scolaires pour l'élève (Vérifie que la table s'appelle bien 'frais_scolaires')
        $fraisInsert = $bd->prepare("
            INSERT INTO frais_scolaires (eleve_id, montant_total, annee_scolaire)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE montant_total = VALUES(montant_total), annee_scolaire = VALUES(annee_scolaire)
        ");
        $fraisInsert->execute([$eleve_id, $frais_scolaire, $annee_scolaire]);

        // Valider la transaction
        $bd->commit();

        // Redirection vers la liste
        header("Location: ../../frontends/liste.html");
        exit;

    } catch (Exception $e) {
        // Annuler les modifications en cas de bug SQL
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        echo '<div class="alert alert-danger">Erreur lors de l\'ajout: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Erreur : Le formulaire n'."'".'a pas été soumis correctement.</div>';
}
?>
