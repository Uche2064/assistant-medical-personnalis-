# NOUVELLE ARCHITECTURE BASE DE DONNÉES - SUNU SANTÉ

## 📋 **PRINCIPE DE L'ARCHITECTURE**

### **Règles métier clarifiées :**
1. **User** = Compte d'authentification uniquement
2. **Client** = Prospect (physique ou moral) - pas encore assuré
3. **Entreprise** = Client moral avec employés
4. **Assure** = Personne assurée (principal ou bénéficiaire)
5. **Personnel** = Employés SUNU Santé
6. **Prestataire** = Centres de soins, pharmacies, etc.
7. **Bénéficiaires** = Pas de compte User, juste Assure (est_principal = false)

## 🗄️ **STRUCTURE COMPLÈTE DES TABLES**

### **1. AUTHENTIFICATION & UTILISATEURS**

```sql
-- Table des utilisateurs (authentification uniquement)
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    contact VARCHAR(255) UNIQUE NULL,
    password VARCHAR(255) NULL,
    adresse TEXT NULL,
    photo_url VARCHAR(255) NULL,
    est_actif BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    mot_de_passe_a_changer BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Table des rôles (Spatie Permission)
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Table des permissions (Spatie Permission)
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tables pivot Spatie Permission
CREATE TABLE model_has_roles (...);
CREATE TABLE model_has_permissions (...);
CREATE TABLE role_has_permissions (...);
```

### **2. CLIENTS & PROSPECTS**

```sql
-- Table des clients (prospects - pas encore assurés)
CREATE TABLE clients (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    commercial_id BIGINT UNSIGNED NULL,
    type_client ENUM('physique', 'moral') NOT NULL,
    profession VARCHAR(255) NULL,
    code_parrainage VARCHAR(255) NULL,
    statut ENUM('prospect', 'client', 'assure') DEFAULT 'prospect',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (commercial_id) REFERENCES personnels(id) ON DELETE SET NULL
);

-- Table des entreprises (clients moraux)
CREATE TABLE entreprises (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    raison_sociale VARCHAR(255) NOT NULL,
    siret VARCHAR(14) UNIQUE NOT NULL,
    adresse_siege TEXT NOT NULL,
    nombre_employes INT NOT NULL,
    statut ENUM('active', 'inactive') DEFAULT 'active',
    lien_adhesion VARCHAR(255) UNIQUE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **3. ASSURÉS & BÉNÉFICIAIRES**

```sql
-- Table des assurés (principaux et bénéficiaires)
CREATE TABLE assures (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL, -- NULL pour les bénéficiaires
    client_id BIGINT UNSIGNED NULL, -- Pour clients physiques
    entreprise_id BIGINT UNSIGNED NULL, -- Pour employés d'entreprise
    assure_principal_id BIGINT UNSIGNED NULL, -- Pour les bénéficiaires
    contrat_id BIGINT UNSIGNED NULL,
    lien_parente ENUM('conjoint', 'enfant', 'parent', 'autre') NULL,
    est_principal BOOLEAN DEFAULT TRUE,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    date_debut_contrat DATE NULL,
    date_fin_contrat DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL,
    FOREIGN KEY (assure_principal_id) REFERENCES assures(id) ON DELETE SET NULL,
    FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE SET NULL
);
```

### **4. PERSONNEL SUNU SANTÉ**

```sql
-- Table du personnel SUNU Santé
CREATE TABLE personnels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenoms VARCHAR(255) NULL,
    sexe ENUM('M', 'F') NOT NULL,
    date_naissance DATE NULL,
    code_parrainage VARCHAR(255) UNIQUE NULL,
    gestionnaire_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (gestionnaire_id) REFERENCES personnels(id) ON DELETE SET NULL
);
```

### **5. PRESTATAIRES DE SOINS**

```sql
-- Table des prestataires de soins
CREATE TABLE prestataires (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type_prestataire ENUM('centre_de_soins', 'laboratoire_centre_diagnostic', 'medecin_liberal', 'pharmacie', 'optique') NOT NULL,
    nom_etablissement VARCHAR(255) NOT NULL,
    adresse TEXT NOT NULL,
    medecin_controleur_id BIGINT UNSIGNED NULL,
    statut ENUM('en_attente', 'valide', 'rejete', 'suspendu') DEFAULT 'en_attente',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_controleur_id) REFERENCES personnels(id) ON DELETE SET NULL
);
```

### **6. DEMANDES D'ADHÉSION**

```sql
-- Table des demandes d'adhésion
CREATE TABLE demandes_adhesions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type_demandeur ENUM('physique', 'centre_de_soins', 'laboratoire_centre_diagnostic', 'medecin_liberal', 'pharmacie', 'optique', 'autre') NOT NULL,
    statut ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente',
    motif_rejet TEXT NULL,
    valide_par_id BIGINT UNSIGNED NULL,
    code_parrainage VARCHAR(255) NULL,
    valider_a TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (valide_par_id) REFERENCES personnels(id) ON DELETE SET NULL
);
```

### **7. CONTRATS & GARANTIES**

```sql
-- Table des contrats
CREATE TABLE contrats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    numero_police VARCHAR(255) UNIQUE NOT NULL,
    type_contrat ENUM('basic', 'standard', 'premium', 'team') NOT NULL,
    technicien_id BIGINT UNSIGNED NULL,
    prime_standard DECIMAL(12,2) NOT NULL,
    frais_gestion DECIMAL(5,2) DEFAULT 20.00, -- 20% par défaut
    commission_commercial DECIMAL(5,2) DEFAULT 3.00, -- 3% par défaut
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('propose', 'accepte', 'refuse', 'actif', 'expire', 'resilie') DEFAULT 'propose',
    est_actif BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (technicien_id) REFERENCES personnels(id) ON DELETE SET NULL
);

-- Table des catégories de garanties
CREATE TABLE categories_garanties (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    libelle VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    medecin_controleur_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (medecin_controleur_id) REFERENCES personnels(id) ON DELETE SET NULL
);

-- Table des garanties
CREATE TABLE garanties (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    libelle VARCHAR(255) UNIQUE NOT NULL,
    categorie_garantie_id BIGINT UNSIGNED NOT NULL,
    medecin_controleur_id BIGINT UNSIGNED NULL,
    plafond DECIMAL(12,2) NOT NULL,
    prix_standard DECIMAL(12,2) NOT NULL,
    taux_couverture DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (categorie_garantie_id) REFERENCES categories_garanties(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_controleur_id) REFERENCES personnels(id) ON DELETE SET NULL
);

-- Table pivot contrat_categorie_garantie
CREATE TABLE contrat_categorie_garantie (
    contrat_id BIGINT UNSIGNED NOT NULL,
    categorie_garantie_id BIGINT UNSIGNED NOT NULL,
    couverture DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    PRIMARY KEY (contrat_id, categorie_garantie_id),
    FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_garantie_id) REFERENCES categories_garanties(id) ON DELETE CASCADE
);
```

### **8. SINISTRES & FACTURES**

```sql
-- Table des sinistres
CREATE TABLE sinistres (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    assure_id BIGINT UNSIGNED NOT NULL,
    prestataire_id BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    date_sinistre DATE NOT NULL,
    statut ENUM('declare', 'en_cours', 'traite', 'cloture') DEFAULT 'declare',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (assure_id) REFERENCES assures(id) ON DELETE CASCADE,
    FOREIGN KEY (prestataire_id) REFERENCES prestataires(id) ON DELETE CASCADE
);

-- Table des factures
CREATE TABLE factures (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    numero_facture VARCHAR(255) UNIQUE NOT NULL,
    sinistre_id BIGINT UNSIGNED NOT NULL,
    prestataire_id BIGINT UNSIGNED NOT NULL,
    montant_reclame DECIMAL(12,2) NOT NULL,
    montant_a_rembourser DECIMAL(12,2) NOT NULL,
    diagnostic TEXT NOT NULL,
    photo_justificatifs JSON NOT NULL,
    ticket_moderateur DECIMAL(12,2) NOT NULL,
    statut ENUM('en_attente', 'validee_technicien', 'validee_medecin', 'autorisee_comptable', 'remboursee', 'rejetee') DEFAULT 'en_attente',
    motif_rejet TEXT NULL,
    
    -- Validation par technicien
    est_valide_par_technicien BOOLEAN DEFAULT FALSE,
    technicien_id BIGINT UNSIGNED NULL,
    valide_par_technicien_a TIMESTAMP NULL,
    
    -- Validation par médecin
    est_valide_par_medecin BOOLEAN DEFAULT FALSE,
    medecin_id BIGINT UNSIGNED NULL,
    valide_par_medecin_a TIMESTAMP NULL,
    
    -- Autorisation par comptable
    est_autorise_par_comptable BOOLEAN DEFAULT FALSE,
    comptable_id BIGINT UNSIGNED NULL,
    autorise_par_comptable_a TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (sinistre_id) REFERENCES sinistres(id) ON DELETE CASCADE,
    FOREIGN KEY (prestataire_id) REFERENCES prestataires(id) ON DELETE CASCADE,
    FOREIGN KEY (technicien_id) REFERENCES personnels(id) ON DELETE SET NULL,
    FOREIGN KEY (medecin_id) REFERENCES personnels(id) ON DELETE SET NULL,
    FOREIGN KEY (comptable_id) REFERENCES personnels(id) ON DELETE SET NULL
);
```

### **9. QUESTIONNAIRES & RÉPONSES**

```sql
-- Table des questions
CREATE TABLE questions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    libelle TEXT NOT NULL,
    type_donnee ENUM('text', 'number', 'boolean', 'date', 'file', 'select') NOT NULL,
    options JSON NULL, -- Pour les questions à choix multiples
    destinataire ENUM('physique', 'centre_de_soins', 'laboratoire_centre_diagnostic', 'medecin_liberal', 'pharmacie', 'optique', 'autre') NOT NULL,
    obligatoire BOOLEAN DEFAULT FALSE,
    est_actif BOOLEAN DEFAULT TRUE,
    cree_par_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (cree_par_id) REFERENCES personnels(id) ON DELETE SET NULL
);

-- Table des réponses aux questionnaires
CREATE TABLE reponses_questionnaire (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    question_id BIGINT UNSIGNED NOT NULL,
    demande_adhesion_id BIGINT UNSIGNED NOT NULL,
    personne_type VARCHAR(255) NOT NULL, -- Polymorphic
    personne_id BIGINT UNSIGNED NOT NULL,
    reponse_text TEXT NULL,
    reponse_bool BOOLEAN NULL,
    reponse_decimal DECIMAL(12,2) NULL,
    reponse_date DATE NULL,
    reponse_fichier VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (demande_adhesion_id) REFERENCES demandes_adhesions(id) ON DELETE CASCADE
);
```

### **10. SYSTÈME DE PARRAINAGE & INVITATIONS**

```sql
-- Table des invitations employés (pour entreprises)
CREATE TABLE invitation_employes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    entreprise_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expire_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);

-- Table des OTP
CREATE TABLE otps (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code_otp VARCHAR(6) NOT NULL,
    phone VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    expires_a TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **11. NOTIFICATIONS & COMMUNICATION**

```sql
-- Table des notifications
CREATE TABLE notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des conversations
CREATE TABLE conversations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id_1 BIGINT UNSIGNED NOT NULL,
    user_id_2 BIGINT UNSIGNED NOT NULL,
    dernier_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id_1) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des messages
CREATE TABLE messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    expediteur_id BIGINT UNSIGNED NOT NULL,
    contenu TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **12. RÉSEAUX & AFFECTATIONS**

```sql
-- Table des réseaux prestataires
CREATE TABLE reseau_prestataire (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    prestataire_id BIGINT UNSIGNED NOT NULL,
    assure_id BIGINT UNSIGNED NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (prestataire_id) REFERENCES prestataires(id) ON DELETE CASCADE,
    FOREIGN KEY (assure_id) REFERENCES assures(id) ON DELETE CASCADE
);

-- Table des portefeuilles
CREATE TABLE portefeuilles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    assure_id BIGINT UNSIGNED NOT NULL,
    solde DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (assure_id) REFERENCES assures(id) ON DELETE CASCADE
);

-- Table des transactions
CREATE TABLE transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    portefeuille_id BIGINT UNSIGNED NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (portefeuille_id) REFERENCES portefeuilles(id) ON DELETE CASCADE
);
```

## 🔄 **FLOW MÉTIER AVEC NOUVELLE ARCHITECTURE**

### **Client Physique :**
1. User + Client (type: physique, statut: prospect)
2. Demande d'adhésion + réponses questionnaire
3. Technicien valide → Contrat proposé
4. Client accepte → Client devient Assure (est_principal: true)
5. Assure peut ajouter bénéficiaires → Assure (est_principal: false, user_id: null)

### **Entreprise :**
1. User + Entreprise
2. Lien généré → employés remplissent fiches
3. RH soumet → Technicien valide → Contrat proposé
4. Entreprise accepte → chaque employé devient Assure (est_principal: true)
5. Employés peuvent ajouter bénéficiaires → Assure (est_principal: false, user_id: null)

### **Prestataire :**
1. User + Prestataire
2. Demande d'adhésion + documents
3. Médecin contrôleur valide → Prestataire activé

## ✅ **AVANTAGES DE CETTE ARCHITECTURE**

1. **Clarté** : Chaque table a un rôle précis
2. **Flexibilité** : Supporte tous les cas d'usage
3. **Performance** : Relations optimisées
4. **Maintenabilité** : Structure logique et cohérente
5. **Évolutivité** : Facile d'ajouter de nouvelles fonctionnalités

Voulez-vous que je commence par créer les nouvelles migrations dans l'ordre logique ?