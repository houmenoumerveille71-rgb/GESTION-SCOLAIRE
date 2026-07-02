<?php
// =========================================================================
// 1. GESTION STRICTE DU CORS (DOIT ÊTRE AU TOUT DÉBUT SANS AUCUN CARACTÈRE AVANT)
// =========================================================================

$allowed_origins = [
    'https://houmenoumerveille71-rgb.github.io',
    'https://gestion-scolaire-production-5e27.up.railway.app',
    'http://localhost',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://houmenoumerveille71-rgb.github.io");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Gestion immédiate du Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =========================================================================
// 2. CONFIGURATION ET CONNEXION
// =========================================================================
require "config/connexion.php";

// =========================================================================
// 3. ACTION : CHARGEMENT DES CLASSES (GET
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_classes') {
    header("Content-Type: application/json; charset=UTF-8");
    try {
        $stmt = $bd->query("SELECT code_classe as id, nom_classe as nom, montant_scolarite as frais_scolaire_defaut FROM classes ORDER BY code_classe ASC");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'classes' => $classes]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// 4. ACTION : MISE À JOUR DU TARIF (POST)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_tarif') {
    header("Content-Type: application/json; charset=UTF-8");
    
    $code_classe = isset($_POST['id_classe']) ? trim($_POST['id_classe']) : '';
    $montant     = isset($_POST['montant']) ? floatval($_POST['montant']) : -1;

    if (empty($code_classe) || $montant < 0) {
        echo json_encode(['success' => false, 'message' => 'Données reçues incorrectes ou incomplètes.']);
        exit;
    }

    try {
        $sql = "UPDATE classes SET montant_scolarite = ? WHERE code_classe = ?";
        $stmt = $bd->prepare($sql);
        $stmt->execute([$montant, $code_classe]);

        echo json_encode([
            'success' => true,
            'message' => "Le tarif de la classe " . $code_classe . " a été modifié à " . number_format($montant, 0, ',', ' ') . " FCFA."
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}

// Si aucune action ne correspond
header("Content-Type: application/json; charset=UTF-8");
echo json_encode(['success' => false, 'message' => 'Action ou méthode non autorisée.']);
exit;
?>