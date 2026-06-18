<?php
/**
 * Test script pour vérifier le bon fonctionnement du système de gestion des frais scolaires
 * Ce script vérifie:
 * 1. La connexion à la base de données
 * 2. La structure des tables
 * 3. L'authentification
 * 4. La création d'élève avec génération automatique des frais
 * 5. La mise à jour des frais par classe
 */

require "backends/config/connexion.php";

// Test de connexion
echo "=== Test de connexion à la base de données ===\n";
try {
    $stmt = $bd->query("SELECT 1");
    $result = $stmt->fetchColumn();
    if ($result == 1) {
        echo "✓ Connexion réussie\n";
    } else {
        echo "✗ Erreur de connexion\n";
        exit;
    }
} catch (Exception $e) {
    echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
    exit;
}

// Vérifier les tables
echo "\n=== Vérification des tables ===\n";
$tables = ['classes', 'eleves', 'frais_scolaires', 'utilisateurs'];
foreach ($tables as $table) {
    try {
        $stmt = $bd->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetchColumn();
        if ($exists) {
            echo "✓ Table '$table' existe\n";
        } else {
            echo "✗ Table '$table' manquante\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur lors de la vérification de la table '$table': " . $e->getMessage() . "\n";
    }
}

// Vérifier les colonnes de la table classes
echo "\n=== Vérification de la structure de la table 'classes' ===\n";
try {
    $cols = $bd->query("DESCRIBE classes")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    $requiredCols = ['id', 'nom_classe', 'niveau', 'frais_scolaire_defaut'];
    foreach ($requiredCols as $col) {
        if (in_array($col, $colNames)) {
            echo "✓ Colonne '$col' présente\n";
        } else {
            echo "✗ Colonne '$col' manquante\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Erreur lors de la vérification de la structure: " . $e->getMessage() . "\n";
}

// Vérifier les données dans les classes
echo "\n=== Données dans la table 'classes' ===\n";
try {
    $stmt = $bd->query("SELECT id, nom_classe, niveau, frais_scolaire_defaut FROM classes ORDER BY nom_classe");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($classes) > 0) {
        echo "✓ " . count($classes) . " classe(s) trouvée(s):\n";
        foreach ($classes as $classe) {
            echo "  - ID: {$classe['id']}, Classe: {$classe['nom_classe']}, Niveau: {$classe['niveau']}, Frais: {$classe['frais_scolaire_defaut']} €\n";
        }
    } else {
        echo "✗ Aucune classe trouvée\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors de la lecture des classes: " . $e->getMessage() . "\n";
}

// Test de création d'utilisateur admin (si pas déjà existant)
echo "\n=== Vérification de l'utilisateur admin ===\n";
try {
    $stmt = $bd->query("SELECT id, nom, email, role FROM utilisateurs WHERE email = 'admin@ecole.com'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "✓ Utilisateur admin trouvé: {$admin['nom']} ({$admin['email']}) - Role: {$admin['role']}\n";
    } else {
        echo "! Utilisateur admin non trouvé, création...\n";
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $bd->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['Administrateur', 'admin@ecole.com', $hash, 'admin']);
        if ($result) {
            echo "✓ Utilisateur admin créé avec succès\n";
        } else {
            echo "✗ Échec de la création de l'utilisateur admin\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Erreur lors de la vérification de l'utilisateur admin: " . $e->getMessage() . "\n";
}

// Test de création d'un élève de test
echo "\n=== Test de création d'élève ===\n";
try {
    // Récupérer une classe pour le test
    $stmt = $bd->query("SELECT id, nom_classe, frais_scolaire_defaut FROM classes LIMIT 1");
    $testClass = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testClass) {
        $matricule = 'TEST' . time();
        $nom = 'Dupont';
        $prenom = 'Jean';
        $sexe = 'M';
        $date_naissance = '2010-01-01';
        $nom_parent = 'Dupont Pierre';
        $telephone_parent = '0123456789';
        $adresse = '123 Rue de Test, 75000 Paris';
        $classe_id = $testClass['id'];
        
        // Démarrer transaction
        $bd->beginTransaction();
        
        // Insérer l'élève
        $stmt = $bd->prepare("INSERT INTO eleves (matricule, nom, prenom, sexe, date_naissance, nom_parent, telephone_parent, adresse, classe_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$matricule, $nom, $prenom, $sexe, $date_naissance, $nom_parent, $telephone_parent, $adresse, $classe_id]);
        $eleveId = $bd->lastInsertId();
        
        // Vérifier que les frais ont été créés
        $stmt = $bd->prepare("SELECT montant_total FROM frais_scolaires WHERE eleve_id = ?");
        $stmt->execute([$eleveId]);
        $frais = $stmt->fetchColumn();
        
        if ($frais !== false && $frais == $testClass['frais_scolaire_defaut']) {
            echo "✓ Élève créé avec succès (ID: $eleveId)\n";
            echo "✓ Frais scolaires générés automatiquement: $frais € (correspond aux frais de la classe)\n";
        } else {
            echo "✗ Erreur lors de la génération des frais scolaires\n";
        }
        
        // Nettoyer: supprimer l'élève de test
        $bd->prepare("DELETE FROM frais_scolaires WHERE eleve_id = ?")->execute([$eleveId]);
        $bd->prepare("DELETE FROM eleves WHERE id = ?")->execute([$eleveId]);
        $bd->commit();
        
        echo "✓ Nettoyage de l'élève de test effectué\n";
    } else {
        echo "✗ Aucune classe trouvée pour le test\n";
    }
} catch (Exception $e) {
    $bd->rollBack();
    echo "✗ Erreur lors du test de création d'élève: " . $e->getMessage() . "\n";
}

// Test de mise à jour des frais par classe
echo "\n=== Test de mise à jour des frais par classe ===\n";
try {
    // Récupérer une classe pour le test
    $stmt = $bd->query("SELECT id, nom_classe, frais_scolaire_defaut FROM classes LIMIT 1");
    $testClass = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testClass) {
        $nouveauMontant = $testClass['frais_scolaire_defaut'] + 50.00; // Augmenter de 50€
        
        // Mettre à jour les frais
        $stmt = $bd->prepare("UPDATE classes SET frais_scolaire_defaut = ? WHERE id = ?");
        $result = $stmt->execute([$nouveauMontant, $testClass['id']]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo "✓ Frais de la classe '{$testClass['nom_classe']}' mis à jour avec succès\n";
            
            // Vérifier la mise à jour
            $stmt = $bd->query("SELECT frais_scolaire_defaut FROM classes WHERE id = {$testClass['id']}");
            $montantVerif = $stmt->fetchColumn();
            if ($montantVerif == $nouveauMontant) {
                echo "✓ Vérification: nouveau montant = $montantVerif €\n";
            } else {
                echo "✗ Erreur lors de la vérification du montant mis à jour\n";
            }
            
            // Remettre à la valeur d'origine pour ne pas perturber les autres tests
            $stmt = $bd->prepare("UPDATE classes SET frais_scolaire_defaut = ? WHERE id = ?");
            $stmt->execute([$testClass['frais_scolaire_defaut'], $testClass['id']]);
            echo "✓ Valeur remise à l'état initial pour les tests suivants\n";
        } else {
            echo "✗ Échec de la mise à jour des frais\n";
        }
    } else {
        echo "✗ Aucune classe trouvée pour le test\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test de mise à jour des frais: " . $e->getMessage() . "\n";
}

echo "\n=== Tests terminés ===\n";
?>