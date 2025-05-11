CREATE DATABASE IF NOT EXISTS plateforme_projets_militaires;
USE plateforme_projets_militaires;
CREATE TABLE unites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_unite VARCHAR(100) NOT NULL,
    role ENUM('eleve', 'encadrant', 'admin') NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    UNIQUE KEY (nom_unite, role)
);
CREATE TABLE projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre_projet VARCHAR(255) NOT NULL,
    specialite ENUM('GC', 'TEL', 'EM', 'GI') NOT NULL COMMENT 'GC=Génie Civil, TEL=Télécommunication, EM=Électromécanique, GI=Génie Informatique',
    nombre_eleves ENUM('Monome', 'Binome') NOT NULL,
    encadrant VARCHAR(100) NOT NULL,
    organisme_adresse TEXT NOT NULL,
    grade ENUM('Lieutenant', 'Capitaine', 'Commandant', 'Lieutenant-Colonel', 'Colonel', 'Colonel-major') NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    objectif TEXT NOT NULL,
    resultats_attendus TEXT NOT NULL,
    date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_unite INT,
    FOREIGN KEY (id_unite) REFERENCES unites(id)
);
-- Académie militaire (avec les 3 rôles)
INSERT INTO unites (nom_unite, role, mot_de_passe) VALUES 
('Académie militaire', 'admin', SHA2('admin123', 256)),
('Académie militaire', 'encadrant', SHA2('encadrant123', 256)),
('Académie militaire', 'eleve', SHA2('eleve123', 256));

-- Autres unités (seulement encadrant)
INSERT INTO unites (nom_unite, role, mot_de_passe) VALUES 
('Hôpital militaire principal d''instruction de Tunis', 'encadrant', SHA2('hopital123', 256)),
('D.G.T.I', 'encadrant', SHA2('dgti123', 256)),
('D.G.M.A', 'encadrant', SHA2('dgma123', 256)),
('Régiment 71 transmission', 'encadrant', SHA2('regiment123', 256)),
('D.G.M.R.E', 'encadrant', SHA2('dgmre123', 256));
INSERT INTO projets (
    titre_projet, 
    specialite, 
    nombre_eleves, 
    encadrant, 
    organisme_adresse, 
    grade, 
    telephone, 
    email, 
    description, 
    objectif, 
    resultats_attendus,
    id_unite
) VALUES (
    'Système de surveillance intelligent', 
    'GI', 
    'Binome', 
    'Capitaine Ahmed', 
    'Académie Militaire, Tunis', 
    'Capitaine', 
    '12345678', 
    'ahmed@militaire.tn', 
    'Développement d''un système de surveillance utilisant l''IA', 
    'Améliorer la sécurité des installations militaires', 
    'Prototype fonctionnel avec détection automatique d''intrus',
    2  -- ID de l'unité Académie militaire encadrant
);