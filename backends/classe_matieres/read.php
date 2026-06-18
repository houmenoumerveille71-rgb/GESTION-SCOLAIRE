<?php
// Charger la connexion
require "../config/connexion.php";

header("Content-Type: application/json");

// Requête pour récupérer les associations classe-matière avec les noms
$sql = "
    SELECT cm.id, c.nom_classe, m.nom_matiere
    FROM classe_matieres cm
    JOIN classes c ON cm.classe_id = c.id
    JOIN matieres m ON cm.matiere_id = m.id
    ORDER BY c.nom_classe, m.nom_matiere
";

$stmt = $bd->prepare($sql);
$stmt->execute();

$associations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($associations);
?>