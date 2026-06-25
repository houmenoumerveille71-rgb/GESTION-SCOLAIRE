<?php
// =========================================================================
// 1. CONFIGURATION CORS POUR GITHUB PAGES (Indispensable)
// =========================================================================
header("Access-Control-Allow-Origin: https://houmenoumerveille71-rgb.github.io");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Gestion de la pré-vérification du navigateur
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =========================================================================
// 2. CONNEXION À LA BASE DE DONNÉES
// =========================================================================
require "../config/connexion.php";

// Récupération des données brutes envoyées en JSON par le Fetch
$inputData = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($inputData)) {
    
    // Récupération et nettoyage des valeurs
    $matricule        = isset($inputData['matricule']) ? trim($inputData['matricule']) : '';
    $nom              = isset($inputData['nom']) ? trim($inputData['nom']) : '';
    $prenom           = isset($inputData['prenom']) ? trim($inputData['prenom']) : '';
    $sexe             = isset($inputData['sexe']) ? trim($inputData['sexe']) : '';
    $date_naissance   = isset($inputData['date_naissance']) ? trim($inputData['date_naissance']) : '';
    $nom_parent       = isset($inputData['nom_parent']) ? trim($inputData['nom_parent']) : '';
    $telephone_parent = isset($inputData['telephone_parent']) ? trim($inputData['telephone_parent']) : '';
    $adresse          = isset($inputData['adresse']) ? trim($inputData['adresse']) : '';
    $code_classe      = isset($inputData['code_classe']) ? trim($inputData['code_classe']) : '';

    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($code_classe)) {
        echo json_encode(['success' => false, 'message' => 'Le nom, le prénom et la classe sont obligatoires.']);
        exit;
    }

    try {
        $bd->beginTransaction();

        // 1. Insertion de l'élève
        $q = $bd->prepare("
            INSERT INTO eleves (matricule, nom, prenom, sexe, date_naissance, nom_parent, telephone_parent, adresse, code_classe)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $q->execute([$matricule, $nom, $prenom, $sexe, $date_naissance, $nom_parent, $telephone_parent, $adresse, $code_classe]);

        $eleve_id = $bd->lastInsertId();

        // 2. Récupération des frais de scolarité associés à cette classe
        $fraisQuery = $bd->prepare("SELECT montant_scolarite FROM classes WHERE code_classe = ?");
        $fraisQuery->execute([$code_classe]);
        $classe_data = $fraisQuery->fetch(PDO::FETCH_ASSOC);

        // Si la classe ou le montant n'existe pas, on met 0 par défaut pour éviter un crash
        $frais_scolaire = isset($classe_data['montant_scolarite']) ? $classe_data['montant_scolarite'] : 0;
        $annee_scolaire = date('Y');

        // 3. Insertion automatique dans la table des frais scolarité
        $fraisInsert = $bd->prepare("
            INSERT INTO frais_scolaires (eleve_id, montant_total, annee_scolaire)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE montant_total = VALUES(montant_total), annee_scolaire = VALUES(annee_scolaire)
        ");
        $fraisInsert->execute([$eleve_id, $frais_scolaire, $annee_scolaire]);

        $bd->commit();

        // Réponse positive renvoyée au JavaScript
        echo json_encode(['success' => true, 'message' => 'Élève enregistré avec succès !']);
        exit;

    } catch (Exception $e) {
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Erreur SQL lors de l\'ajout : ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue ou méthode invalide.']);
    exit;
}
?>