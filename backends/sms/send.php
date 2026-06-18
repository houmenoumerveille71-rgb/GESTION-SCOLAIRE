<?php
require "../config/connexion.php";
require "../config/auth.php";
header("Content-Type: application/json");

// Check if user is logged in
if (!estConnecte()) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

if (!estAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Privilèges administrateur requis']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleve_id = $_POST['eleve_id'] ?? '';
    $type = $_POST['type'] ?? 'notification';
    
    if (empty($eleve_id)) {
        echo json_encode(['success' => false, 'message' => 'ID élève requis']);
        exit;
    }
    
    try {
        // Get student and parent phone number
        $stmt = $bd->prepare("
            SELECT e.telephone_parent, e.nom, e.prenom, 
                   COALESCE(fs.montant_total, 0) as total_du,
                   COALESCE(SUM(p.montant_verse), 0) as total_paye,
                   (COALESCE(fs.montant_total, 0) - COALESCE(SUM(p.montant_verse), 0)) as reste_a_payer
            FROM eleves e
            LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
            LEFT JOIN paiements p ON e.id = p.eleve_id
            WHERE e.id = ?
            GROUP BY e.id, e.telephone_parent, e.nom, e.prenom
        ");
        $stmt->execute([$eleve_id]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$eleve) {
            echo json_encode(['success' => false, 'message' => 'Élève non trouvé']);
            exit;
        }
        
        $telephone = $eleve['telephone_parent'];
        $nomEleve = $eleve['nom'] . ' ' . $eleve['prenom'];
        $resteAPayer = $eleve['reste_a_payer'];
        
        // Validate phone number
        if (empty($telephone) || !preg_match('/^\+?[\d\s\-\(\)]{10,}$/', $telephone)) {
            echo json_encode(['success' => false, 'message' => 'Numéro de téléphone invalide ou manquant']);
            exit;
        }
        
        // In a real implementation, you would integrate with an SMS gateway API here
        // For this example, we'll simulate the SMS sending
        
        // Simulate SMS sending (replace with actual SMS gateway integration)
        $message = "Bonjour, ceci est un rappel de paiement pour $nomEleve. Reste à payer: $resteAPayer FCFA. Merci de régler rapidement.";
        
        // Log the SMS attempt (in a real app, you'd log to database or file)
        error_log("SMS envoyé à $telephone: $message");
        
        // Simulate success (in real implementation, this would depend on API response)
        echo json_encode([
            'success' => true, 
            'message' => 'SMS envoyé avec succès',
            'data' => [
                'telephone' => $telephone,
                'message' => $message
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>