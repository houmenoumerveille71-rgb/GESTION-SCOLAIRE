<?php
require "backends/config/connexion.php";

try {
    $result = $bd->query("SHOW TABLES");
    echo "Tables in the database:<br>";
    while($row = $result->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "<br>";
    }
    
    // Check structure of existing tables
    $tables = ['eleves', 'matieres', 'notes', 'enseignants'];
    foreach($tables as $table) {
        if(in_array($table, $result->fetchAll(PDO::FETCH_COLUMN))) {
            echo "<br><br>Structure of table {$table}:<br>";
            $cols = $bd->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($cols);
            echo "</pre>";
        }
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>