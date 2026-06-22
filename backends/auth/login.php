<?php
// =========================================================================
// 1. AUTORISATIONS CORS (Indispensable pour GitHub Pages)
// =========================================================================
header("Access-Control-Allow-Origin: https://houmenoumerveille71-rgb.github.io");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Si GitHub Pages envoie une requête de vérification "OPTIONS", on répond 200 et on s'arrête
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Connexion à la base de données Railway
require "../config/connexion.php";

// Récupération des données (gère à la fois le format Formulaire et le format JSON envoyé par fetch)
$inputData = json_decode(file_get_contents("php://input"), true);
$email_saisi = trim($inputData['email'] ?? $_POST['email'] ?? '');
$mdp_saisi = $inputData['mot_de_passe'] ?? $_POST['mot_de_passe'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validation des champs vides
    if (empty($email_saisi) || empty($mdp_saisi)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    // =========================================================================
    // CONFIGURATION DES IDENTIFIANTS EN DUR
    // =========================================================================
    $admin_email = "admin@ecole.com";
    $admin_mdp   = "Admin2026";

    $comptable_email = "comptable@ecole.com";
    $comptable_mdp   = "Compta2026";

    $caissier_email = "caissier@ecole.com";
    $caissier_mdp   = "Caisse2026";

    // =========================================================================
    // VÉRIFICATION DES IDENTIFIANTS ET REDIRECTION
    // =========================================================================
    $success = false;
    $redirectUrl = '';
    $user_nom = '';
    $user_role = '';

    if ($email_saisi === $admin_email && $mdp_saisi === $admin_mdp) {
        $success = true;
        $user_nom = "Administrateur Principal";
        $user_role = "admin";
        $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/dashboard_admin.html';
    } 
    elseif ($email_saisi === $comptable_email && $mdp_saisi === $comptable_mdp) {
        $success = true;
        $user_nom = "Gestionnaire Comptable";
        $user_role = "comptable";
        $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/index.html';
    } 
    elseif ($email_saisi === $caissier_email && $mdp_saisi === $caissier_mdp) {
        $success = true;
        $user_nom = "Caissier Service";
        $user_role = "caissier";
        $redirectUrl = 'https://houmenoumerveille71-rgb.github.io/GESTION-SCOLAIRE/frontends/index.html';
    }

    // =========================================================================
    // TRAITEMENT DU RÉSULTAT
    // =========================================================================
    if ($success) {
        $_SESSION['user_id']    = $user_role . '_fixed';
        $_SESSION['user_nom']   = $user_nom;
        $_SESSION['user_email'] = $email_saisi;
        $_SESSION['user_role']  = $user_role;

        // Optionnel : Tentative de journalisation ou log en BDD sans bloquer l'utilisateur
        try {
            if (isset($bd)) {
                $stmt = $bd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
                $stmt->execute([$email_saisi]);
                $user_bdd = $stmt->fetch();
                
                // Si l'utilisateur par défaut n'existe pas encore en BDD, on peut choisir 
                // de l'insérer automatiquement pour que ta BDD se remplisse toute seule !
                if (!$user_bdd) {
                    $insert = $bd->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
                    // Note : On sauvegarde le mot de passe hashé par sécurité en BDD
                    $insert->execute([$user_nom, $email_saisi, password_hash($mdp_saisi, PASSWORD_BCRYPT), $user_role]);
                }
            }
        } catch (Exception $e) {
            // Si la table n'existe pas encore ou bug de connexion, on laisse passer l'admin static
        }

        echo json_encode([
            'success'  => true,
            'message'  => 'Connexion réussie ! Autorisée par le système.',
            'redirect' => $redirectUrl,
            'user'     => [
                'nom'  => $user_nom,
                'role' => $user_role
            ]
        ]);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}
?>