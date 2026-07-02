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

$totalEleves = 0;
$totalRecette = 0;
$errors = [];

// 1. Requête pour les élèves (isolée)
try {
    $stmtEleves = $bd->query("SELECT COUNT(*) as total_eleves FROM eleves");
    $resEleves = $stmtEleves->fetch(PDO::FETCH_ASSOC);
    $totalEleves = $resEleves['total_eleves'] ?? 0;
} catch (Exception $e) {
    $errors[] = "Erreur élèves: " . $e->getMessage();
}

// 2. Requête pour les recettes (isolée)
try {
    // Si l'erreur persiste, tu pourras modifier 'frais_scolaires' ou 'montant' ici
    $stmtEncaisse = $bd->query("SELECT SUM(montant) as total_recette FROM frais_scolaires");
    $resEncaisse = $stmtEncaisse->fetch(PDO::FETCH_ASSOC);
    $totalRecette = $resEncaisse['total_recette'] ?? 0;
} catch (Exception $e) {
    $errors[] = "Erreur recettes: " . $e->getMessage();
}

// Réponse JSON (success est vrai si au moins les élèves fonctionnent)
echo json_encode([
    'success' => (count($errors) === 0),
    'total_eleves' => intval($totalEleves),
    'total_encaisse' => floatval($totalRecette),
    'message' => implode(" | ", $errors)
]);
exit;
?>