<?php
require "backends/config/connexion.php";

echo "<h1>Database Structure</h1>";

// Get all tables
$result = $bd->query("SHOW TABLES");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);

echo "<h2>Tables:</h2><ul>";
foreach($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";

// Check each table structure
foreach($tables as $table) {
    echo "<h2>Structure of $table:</h2>";
    try {
        $cols = $bd->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($cols);
        echo "</pre>";
    } catch(Exception $e) {
        echo "Error describing $table: " . $e->getMessage();
    }
}
?>