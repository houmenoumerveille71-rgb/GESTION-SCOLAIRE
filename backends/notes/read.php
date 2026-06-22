<?php
// Charger la connexion
require "../config/connexion.php";

header("Content-Type: application/json");

// Requête pour récupérer les notes avec les noms de l'élève, de la matière et de la classe
$sql = "
    SELECT n.id, n.note, n.semestre, n.annee_scolaire,
           e.nom AS eleve_nom, e.prenom AS eleve_prenom, e.matricule,
           m.nom_matiere,
           c.nom_classe
    FROM notes n
    JOIN eleves e ON n.eleve_id = e.id
    JOIN matieres m ON n.matiere_id = m.id
    LEFT JOIN classes c ON e.classe_id = c.id
    ORDER BY e.nom, e.prenom, m.nom_matiere, n.semestre
";

$stmt = $bd->prepare($sql);
$stmt->execute();

$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notes);
?>
