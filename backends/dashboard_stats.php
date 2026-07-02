<?php
// =========================================================================
// 1. GESTION STRICTE DU CORS
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
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =========================================================================
// 2. CONNEXION À LA BASE DE DONNÉES
// =========================================================================
require "config/connexion.php";

try {
    // 1. Compter le nombre total d'élèves
    $stmtEleves = $bd->query("SELECT COUNT(*) as total_eleves FROM eleves");
    $resEleves = $stmtEleves->fetch(PDO::FETCH_ASSOC);
    $totalEleves = $resEleves['total_eleves'] ?? 0;

    // 2. Calculer le total encaissé depuis les frais scolaires / paiements
    // Note : Remplace "frais_scolaires" et "montant_paye" par tes vrais noms de table/colonne si nécessaire
    $stmtEncaisse = $bd->query("SELECT SUM(montant_paye) as total_recette FROM frais_scolaires");
    $resEncaisse = $stmtEncaisse->fetch(PDO::FETCH_ASSOC);
    $totalRecette = $resEncaisse['total_recette'] ?? 0;

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'total_eleves' => intval($totalEleves),
        'total_encaisse' => floatval($totalRecette)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ]);
}
exit;
?>