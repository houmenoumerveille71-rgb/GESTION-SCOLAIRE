<?php
require "../config/connexion.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Récupération du terme recherché depuis l'URL (ex: ?q=houmenou)
$recherche = $_GET['q'] ?? '';

try {
    // Requête SQL complète et sécurisée
    // Elle récupère l'élève, sa classe, sa scolarité par défaut, calcule la somme de ses versements réels, et en déduit le reste à payer.
    $sql = "SELECT 
                e.id, 
                e.matricule, 
                e.nom, 
                e.prenom,
                c.nom_classe as nom_classe,
                COALESCE(fs.montant_total, c.montant_scolarite) AS total_attendu,
                COALESCE((SELECT SUM(p.montant_verse) FROM paiements p WHERE p.eleve_id = e.id), 0) AS total_paye,
                (COALESCE(fs.montant_total, c.montant_scolarite) - COALESCE((SELECT SUM(p.montant_verse) FROM paiements p WHERE p.eleve_id = e.id), 0)) AS reste_a_payer
            FROM eleves e
            LEFT JOIN classes c ON e.code_classe = c.code_classe
            LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
            WHERE e.nom LIKE ? OR e.prenom LIKE ? OR e.matricule LIKE ?
            LIMIT 5";
            
    $stmt = $bd->prepare($sql);
    $terme = "%" . $recherche . "%";
    $stmt->execute([$terme, $terme, $terme]);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Forcer le typage numérique pour éviter que le JavaScript reçoive des chaînes de caractères
    foreach ($resultats as &$row) {
        $row['id'] = (int)$row['id'];
        $row['total_attendu'] = (float)$row['total_attendu'];
        $row['total_paye'] = (float)$row['total_paye'];
        $row['reste_a_payer'] = (float)$row['reste_a_payer'];
    }

    // Envoi de la liste au format JSON attendu par caisse.html
    echo json_encode($resultats);
    exit;

} catch (Exception $e) {
    // En cas de bug ou d'erreur SQL, renvoie un tableau vide pour ne pas faire bloquer le JavaScript
    echo json_encode([]);
    exit;
}
?>
