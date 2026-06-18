<?php

include("../config/connexion.php");

header("Content-Type: application/json");

$sql = "SELECT * FROM enseignants";

$stmt = $bd->prepare($sql);
$stmt->execute();

$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($enseignants);

?>