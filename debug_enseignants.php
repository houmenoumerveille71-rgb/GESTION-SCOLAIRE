<?php
require "backends/config/connexion.php";
$result = $bd->query('DESCRIBE enseignants');
echo "<pre>";
print_r($result->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>