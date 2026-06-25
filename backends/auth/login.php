<?php
// =========================================================================
// 1. AUTORISATIONS CORS & EN-TÊTES (Pour sécuriser les requêtes cross-origin)
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

// Gestion de la requête de pré-vérification OPTIONS
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

// Connexion à la base de données
require "../config/connexion.php";

// =========================================================================
// 3. RÉCUPÉRATION ET SÉCURISATION DES DONNÉES SRAISIES
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non autorisée.']);
    exit;
}

// Lecture du flux JSON envoyé par ton code frontend actuel
$inputData = json_decode(file_get_contents("php://input"), true);

// Extraction des champs basés sur les attributs `name` du formulaire HTML
$email_saisi = trim($inputData['email'] ?? $_POST['email'] ?? '');
$mdp_saisi = $inputData['mot_de_passe'] ?? $_POST['mot_de_passe'] ?? '';

if (empty($email_saisi) || empty($mdp_saisi)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
    exit;
}

try {
    // =========================================================================
    // 4. VÉRIFICATION DANS LA BASE DE DONNÉES
    // =========================================================================
    $stmt = $bd->prepare("SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = ? LIMIT 1");
    $stmt->execute([$email_saisi]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur n'existe pas ou que le mot de passe hashé ne correspond pas
    if (!$user || !password_verify($mdp_saisi, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Identifiants ou mot de passe incorrect.']);
        exit;
    }

    // Enregistrement des données utilisateur dans la session PHP
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // =========================================================================
    // 5. LOGIQUE DE ROUTAGE ET REDIRECTION PAR RÔLE
    // =========================================================================
    $baseUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends';

    switch ($user['role']) {
        case 'admin':
            $redirectUrl = $baseUrl . '/dashboard_admin.html';
            break;
        case 'comptable':
            $redirectUrl = $baseUrl . '/dashboard_comptable.html';
            break;
        case 'secretaire':
        case 'secrétaire': // Gère l'éventuel accent dans ton champ de rôle
            $redirectUrl = $baseUrl . '/dashboard_secretaire.html';
            break;
        case 'caissier':
            $redirectUrl = $baseUrl . '/dashboard_caissier.html';
            break;
        default:
            $redirectUrl = $baseUrl . '/index.html';
            break;
    }

    // Réponse de succès interceptée par ton fetch()
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie ! Redirection en cours...',
        'redirect' => $redirectUrl,
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur critique serveur : ' . $e->getMessage()]);
}
?>