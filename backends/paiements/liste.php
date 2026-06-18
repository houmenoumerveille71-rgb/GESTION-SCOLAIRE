<?php
require "../config/connexion.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    // Requête SQL alignée à 100% avec ta table 'paiements' (montant_verse)
    $sql = "SELECT 
                p.numero_recu,
                p.date_paiement,
                e.matricule,
                e.nom,
                e.prenom,
                p.montant_verse, -- Changé ici : ton vrai champ phpMyAdmin
                (COALESCE(fs.montant_total, c.montant_scolarite) - (
                    SELECT COALESCE(SUM(p2.montant_verse), 0) -- Changé ici aussi
                    FROM paiements p2 
                    WHERE p2.eleve_id = e.id
                )) AS reste_a_payer
            FROM paiements p
            JOIN eleves e ON p.eleve_id = e.id
            JOIN classes c ON e.code_classe = c.code_classe
            LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
            ORDER BY p.date_paiement DESC, p.id DESC";

    $stmt = $bd->prepare($sql);
    $stmt->execute();
    $liste_paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Forcer les types numériques pour éviter les chaînes de caractères en JS
    foreach ($liste_paiements as &$row) {
        $row['montant_verse'] = (float)$row['montant_verse'];
        $row['reste_a_payer'] = (float)$row['reste_a_payer'];
    }

    echo json_encode([
        "success" => true,
        "data" => $liste_paiements
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur : " . $e->getMessage()
    ]);
    exit;
}
?>