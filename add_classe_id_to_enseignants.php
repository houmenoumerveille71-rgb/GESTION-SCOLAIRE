<?php
require "backends/config/connexion.php";
try {
    $result = $bd->query("SHOW COLUMNS FROM enseignants LIKE 'classe_id'");
    $exists = $result->rowCount() > 0;
    echo "Classe_id column exists: " . ($exists ? "Yes" : "No") . "\n";
    
    if (!$exists) {
        echo "Adding classe_id column to enseignants table...\n";
        $stmt = $bd->prepare("ALTER TABLE enseignants ADD COLUMN classe_id INT NULL AFTER email");
        $stmt->execute();
        echo "Added classe_id column.\n";
        
        echo "Adding foreign key constraint...\n";
        $stmt = $bd->prepare("ALTER TABLE enseignants ADD CONSTRAINT fk_enseignants_classe FOREIGN KEY (classe_id) REFERENCES classes(id)");
        $stmt->execute();
        echo "Added foreign key constraint.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>