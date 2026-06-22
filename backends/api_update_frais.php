<?php
header("Content-Type: application/json; charset=utf-8");
require "../config/connexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $montant = $_POST['montant'] ?? $_POST['frais_scolaire_defaut'] ?? '';

    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }

    if (!is_numeric($montant) || $montant < 0) {
        echo json_encode(['success' => false, 'message' => 'Montant invalide']);
        exit;
    }

    try {
        $q = $bd->prepare("UPDATE classes SET frais_scolaire_defaut = ? WHERE id = ?");
        $q->execute([$montant, $id]);

        if ($q->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Frais mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Classe non trouvée']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode POST requise']);
}
?>
