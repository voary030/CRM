-- 1. STATISTIQUES AVANT RÉACTION
-- ==============================

-- Nombre d'actions en attente par type d'action
CREATE VIEW stats_actions_en_attente AS
SELECT 
    ta.description AS type_action,
    COUNT(a.id) AS nombre_actions,
    AVG(DATEDIFF(CURRENT_DATE(), a.created_at)) AS jours_attente_moyens
FROM actions a
JOIN type_actions ta ON a.type_action_id = ta.id
LEFT JOIN reactions r ON a.id = r.action_id
WHERE r.id IS NULL
GROUP BY ta.description
ORDER BY nombre_actions DESC;

-- Clients actifs (avec interactions récentes)
CREATE VIEW stats_clients_actifs AS
SELECT 
    c.type AS type_client,
    COUNT(DISTINCT c.id) AS nombre_clients,
    (COUNT(DISTINCT c.id) * 100 / (SELECT COUNT(*) FROM clients WHERE actif = TRUE)) AS pourcentage_total
FROM clients c
JOIN actions a ON c.id = a.client_id
WHERE a.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)
AND c.actif = TRUE
GROUP BY c.type;

-- stat global
CREATE OR REPLACE VIEW stats_etat_entreprise AS
SELECT
    -- Statistiques animaux
    (SELECT COUNT(*) FROM animaux WHERE statut = 'disponible') AS animaux_disponibles,
    (SELECT COUNT(*) FROM animaux WHERE statut = 'réservé') AS animaux_reserves,
    (SELECT COUNT(*) FROM animaux a 
     JOIN transactions_animaux ta ON a.id = ta.animal_id 
     WHERE a.statut = 'vendu' AND YEAR(ta.date_transaction) = YEAR(CURRENT_DATE())) AS animaux_vendus_annee,
    (SELECT AVG(ta.prix) FROM animaux a
     JOIN transactions_animaux ta ON a.id = ta.animal_id
     WHERE a.statut = 'vendu' AND YEAR(ta.date_transaction) = YEAR(CURRENT_DATE())) AS prix_moyen_vente,
    
    -- Statistiques stocks
    (SELECT COUNT(*) FROM stocks WHERE quantite <= seuil_alerte OR quantite <= 0) AS stocks_critiques,
    (SELECT SUM(quantite * prix_unitaire) FROM stocks) AS valeur_stock_total,
    
    -- Statistiques financières
    (SELECT SUM(montant) FROM reactions WHERE statut = 'valide' AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS chiffre_affaires_annee,
    (SELECT SUM(cout) FROM interventions WHERE YEAR(date_intervention) = YEAR(CURRENT_DATE())) AS depenses_interventions,
    (SELECT SUM(prix) FROM transactions_animaux 
     WHERE type_transaction = 'achat' AND YEAR(date_transaction) = YEAR(CURRENT_DATE())) AS depenses_acquisitions;
-- 2. STATISTIQUES PENDANT RÉACTION
-- ================================

-- Actions en cours de traitement par responsable
CREATE VIEW stats_actions_en_cours AS
SELECT 
    u.name AS responsable,
    d.name AS departement,
    COUNT(r.id) AS nombre_actions,
    AVG(DATEDIFF(CURRENT_DATE(), a.created_at)) AS delai_moyen_jours,
    MAX(DATEDIFF(CURRENT_DATE(), a.created_at)) AS delai_max_jours
FROM reactions r
JOIN actions a ON r.action_id = a.id
JOIN user u ON r.valide_par = u.user_id
JOIN department d ON u.department_id = d.department_id
WHERE r.statut = 'en attente'
GROUP BY u.name, d.name
ORDER BY nombre_actions DESC;

-- Temps de réaction moyen par type d'action
CREATE VIEW stats_temps_reaction AS
SELECT 
    ta.description AS type_action,
    COUNT(r.id) AS nombre_reactions,
    AVG(DATEDIFF(r.created_at, a.created_at)) AS temps_moyen_jours,
    MIN(DATEDIFF(r.created_at, a.created_at)) AS temps_min_jours,
    MAX(DATEDIFF(r.created_at, a.created_at)) AS temps_max_jours
FROM reactions r
JOIN actions a ON r.action_id = a.id
JOIN type_actions ta ON a.type_action_id = ta.id
WHERE r.statut = 'valide'
GROUP BY ta.description
ORDER BY nombre_reactions DESC;

-- Charge de travail par département
CREATE VIEW stats_charge_travail AS
SELECT 
    d.name AS departement,
    COUNT(r.id) AS actions_en_cours,
    (COUNT(r.id) * 100 / (SELECT COUNT(*) FROM reactions WHERE statut = 'en attente')) AS pourcentage_total
FROM reactions r
JOIN user u ON r.valide_par = u.user_id
JOIN department d ON u.department_id = d.department_id
WHERE r.statut = 'en attente'
GROUP BY d.name
ORDER BY actions_en_cours DESC;

-- 3. STATISTIQUES APRÈS RÉACTION
-- ==============================

-- Taux de satisfaction client global et par type d'action
CREATE VIEW stats_satisfaction AS
SELECT 
    ta.description AS type_action,
    COUNT(f.id) AS nombre_feedbacks,
    AVG(f.note) AS note_moyenne,
    (COUNT(CASE WHEN f.note >= 4 THEN 1 END) * 100 / COUNT(f.id)) AS pourcentage_satisfaits
FROM feedbacks f
JOIN reactions r ON f.reaction_id = r.id
JOIN actions a ON r.action_id = a.id
JOIN type_actions ta ON a.type_action_id = ta.id
GROUP BY ta.description
UNION
SELECT 
    'GLOBAL' AS type_action,
    COUNT(f.id) AS nombre_feedbacks,
    AVG(f.note) AS note_moyenne,
    (COUNT(CASE WHEN f.note >= 4 THEN 1 END) * 100 / COUNT(f.id)) AS pourcentage_satisfaits
FROM feedbacks f;

-- Taux de résolution des problèmes
CREATE VIEW stats_resolution_problemes AS
SELECT 
    ta.description AS type_probleme,
    COUNT(a.id) AS nombre_problemes,
    COUNT(r.id) AS nombre_resolus,
    (COUNT(r.id) * 100 / COUNT(a.id)) AS taux_resolution,
    AVG(DATEDIFF(r.created_at, a.created_at)) AS delai_moyen_resolution
FROM actions a
JOIN type_actions ta ON a.type_action_id = ta.id
LEFT JOIN reactions r ON a.id = r.action_id AND r.statut = 'valide'
WHERE ta.description LIKE '%plainte%' 
   OR ta.description LIKE '%probleme%'
   OR ta.description LIKE '%réclamation%'
GROUP BY ta.description;

-- 4. STATISTIQUES SPÉCIFIQUES À L'ÉLEVAGE
-- ========================================

-- Performance des animaux (taux de vente, prix moyen)
CREATE VIEW stats_performance_animaux AS
SELECT 
    espece,
    race,
    COUNT(id) AS nombre_animaux,
    SUM(CASE WHEN statut = 'vendu' THEN 1 ELSE 0 END) AS nombre_vendus,
    (SUM(CASE WHEN statut = 'vendu' THEN 1 ELSE 0 END) * 100 / COUNT(id)) AS taux_vente,
    AVG(CASE WHEN statut = 'vendu' THEN prix ELSE NULL END) AS prix_moyen_vente,
    MAX(CASE WHEN statut = 'vendu' THEN prix ELSE NULL END) AS prix_max,
    MIN(CASE WHEN statut = 'vendu' THEN prix ELSE NULL END) AS prix_min
FROM animaux
GROUP BY espece, race
ORDER BY espece, taux_vente DESC;

-- Suivi des interventions sanitaires
CREATE VIEW stats_interventions_sanitaires AS
SELECT 
    i.type,
    COUNT(i.id) AS nombre_interventions,
    AVG(i.cout) AS cout_moyen,
    (SELECT COUNT(DISTINCT animal_id) FROM interventions WHERE type = i.type) AS animaux_concernes,
    (SELECT COUNT(DISTINCT client_id) FROM interventions WHERE type = i.type) AS elevages_concernes
FROM interventions i
GROUP BY i.type
ORDER BY nombre_interventions DESC;

-- Rentabilité par client (éleveur/acheteur)
CREATE VIEW stats_rentabilite_clients AS
SELECT 
    c.id,
    c.nom,
    c.type AS type_client,
    COUNT(DISTINCT a.id) AS nombre_interactions,
    COUNT(DISTINCT ta.id) AS nombre_transactions,
    SUM(CASE WHEN r.montant IS NOT NULL THEN r.montant ELSE 0 END) AS chiffre_affaires,
    (SELECT COUNT(DISTINCT animal_id) FROM transactions_animaux WHERE client_id = c.id) AS nombre_animaux_echanges,
    (SELECT AVG(note) FROM feedbacks WHERE client_id = c.id) AS satisfaction_moyenne
FROM clients c
LEFT JOIN actions a ON c.id = a.client_id
LEFT JOIN reactions r ON a.id = r.action_id AND r.statut = 'valide'
LEFT JOIN transactions_animaux ta ON c.id = ta.client_id
GROUP BY c.id, c.nom, c.type
ORDER BY chiffre_affaires DESC;

-- 5. FONCTIONS UTILITAIRES POUR LE SUIVI
-- =====================================

-- Fonction pour calculer le taux de croissance
DELIMITER //
CREATE FUNCTION calcul_taux_croissance(valeur_actuelle DECIMAL(10,2), valeur_precedente DECIMAL(10,2))
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    IF valeur_precedente = 0 THEN
        RETURN 0;
    ELSE
        RETURN ((valeur_actuelle - valeur_precedente) / valeur_precedente) * 100;
    END IF;
END//
DELIMITER ;

-- Fonction pour obtenir le statut d'un client
DELIMITER //
CREATE FUNCTION get_statut_client(client_id INT)
RETURNS VARCHAR(20)
READS SQL DATA
BEGIN
    DECLARE last_interaction DATE;
    DECLARE nb_actions INT;
    
    SELECT MAX(created_at) INTO last_interaction FROM actions WHERE client_id = client_id;
    SELECT COUNT(*) INTO nb_actions FROM actions WHERE client_id = client_id AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH);
    
    IF nb_actions = 0 THEN
        RETURN 'Inactif';
    ELSEIF nb_actions > 10 THEN
        RETURN 'Très actif';
    ELSEIF nb_actions > 5 THEN
        RETURN 'Actif';
    ELSE
        RETURN 'Peu actif';
    END IF;
END//
DELIMITER ;




-- Tableau de bord synthétique
SELECT 
    (SELECT COUNT(*) FROM stats_actions_en_attente) AS total_actions_attente,
    (SELECT nombre_clients FROM stats_clients_actifs WHERE type_client = 'éleveur') AS eleveurs_actifs,
    (SELECT animaux_disponibles FROM stats_etat_entreprise) AS animaux_disponibles,
    (SELECT chiffre_affaires_annee FROM stats_etat_entreprise) AS ca_annuel,
    (SELECT note_moyenne FROM stats_satisfaction WHERE type_action = 'GLOBAL') AS satisfaction_moyenne,
    (SELECT AVG(taux_resolution) FROM stats_resolution_problemes) AS taux_resolution_moyen;

-- Détails par département
SELECT 
    d.name AS departement,
    (SELECT COUNT(*) FROM stats_actions_en_cours WHERE departement = d.name) AS actions_en_cours,
    (SELECT AVG(delai_moyen_jours) FROM stats_actions_en_cours WHERE departement = d.name) AS delai_moyen,
    (SELECT COUNT(*) FROM reactions r JOIN user u ON r.valide_par = u.user_id 
     WHERE u.department_id = d.department_id AND r.statut = 'valide' 
     AND YEAR(r.created_at) = YEAR(CURRENT_DATE())) AS actions_traitees
FROM department d
WHERE d.department_is_deleted = 0;