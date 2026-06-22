<?php
require "../config/connexion.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'utilisateur';

    // Basic validation
    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        echo json_encode(['success' => false, 'message' => 'Nom, email et mot de passe sont requis']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        exit;
    }

    if (strlen($mot_de_passe) < 6) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
        exit;
    }

    $validRoles = ['admin', 'administrateur', 'comptable', 'caissier', 'eleve', 'enseignant', 'utilisateur'];
    if (!in_array($role, $validRoles)) {
        $role = 'utilisateur';
    }

    try {
        // Check if email already exists
        $stmtCheck = $bd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insert the user
        $stmt = $bd->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $hashed_password, $role]);

        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user_id' => $bd->lastInsertId()
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
