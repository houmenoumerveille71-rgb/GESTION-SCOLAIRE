-- Script d'initialisation de la table classes
-- À exécuter une seule fois pour peupler la table avec les niveaux standards

INSERT INTO classes (nom_classe, niveau) VALUES
('Maternelle Petite Section', 'Maternelle'),
('Maternelle Moyenne Section', 'Maternelle'),
('Maternelle Grande Section', 'Maternelle'),
('CP', 'Élémentaire'),
('CE1', 'Élémentaire'),
('CE2', 'Élémentaire'),
('CM1', 'Élémentaire'),
('CM2', 'Élémentaire')
ON DUPLICATE KEY UPDATE nom_classe=VALUES(nom_classe), niveau=VALUES(niveau);