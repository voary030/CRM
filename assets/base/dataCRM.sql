INSERT INTO clients (type, nom, adresse, telephone, email, siret, numero_eleveur, date_inscription, actif) VALUES 
('éleveur', 'Ferme de la Vallée', '123 Route des Champs, 79000', '0559123456', 'vallee@ferme.fr', '12345678901234', 'ELV12345', '2020-01-15', TRUE),
('éleveur', 'Élevage des Collines', '456 Chemin des Bois, 79000', '0559234567', 'collines@elevage.fr', '23456789012345', 'ELV23456', '2021-03-22', TRUE),
('acheteur', 'Boucherie Dubois', '789 Avenue Centrale, 79000', '0559345678', 'contact@boucherie-dubois.fr', '34567890123456', NULL, '2022-05-10', TRUE),
('partenaire', 'Coopérative Agricole', '101 Rue des Fermiers, 79000', '0559456789', 'contact@coop-agri.fr', '45678901234567', NULL, '2021-11-30', TRUE),
('acheteur', 'Restaurant Le Gourmet', '202 Place du Marché, 79000', '0559567890', 'reservation@gourmet.fr', NULL, NULL, '2023-02-18', TRUE);


INSERT INTO type_actions (description) VALUES 
('Demande de renseignement'),
('Réservation d''animal'),
('Plainte ou réclamation'),
('Commande de produits'),
('Demande d''intervention sanitaire'),
('Demande de visite d''élevage'),
('Demande de contrat'),
('Feedback client');

INSERT INTO type_reactions (description, besoin_validation) VALUES 
('Réponse par email', FALSE),
('Prise de contact téléphonique', FALSE),
('Envoi de devis', TRUE),
('Livraison d''animal', TRUE),
('Livraison de produits', TRUE),
('Intervention programmée', TRUE),
('Remise commerciale', TRUE),
('Remplacement produit', TRUE),
('Remboursement', TRUE);


INSERT INTO animaux (espece, race, sexe, date_naissance, numero_identification, statut, prix, proprietaire_id) VALUES 
('bovin', 'Charolaise', 'femelle', '2021-04-15', 'FR123456789012', 'disponible', 2500.00, 1),
('bovin', 'Limousine', 'mâle', '2022-02-20', 'FR234567890123', 'disponible', 1800.00, 1),
('bovin', 'Blonde d''Aquitaine', 'femelle', '2020-11-10', 'FR345678901234', 'réservé', 3000.00, 2),
('ovin', 'Ile-de-France', 'femelle', '2023-01-05', 'FR456789012345', 'disponible', 450.00, 2),
('caprin', 'Alpine', 'femelle', '2022-09-12', 'FR567890123456', 'vendu', 320.00, 1),
('porcin', 'Large White', 'mâle', '2023-03-18', 'FR678901234567', 'disponible', 280.00, 1);


INSERT INTO actions (description, client_id, type_action_id, created_at) VALUES 
('Demande info sur la race Charolaise', 3, 1, '2023-06-01 09:15:00'),
('Réservation vache Charolaise n°FR123456789012', 3, 2, '2023-06-05 14:30:00'),
('Problème avec livraison précédente', 3, 3, '2023-06-10 11:20:00'),
('Commande 10 agneaux pour Noël', 4, 4, '2023-06-15 16:45:00'),
('Vache malade, besoin intervention', 1, 5, '2023-06-18 08:10:00'),
('Visite pour sélection génétique', 5, 6, '2023-06-20 10:30:00'),
('Demande contrat annuel', 4, 7, '2023-06-22 13:25:00'),
('Feedback positif sur dernier achat', 3, 8, '2023-06-25 17:50:00');


INSERT INTO reactions (action_id, montant, type_reaction_id, statut, valide_par, date_validation, commentaire, created_at) VALUES 
(1, NULL, 1, 'valide', 1, '2023-06-01 11:30:00', 'Info envoyée par email', '2023-06-01 10:45:00'),
(2, 2500.00, 3, 'valide', 1, '2023-06-06 09:15:00', 'Devis accepté par client', '2023-06-05 16:20:00'),
(2, 2500.00, 4, 'en attente', NULL, NULL, 'Livraison prévue le 20/06', '2023-06-06 10:00:00'),
(3, 150.00, 8, 'valide', 5, '2023-06-11 14:30:00', 'Remboursement partiel accordé', '2023-06-10 15:45:00'),
(4, 4500.00, 3, 'valide', 1, '2023-06-16 10:20:00', 'Devis signé', '2023-06-15 17:30:00'),
(5, 120.00, 6, 'valide', 3, '2023-06-18 12:15:00', 'Vétérinaire prévenu', '2023-06-18 09:30:00'),
(6, NULL, 2, 'valide', 2, '2023-06-20 15:00:00', 'RDV fixé au 25/06', '2023-06-20 11:45:00'),
(7, NULL, 1, 'en attente', NULL, NULL, 'Contrat en préparation', '2023-06-22 14:30:00');


INSERT INTO feedbacks (client_id, reaction_id, note, commentaire) VALUES 
(3, 1, 4, 'Réponse rapide mais manque de détails'),
(3, 3, 5, 'Très satisfait du processus de réservation'),
(3, 4, 3, 'Problème résolu mais délai un peu long'),
(5, 6, 4, 'Intervention professionnelle et efficace');


INSERT INTO transactions_animaux (animal_id, client_id, type_transaction, date_transaction, prix, mode_paiement, statut_paiement) VALUES 
(5, 3, 'vente', '2023-05-15', 320.00, 'virement', 'payé'),
(3, 4, 'vente', '2023-06-10', 3000.00, 'chèque', 'impayé'),
(1, 3, 'vente', '2023-06-20', 2500.00, 'virement', 'partiel');

INSERT INTO stocks (type, nom, quantite, unite, seuil_alerte, prix_unitaire, fournisseur_id) VALUES 
('aliment', 'Granulés bovins croissance', 500, 'kg', 100, 0.85, 5),
('aliment', 'Foin de prairie', 2000, 'kg', 500, 0.30, 5),
('médicament', 'Antibiotique bovin', 50, 'doses', 10, 12.50, 4),
('matériel', 'Bottes de paille', 300, 'unités', 50, 5.00, 5);


INSERT INTO interventions (type, animal_id, client_id, date_intervention, description, veterinaire_id, cout, statut) VALUES 
('sanitaire', 1, 1, '2023-06-19', 'Vaccination annuelle', 4, 45.00, 'réalisée'),
('sanitaire', NULL, 2, '2023-06-25', 'Contrôle sanitaire troupeau', 4, 120.00, 'planifiée'),
('reproduction', 2, 1, '2023-05-10', 'Insémination artificielle', 4, 80.00, 'réalisée');


INSERT INTO documents (type_document, client_id, animal_id, transaction_id, nom_fichier, chemin, date_emission) VALUES 
('certificat', 3, 5, 1, 'certificat_sante_567890.pdf', '/docs/certificats/567890.pdf', '2023-05-14'),
('facture', 3, NULL, 1, 'facture_2023_001.pdf', '/docs/factures/2023_001.pdf', '2023-05-16'),
('contrat', 4, NULL, NULL, 'contrat_annuel_2023.pdf', '/docs/contrats/annuel_2023.pdf', '2023-01-10');

INSERT INTO evenements_elevage (type, animal_id, client_id, date_evenement, description, poids) VALUES 
('naissance', 1, 1, '2021-04-15', 'Naissance normale', 42.5),
('vaccination', 1, 1, '2021-06-20', 'Premier vaccin', NULL),
('pesée', 1, 1, '2022-01-15', 'Contrôle croissance', 285.0),
('mise bas', 3, 2, '2021-12-05', 'Première mise bas', NULL);