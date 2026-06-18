<?php
require "backends/config/connexion.php";
try {
    $result = $bd->query('DESCRIBE enseignants');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>Structure de la table enseignants:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>".$col['Field']."</td>";
        echo "<td>".$col['Type']."</td>";
        echo "<td>".$col['Null']."</td>";
        echo "<td>".$col['Key']."</td>";
        echo "<td>".$col['Default']."</td>";
        echo "<td>".$col['Extra']."</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>