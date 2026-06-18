<?php
require "backends/config/connexion.php";

try {
    $stmt = $bd->query("DESCRIBE eleves");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    // Vérifier si classe_id existe
    $hasClasseId = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'classe_id') {
            $hasClasseId = true;
            break;
        }
    }

    if ($hasClasseId) {
        echo "La colonne 'classe_id' existe.<br>";
    } else {
        echo "La colonne 'classe_id' n'existe pas.<br>";
    }

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>