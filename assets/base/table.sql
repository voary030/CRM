-- Active: 1746118334664@@127.0.0.1@3306@budget

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('éleveur', 'acheteur', 'partenaire') NOT NULL,
    nom VARCHAR(100) NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    siret VARCHAR(14),
    numero_eleveur VARCHAR(50), -- Numéro officiel pour les éleveurs
    date_inscription DATE NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE type_actions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE actions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    client_id INT NOT NULL,
    type_action_id INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (type_action_id) REFERENCES type_actions(id)
);

CREATE TABLE type_reactions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    besoin_validation BOOLEAN DEFAULT FALSE
);

CREATE TABLE reactions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10,2),
    type_reaction_id INT NOT NULL,
    statut ENUM('en attente', 'valide', 'rejete') DEFAULT 'en attente',
    valide_par INT,
    date_validation DATETIME,
    commentaire TEXT,
    FOREIGN KEY (action_id) REFERENCES actions(id) ON DELETE CASCADE,
    FOREIGN KEY (type_reaction_id) REFERENCES type_reactions(id),
    FOREIGN KEY (valide_par) REFERENCES user(user_id)
);


-- ajout table



CREATE TABLE animaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    espece ENUM('bovin', 'ovin', 'caprin', 'porcin', 'volaille', 'autre') NOT NULL,
    race VARCHAR(100),
    sexe ENUM('mâle', 'femelle', 'inconnu') NOT NULL,
    date_naissance DATE,
    numero_identification VARCHAR(50) UNIQUE, -- Numéro d'oreille ou autre identification
    statut ENUM('disponible', 'réservé', 'vendu', 'mort', 'abattu') DEFAULT 'disponible',
    prix DECIMAL(10,2),
    proprietaire_id INT, -- Client propriétaire actuel
    mere_id INT, -- Pour le pedigree
    pere_id INT, -- Pour le pedigree
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proprietaire_id) REFERENCES clients(id),
    FOREIGN KEY (mere_id) REFERENCES animaux(id),
    FOREIGN KEY (pere_id) REFERENCES animaux(id)
);

CREATE TABLE transactions_animaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    client_id INT NOT NULL, -- Client concerné par la transaction
    type_transaction ENUM('vente', 'achat', 'don', 'échange') NOT NULL,
    date_transaction DATE NOT NULL,
    prix DECIMAL(10,2),
    mode_paiement VARCHAR(50),
    statut_paiement ENUM('impayé', 'partiel', 'payé') DEFAULT 'impayé',
    livraison_prevue DATE,
    livraison_effectuee DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animaux(id),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    reaction_id INT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_feedback DATETIME DEFAULT CURRENT_TIMESTAMP,
    traite BOOLEAN DEFAULT FALSE, -- Si le feedback a été pris en compte
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (reaction_id) REFERENCES reactions(id)
);


CREATE TABLE stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('aliment', 'matériel', 'médicament', 'autre') NOT NULL,
    nom VARCHAR(100) NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) NOT NULL,
    seuil_alerte DECIMAL(10,2),
    prix_unitaire DECIMAL(10,2),
    fournisseur_id INT,
    date_peremption DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fournisseur_id) REFERENCES clients(id)
);

CREATE TABLE interventions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('sanitaire', 'technique', 'reproduction', 'autre') NOT NULL,
    animal_id INT,
    client_id INT, -- Pour les interventions concernant tout l'élevage
    date_intervention DATE NOT NULL,
    description TEXT NOT NULL,
    veterinaire_id INT, -- Référence à un client de type vétérinaire
    cout DECIMAL(10,2),
    statut ENUM('planifiée', 'réalisée', 'annulée') DEFAULT 'planifiée',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animaux(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (veterinaire_id) REFERENCES clients(id)
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_document ENUM('contrat', 'certificat', 'facture', 'bon de livraison', 'autre') NOT NULL,
    client_id INT,
    animal_id INT,
    transaction_id INT,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    date_emission DATE NOT NULL,
    date_expiration DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (animal_id) REFERENCES animaux(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions_animaux(id)
);

CREATE TABLE evenements_elevage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('naissance', 'sevrage', 'mise bas', 'vaccination', 'pesée', 'exposition', 'autre') NOT NULL,
    animal_id INT,
    client_id INT, -- Pour les événements concernant tout l'élevage
    date_evenement DATE NOT NULL,
    description TEXT,
    poids DECIMAL(10,2),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animaux(id),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);
