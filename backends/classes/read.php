<?php
require "../config/connexion.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Sélection des colonnes demandées par le frontend
        $sql = "SELECT id, nom_classe, montant_scolaire FROM classes ORDER BY id ASC";
        $stmt = $bd->prepare($sql);
        $stmt->execute();
        
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Envoi direct du tableau JSON requis par ton script JavaScript
        echo json_encode($classes);
        exit;

    } catch (PDOException $e) {
        echo json_encode([]);
        exit;
    }
} else {
    echo json_encode([]);
    exit;
}
?>