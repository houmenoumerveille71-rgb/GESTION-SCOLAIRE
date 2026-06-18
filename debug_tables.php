<?php
require "backends/config/connexion.php";
try {
    $stmt = $bd->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables:\n";
    foreach($tables as $t) {
        echo "- $t\n";
        // describe each
        $desc = $bd->query("DESCRIBE $t")->fetchAll();
        foreach($desc as $row) {
            echo "  {$row['Field']} ({$row['Type']})";
            if ($row['Key'] == 'PRI') echo " PRI";
            if ($row['Extra'] == 'auto_increment') echo " AI";
            if ($row['Null'] == 'NO') echo " NOT NULL";
            if ($row['Default'] != '') echo " DEFAULT {$row['Default']}";
            echo "\n";
        }
        echo "\n";
    }
} catch(Exception $e) {
    echo "Error: ".$e->getMessage();
}
?>