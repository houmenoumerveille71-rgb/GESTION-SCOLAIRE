<?php
require "../config/connexion.php";
require "../config/auth.php";
header("Content-Type: application/json");

// Check if user is logged in
if (!estConnecte()) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

// Optional: Check if user is admin or has appropriate permissions
// if (!estAdmin()) {
//     echo json_encode(['success' => false, 'message' => 'Privilèges administrateur requis']);
//     exit;
// }

try {
    // Query to get students with remaining balance > 0
    // reste_a_payer = frais_scolaires.montant_total - SUM(paiements.montant_verse)
    $sql = "SELECT 
                e.id,
                e.matricule,
                e.nom,
                e.prenom,
                e.sexe,
                e.date_naissance,
                e.nom_parent,
                e.telephone_parent,
                e.adresse,
                e.classe_id,
                c.nom_classe,
                c.niveau,
                COALESCE(fs.montant_total, 0) as total_du,
                COALESCE(SUM(p.montant_verse), 0) as total_paye,
                (COALESCE(fs.montant_total, 0) - COALESCE(SUM(p.montant_verse), 0)) as reste_a_payer
            FROM eleves e
            LEFT JOIN classes c ON e.classe_id = c.id
            LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
            LEFT JOIN paiements p ON e.id = p.eleve_id
            GROUP BY e.id, e.matricule, e.nom, e.prenom, e.sexe, e.date_naissance, 
                     e.nom_parent, e.telephone_parent, e.adresse, e.classe_id,
                     c.nom_classe, c.niveau, fs.montant_total
            HAVING reste_a_payer > 0
            ORDER BY e.nom, e.prenom";
    
    $stmt = $bd->query($sql);
    $insolvables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $insolvables]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
