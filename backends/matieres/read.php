<?php
// Charger la connexion
require "../config/connexion.php";

header("Content-Type: application/json");

$sql = "SELECT id, nom_matiere FROM matieres ORDER BY nom_matiere";

$stmt = $bd->prepare($sql);
$stmt->execute();

$matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($matieres);
?>
