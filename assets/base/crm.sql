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