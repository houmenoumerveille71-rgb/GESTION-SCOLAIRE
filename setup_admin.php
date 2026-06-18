<?php
require "backends/config/connexion.php";
$hash = password_hash('password', PASSWORD_DEFAULT);
$stmt = $bd->prepare("INSERT IGNORE INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
$stmt->execute(['Administrateur', 'admin@ecole.com', $hash, 'admin']);
echo "Admin user created (if not already exists).";
?>