<?php
// =========================================================================
// 1. AUTORISATIONS CORS (Indispensable pour GitHub Pages)
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

require "../config/connexion.php";

$inputData = json_decode(file_get_contents("php://input"), true);
$email_saisi = trim($inputData['email'] ?? $_POST['email'] ?? '');
$mdp_saisi = $inputData['mot_de_passe'] ?? $_POST['mot_de_passe'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (empty($email_saisi) || empty($mdp_saisi)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
    exit;
}

try {
    $stmt = $bd->prepare("SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = ? LIMIT 1");
    $stmt->execute([$email_saisi]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($mdp_saisi, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    switch ($user['role']) {
        case 'admin':
            $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/dashboard_admin.html';
            break;
        case 'comptable':
            $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/dashboard_comptable.html';
            break;
        case 'caissier':
            $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/dashboard_caissier.html';
            break;
        default:
            $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/index.html';
            break;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie.',
        'redirect' => $redirectUrl,
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
