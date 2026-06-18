<?php
require "C:\wamp64\www\projet_scolaire\backends\config\connexion.php";
$result = $bd->query('DESCRIBE enseignants');
print_r($result->fetchAll(PDO::FETCH_ASSOC));
?>