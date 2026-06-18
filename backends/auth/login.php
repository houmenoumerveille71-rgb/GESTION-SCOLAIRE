<?php
// 1. Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Connexion à la base de données (si tu veux quand même faire une vérification ou un enregistrement)
require "../config/connexion.php";

// 3. Déclaration du format de réponse en JSON
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données saisies par l'utilisateur
    $email_saisi = trim($_POST['email'] ?? '');
    $mdp_saisi = $_POST['mot_de_passe'] ?? '';

    // Validation des champs vides
    if (empty($email_saisi) || empty($mdp_saisi)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    // =========================================================================
    // CONFIGURATION DES IDENTIFIANTS EN DUR (Tu peux modifier les valeurs ici)
    // =========================================================================
    
    // 1. Identifiants de l'Administrateur
    $admin_email = "admin@ecole.com";
    $admin_mdp   = "Admin2026"; // Ton mot de passe en clair pour le test en dur

    // 2. Identifiants du Comptable
    $comptable_email = "comptable@ecole.com";
    $comptable_mdp   = "Compta2026";

    // 3. Identifiants du Caissier
    $caissier_email = "caissier@ecole.com";
    $caissier_mdp   = "Caisse2026";

    // =========================================================================
    // VÉRIFICATION DES IDENTIFIANTS ET REDIRECTION
    // =========================================================================

    $success = false;
    $redirectUrl = '';
    $user_nom = '';
    $user_role = '';

    // Cas 1 : C'est l'Administrateur
    if ($email_saisi === $admin_email && $mdp_saisi === $admin_mdp) {
        $success = true;
        $user_nom = "Administrateur Principal";
        $user_role = "admin";
        $redirectUrl = '../frontends/dashboard_admin.html';
    } 
    // Cas 2 : C'est le Comptable
    elseif ($email_saisi === $comptable_email && $mdp_saisi === $comptable_mdp) {
        $success = true;
        $user_nom = "Gestionnaire Comptable";
        $user_role = "comptable";
        $redirectUrl = '../frontends/index.html';
    } 
    // Cas 3 : C'est le Caissier
    elseif ($email_saisi === $caissier_email && $mdp_saisi === $caissier_mdp) {
        $success = true;
        $user_nom = "Caissier Service";
        $user_role = "caissier";
        $redirectUrl = '../frontends/index.html';
    }

    // =========================================================================
    // TRAITEMENT DU RÉSULTAT
    // =========================================================================

    if ($success) {
        // Enregistrement dans la session PHP pour sécuriser les pages suivantes
        $_SESSION['user_id']    = $user_role . '_fixed'; // Génère un ID fictif pour la session
        $_SESSION['user_nom']   = $user_nom;
        $_SESSION['user_email'] = $email_saisi;
        $_SESSION['user_role']  = $user_role;

        // Optionnel : Si tu veux quand même vérifier que l'utilisateur existe dans la BDD,
        // tu peux laisser cette requête, sinon tu peux l'enlever.
        try {
            $stmt = $bd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email_saisi]);
            $user_bdd = $stmt->fetch();
            
            if (!$user_bdd) {
                // Si l'email n'est pas trouvé du tout dans la table SQL
                echo json_encode(['success' => false, 'message' => 'Identifiants valides mais utilisateur absent de la base de données.']);
                exit;
            }
        } catch (Exception $e) {
            // Évite de bloquer si la BDD a un problème passager
        }

        // Envoi de la réponse positive avec le lien vers sa page dédiée
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
        // Si aucun des trois blocs 'if' ou 'elseif' n'a fonctionné
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect pour ce profil.']);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}
?>