<?php
require "../config/connexion.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT code_classe as id, nom_classe, montant_scolarite as montant_scolaire FROM classes ORDER BY code_classe ASC";
        $stmt = $bd->prepare($sql);
        $stmt->execute();
        
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
