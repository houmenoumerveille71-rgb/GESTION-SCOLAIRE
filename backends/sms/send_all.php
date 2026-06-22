<?php
require "../config/connexion.php";
require "../config/auth.php";
header("Content-Type: application/json");

// Check if user is logged in
if (!estConnecte()) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

// Optional: Check if user has appropriate permissions for sending SMS
if (!estAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Privilèges administrateur requis']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get all insolvent students with valid phone numbers
        $sql = "SELECT 
                    e.id,
                    e.nom,
                    e.prenom,
                    e.telephone_parent,
                    COALESCE(fs.montant_total, 0) as total_du,
                    COALESCE(SUM(p.montant_verse), 0) as total_paye,
                    (COALESCE(fs.montant_total, 0) - COALESCE(SUM(p.montant_verse), 0)) as reste_a_payer
                FROM eleves e
                LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
                LEFT JOIN paiements p ON e.id = p.eleve_id
                WHERE e.telephone_parent IS NOT NULL 
                  AND e.telephone_parent != ''
                  AND e.telephone_parent REGEXP '^[+]?[0-9\\s\\-\\(\\)]{10,}$'
                GROUP BY e.id, e.nom, e.prenom, e.telephone_parent
                HAVING reste_a_payer > 0
                ORDER BY e.nom, e.prenom";
        
        $stmt = $bd->query($sql);
        $insolvables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($insolvables as $eleve) {
            try {
                $telephone = $eleve['telephone_parent'];
                $nomEleve = $eleve['nom'] . ' ' . $eleve['prenom'];
                $resteAPayer = $eleve['reste_a_payer'];
                
                // Prepare SMS message
                $message = "Bonjour, ceci est un rappel de paiement pour $nomEleve. Reste à payer: $resteAPayer FCFA. Merci de régler rapidement.";
                
                // In a real implementation, you would integrate with an SMS gateway API here
                // For this example, we'll simulate the SMS sending
                
                // Log the SMS attempt
                error_log("SMS envoyé à $telephone: $message");
                
                // Simulate success
                $sentCount++;
                
            } catch (Exception $e) {
                error_log("Échec envoi SMS pour élève ID {$eleve['id']}: " . $e->getMessage());
                $failedCount++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Opération terminée',
            'data' => [
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => count($insolvables)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
