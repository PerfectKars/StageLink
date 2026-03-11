CREATE DATABASE IF NOT EXISTS site_stages;
USE site_stages;

-- 1. Table Entreprise
CREATE TABLE ENTREPRISE (
    id_entreprise INT AUTO_INCREMENT PRIMARY KEY,
    raison_sociale VARCHAR(100) NOT NULL,
    siret VARCHAR(14) UNIQUE,
    secteur_activite VARCHAR(50),
    description TEXT,
    adresse_siege VARCHAR(255),
    site_web VARCHAR(150),
    logo_url VARCHAR(255)
);

-- 2. Table Type de Contrat (Stage, Alternance...)
CREATE TABLE TYPE_CONTRAT (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- 3. Table Étudiant
CREATE TABLE ETUDIANT (
    id_etudiant INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(15),
    niveau_etudes VARCHAR(50),
    cv_url VARCHAR(255)
);

-- 4. Table Offre (Liée à l'entreprise et au type de contrat)
CREATE TABLE OFFRE (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description_mission TEXT NOT NULL,
    date_debut DATE,
    duree_mois INT,
    gratification DECIMAL(10,2),
    ville_stage VARCHAR(100),
    teletravail BOOLEAN DEFAULT FALSE,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_entreprise INT NOT NULL,
    id_type INT NOT NULL,
    FOREIGN KEY (id_entreprise) REFERENCES ENTREPRISE(id_entreprise) ON DELETE CASCADE,
    FOREIGN KEY (id_type) REFERENCES TYPE_CONTRAT(id_type)
);

-- 5. Table de liaison Postuler (Candidatures)
CREATE TABLE POSTULER (
    id_etudiant INT,
    id_offre INT,
    date_candidature DATETIME DEFAULT CURRENT_TIMESTAMP,
    lettre_motivation TEXT,
    statut ENUM('En attente', 'Entretien', 'Refusé', 'Accepté') DEFAULT 'En attente',
    PRIMARY KEY (id_etudiant, id_offre),
    FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_etudiant) ON DELETE CASCADE,
    FOREIGN KEY (id_offre) REFERENCES OFFRE(id_offre) ON DELETE CASCADE
);

-- 6. Table Compétences et Liaison avec les offres
CREATE TABLE COMPETENCE (
    id_competence INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE REQUERIR (
    id_offre INT,
    id_competence INT,
    PRIMARY KEY (id_offre, id_competence),
    FOREIGN KEY (id_offre) REFERENCES OFFRE(id_offre) ON DELETE CASCADE,
    FOREIGN KEY (id_competence) REFERENCES COMPETENCE(id_competence) ON DELETE CASCADE
);
