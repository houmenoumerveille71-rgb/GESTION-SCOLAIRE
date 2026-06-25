<?php
// =========================================================================
// 1. AUTORISATIONS CORS & EN-TÊTES
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
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =========================================================================
// 2. INITIALISATION DE LA SESSION
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
    session_start();
}

// NOTE : Plus besoin de require "../config/connexion.php" si tu n'utilises plus la BD ici.

// =========================================================================
// 3. RÉCUPÉRATION DES DONNÉES SAISIES
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non autorisée.']);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);
$email_saisi = trim($inputData['email'] ?? $_POST['email'] ?? '');
$mdp_saisi = $inputData['mot_de_passe'] ?? $_POST['mot_de_passe'] ?? '';

if (empty($email_saisi) || empty($mdp_saisi)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
    exit;
}

// =========================================================================
// 4. CONFIGURATION DE TES IDENTIFIANTS (Modifie les valeurs ici ✍️)
// =========================================================================
$admin_email = "admin@ecole.com";
$admin_mdp   = "Admin2026!"; // Ton mot de passe admin

$comptable_email = "comptable@ecole.com";
$comptable_mdp   = "Compta2026!"; // Ton mot de passe comptable

$secretaire_email = "secretaire@ecole.com";
$secretaire_mdp   = "Secret2026!"; // Ton mot de passe secrétaire

$caissier_email = "caissier@ecole.com";
$caissier_mdp   = "Caisse2026!"; // Ton mot de passe caissier


// Variable qui contiendra les infos du compte validé
$user = null;

// =========================================================================
// 5. VÉRIFICATION SANS BASE DE DONNÉES
// =========================================================================
if ($email_saisi === $admin_email && $mdp_saisi === $admin_mdp) {
    $user = ['id' => 1, 'nom' => 'Directeur', 'email' => $admin_email, 'role' => 'admin'];
} 
define_role_comptable:
if ($email_saisi === $comptable_email && $mdp_saisi === $comptable_mdp) {
    $user = ['id' => 2, 'nom' => 'Service Comptabilité', 'email' => $comptable_email, 'role' => 'comptable'];
} 
define_role_secretaire:
if ($email_saisi === $secretaire_email && $mdp_saisi === $secretaire_mdp) {
    $user = ['id' => 3, 'nom' => 'Secrétariat Général', 'email' => $secretaire_email, 'role' => 'secretaire'];
} 
define_role_caissier:
if ($email_saisi === $caissier_email && $mdp_saisi === $caissier_mdp) {
    $user = ['id' => 4, 'nom' => 'Caisse', 'email' => $caissier_email, 'role' => 'caissier'];
}

// Si aucun compte ne correspond aux identifiants définis au-dessus
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
    exit;
}

// Stockage en session PHP
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_nom']   = $user['nom'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];

// =========================================================================
// 6. ADRESSES DE REDIRECTION (Selon tes demandes précédentes)
// =========================================================================
$baseUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends';

switch ($user['role']) {
    case 'admin':
        $redirectUrl = $baseUrl . '/dashboard_admin.html';
        break;
    case 'comptable':
        $redirectUrl = $baseUrl . '/index.html';
        break;
    case 'secretaire':
        $redirectUrl = $baseUrl . '/index.html';
        break;
    case 'caissier':
        $redirectUrl = $baseUrl . '/index.html';
        break;
    
}

// Réponse JSON renvoyée au JavaScript de ta page de connexion
echo json_encode([
    'success' => true,
    'message' => 'Connexion réussie ! Redirection en cours...',
    'redirect' => $redirectUrl,
    'user' => $user
]);
?>