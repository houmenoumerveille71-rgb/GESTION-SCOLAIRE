<?php
require __DIR__ . "/backends/config/connexion.php";

echo "<pre>=== Installation des utilisateurs par défaut ===\n\n";

try {
    $utilisateurs = [
        ['Administrateur', 'admin@ecole.com', 'admin123', 'admin'],
        ['Comptable', 'comptable@ecole.com', 'comptable123', 'comptable'],
        ['Caissier', 'caissier@ecole.com', 'caissier123', 'caisier']
    ];

    foreach ($utilisateurs as $u) {
        $stmt = $bd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$u[1]]);
        
        if ($stmt->fetch()) {
            echo "Existe déjà: {$u[1]}\n";
        } else {
            $hash = password_hash($u[2], PASSWORD_DEFAULT);
            $stmt = $bd->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, actif) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$u[0], $u[1], $hash, $u[3]]);
            echo "Créé: {$u[1]} / {$u[2]}\n";
        }
    }
    
    echo "\n=== Terminé! ===\n</pre>";
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
?>