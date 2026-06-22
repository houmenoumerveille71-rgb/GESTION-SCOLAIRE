<?php
// 1. Démarre la session et sécurise l'accès (récupère l'utilisateur connecté)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optionnel mais recommandé : charge ton fichier de restriction si nécessaire
// require __DIR__ . "/../auth.php"; 

require "../config/connexion.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleve_id = $_POST['eleve_id'] ?? '';
    $montant = floatval($_POST['montant'] ?? 0);
    $type_paiement = $_POST['type_paiement'] ?? 'SCOLARITE';
    $date_paiement = $_POST['date_paiement'] ?? date('Y-m-d');
    
    // DYNAMIQUE : Récupère le nom exact stocké lors de la connexion dans login.php
    $nom_caissier = $_SESSION['user_nom'] ?? 'Caissier non défini';

    if (empty($eleve_id) || $montant <= 0) {
        echo json_encode(['success' => false, 'message' => 'Élève et montant valide requis']);
        exit;
    }

    // Génération du numéro de reçu unique
    $numero_recu = "REC-" . date('Ymd-His') . "-" . rand(10, 99);
    $annee_scolaire = date('Y') . '-' . (date('Y') + 1);

    try {
        $bd->beginTransaction();

        // Insertion du paiement (montant_verse existe bien selon ta structure !)
        $q = $bd->prepare("INSERT INTO paiements (eleve_id, montant_verse, date_paiement, numero_recu, annee_scolaire) VALUES (?, ?, ?, ?, ?)");
        $q->execute([$eleve_id, $montant, $date_paiement, $numero_recu, $annee_scolaire]);

        // Calcul du reste à payer mis à jour avec tes vraies colonnes (montant_scolarite et code_classe)
        $sql_reste = "SELECT 
                        (COALESCE(fs.montant_total, c.montant_scolarite) - COALESCE(SUM(p.montant_verse), 0)) AS reste
                      FROM eleves e
                      JOIN classes c ON e.code_classe = c.code_classe
                      LEFT JOIN frais_scolaires fs ON e.id = fs.eleve_id
                      LEFT JOIN paiements p ON e.id = p.eleve_id
                      WHERE e.id = ?
                      GROUP BY e.id, c.montant_scolarite, fs.montant_total";
        
        $stmt_reste = $bd->prepare($sql_reste);
        $stmt_reste->execute([$eleve_id]);
        $info_argent = $stmt_reste->fetch(PDO::FETCH_ASSOC);
        $reste_a_payer = $info_argent ? floatval($info_argent['reste']) : 0;

        $bd->commit();

        // Renvoie TOUTES les infos au Frontend, incluant le nom du caissier connecté
        echo json_encode([
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => [
                'numero_recu' => $numero_recu,
                'date_paiement' => date('d/m/Y', strtotime($date_paiement)),
                'montant_paye' => $montant,
                'reste_a_payer' => $reste_a_payer,
                'caissier' => $nom_caissier // Transmis au JavaScript pour l'affichage immédiat du reçu
            ]
        ]);
        exit;

    } catch (Exception $e) {
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}
?>
