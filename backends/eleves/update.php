<?php
// Charger la connexion
require "../config/connexion.php";

// 1. Sécurisation de la session
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

// Vérifier si le bouton modifier existe
if (isset($_POST['modifier'])) {
    // Récupération des données
    $id = $_POST['id'];
    
    $matricule        = $_POST['matricule'];
    $nom              = $_POST['nom'];
    $prenom           = $_POST['prenom'];
    $sexe             = $_POST['sexe'];
    $date_naissance   = $_POST['date_naissance'];
    $nom_parent       = $_POST['nom_parent'];
    $telephone_parent = $_POST['telephone_parent'];
    $adresse          = $_POST['adresse'];
    $code_classe      = $_POST['code_classe']; // CORRECTION : classe_id -> code_classe

    try {
        // Démarrer transaction
        $bd->beginTransaction();

        // Vérifier si une nouvelle photo est envoyée
        if (!empty($_FILES['photo']['name'])) {
            $photo = basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], '../photos/' . $photo);
            
            // CORRECTION SQL : classe_id -> code_classe
            $q = $bd->prepare("
                UPDATE eleves SET
                    matricule = ?,
                    nom = ?,
                    prenom = ?,
                    sexe = ?,
                    date_naissance = ?,
                    photo = ?,
                    nom_parent = ?,
                    telephone_parent = ?,
                    adresse = ?,
                    code_classe = ?
                WHERE id = ?
            ");
            $q->execute(array(
                $matricule, $nom, $prenom, $sexe, $date_naissance,
                $photo, $nom_parent, $telephone_parent, $adresse, $code_classe, $id
            ));
        } else {
            // Requête sans modifier la photo (CORRECTION : classe_id -> code_classe)
            $q = $bd->prepare("
                UPDATE eleves SET
                    matricule = ?,
                    nom = ?,
                    prenom = ?,
                    sexe = ?,
                    date_naissance = ?,
                    nom_parent = ?,
                    telephone_parent = ?,
                    adresse = ?,
                    code_classe = ?
                WHERE id = ?
            ");
            $q->execute(array(
                $matricule, $nom, $prenom, $sexe, $date_naissance,
                $nom_parent, $telephone_parent, $adresse, $code_classe, $id
            ));
        }

        // CORRECTION SQL : Sélection des frais avec les vrais noms de colonnes ('montant_scolarite' et 'code_classe')
        $fraisQuery = $bd->prepare("SELECT montant_scolarite FROM classes WHERE code_classe = ?");
        $fraisQuery->execute([$code_classe]);
        $frais_scolaire = $fraisQuery->fetchColumn();

        // Sécurité si la classe n'est pas trouvée
        if ($frais_scolaire === false) {
            $frais_scolaire = 0;
        }

        // Déterminer l'année scolaire
        $annee_scolaire = date('Y');

        // Insérer ou mettre à jour les frais scolaires pour l'élève
        $fraisInsert = $bd->prepare("
            INSERT INTO frais_scolaires (eleve_id, montant_total, annee_scolaire)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE montant_total = VALUES(montant_total), annee_scolaire = VALUES(annee_scolaire)
        ");
        $fraisInsert->execute([$id, $frais_scolaire, $annee_scolaire]);

        // Valider la transaction
        $bd->commit();

        header("Location: ../../frontends/liste.html");
        exit;

    } catch (Exception $e) {
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        echo '<div class="alert alert-danger">Erreur lors de la modification: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Accès non autorisé</div>';
}
?>
