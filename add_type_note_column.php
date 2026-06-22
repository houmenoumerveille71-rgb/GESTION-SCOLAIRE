<?php
// Script pour ajouter la colonne type_note à la table notes
require "backends/config/connexion.php";

try {
    // Vérifier si la colonne existe déjà
    $result = $bd->query("DESCRIBE notes");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('type_note', $columns)) {
        echo "La colonne type_note existe déjà dans la table notes.<br>";
    } else {
        // Ajouter la colonne type_note
        $sql = "ALTER TABLE notes ADD COLUMN type_note VARCHAR(20) DEFAULT 'CC' AFTER note";
        $bd->exec($sql);
        echo "Colonne type_note ajoutée avec succès à la table notes.<br>";
    }
    
    // Mettre à jour toutes les notes existantes pour qu'elles aient le type 'CC' par défaut
    $sql = "UPDATE notes SET type_note = 'CC' WHERE type_note IS NULL";
    $result = $bd->exec($sql);
    echo "Mise à jour effectuée: {$result} lignes modifiées.<br>";
    
    echo "<br>Structure de la table notes après modification:<br>";
    $cols = $bd->query("DESCRIBE notes")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($cols);
    echo "</pre>";
    
} catch(Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "<br>";
}
?>