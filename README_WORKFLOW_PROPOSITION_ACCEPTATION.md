# Workflow de Proposition et Acceptation de Contrat - Documentation Complète

## 🎯 Vue d'ensemble

Ce document décrit le workflow complet de proposition et d'acceptation de contrats, incluant l'assignation de réseaux de prestataires.

## 🔄 Workflow Détaillé

### **📋 Étape 1 : Interface Technicien**

#### **Routes disponibles :**

##### **1. Liste des clients avec recherche**
```
GET /api/technicien/clients?search=nom_ou_email
```
- **Permissions** : `technicien`
- **Fonctionnalité** : Recherche dynamique des clients physique/entreprise
- **Réponse** : Liste des demandes avec statuts et informations client

##### **2. Détails d'un client spécifique**
```
GET /api/demandes-adhesions/{id}/details-client
```
- **Permissions** : `technicien`
- **Fonctionnalité** : Statistiques détaillées du client (durée d'attente, priorité)
- **Réponse** : Informations client + métriques de performance

##### **3. Liste des prestataires avec recherche**
```
GET /api/technicien/prestataires?search=nom_ou_adresse&type_prestataire=pharmacie
```
- **Permissions** : `technicien`
- **Fonctionnalité** : Recherche dynamique des prestataires par type
- **Réponse** : Liste des prestataires avec adresses et contacts

### **🔧 Étape 2 : Proposition de Contrat**

#### **Route pour récupérer les contrats disponibles :**
```
GET /api/demandes-adhesions/contrats-disponibles
```
- **Permissions** : `technicien`
- **Réponse** : Liste des contrats avec garanties groupées par catégorie

#### **Route pour proposer un contrat :**
```
PUT /api/demandes-adhesions/{demande_id}/proposer-contrat
```
- **Permissions** : `technicien`
- **Payload** :
```json
{
    "contrat_id": 1,
    "prime_proposee": 45000,
    "taux_couverture": 85,
    "frais_gestion": 15,
    "commentaires": "Proposition adaptée à votre profil",
    "garanties_incluses": [1, 2, 3]
}
```

### **📱 Étape 3 : Interface Client**

#### **Route pour récupérer les contrats proposés :**
```
GET /api/client/contrats-proposes
```
- **Permissions** : `physique`, `entreprise`
- **Réponse** : Liste des propositions avec détails complets

#### **Route pour accepter un contrat :**
```
POST /api/client/contrats-proposes/{proposition_id}/accepter
```
- **Permissions** : `physique`, `entreprise`
- **Fonctionnalité** : Acceptation du contrat avec création automatique

### **🏥 Étape 4 : Assignation Réseau Prestataires**

#### **Route pour assigner un réseau :**
```
POST /api/technicien/assigner-reseau-prestataires
```
- **Permissions** : `technicien`
- **Payload** :
```json
{
    "client_id": 1,
    "contrat_id": 1,
    "prestataires": {
        "pharmacies": [1, 2],
        "centres_soins": [3, 4, 5],
        "optiques": [6, 7],
        "laboratoires": [8, 9],
        "centres_diagnostic": [10, 11]
    }
}
```

## 📊 Nouveaux Statuts

### **StatutDemandeAdhesionEnum :**
- `EN_ATTENTE` : Demande soumise, en attente de traitement
- `PROPOSEE` : Proposition de contrat en cours
- `ACCEPTEE` : Contrat accepté par le client
- `VALIDEE` : Demande validée (prestataires uniquement)
- `REJETEE` : Demande rejetée

## 🗄️ Nouvelles Tables

### **Table `client_contrats` :**
```sql
CREATE TABLE client_contrats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    client_id BIGINT NOT NULL,
    contrat_id BIGINT NOT NULL,
    type_client ENUM('physique', 'entreprise') NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('ACTIF', 'INACTIF', 'EXPIRE') DEFAULT 'ACTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (contrat_id) REFERENCES contrats(id)
);
```

### **Table `client_prestataires` :**
```sql
CREATE TABLE client_prestataires (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    client_contrat_id BIGINT NOT NULL,
    prestataire_id BIGINT NOT NULL,
    type_prestataire ENUM('pharmacie', 'centre_soins', 'optique', 'laboratoire', 'centre_diagnostic') NOT NULL,
    statut ENUM('ACTIF', 'INACTIF') DEFAULT 'ACTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_contrat_id) REFERENCES client_contrats(id),
    FOREIGN KEY (prestataire_id) REFERENCES prestataires(id)
);
```

## 📧 Notifications Automatiques

### **Types de Notifications :**

#### **Pour Client :**
- `contrat_propose` : Nouvelle proposition de contrat
- `contrat_accepte` : Contrat accepté avec succès
- `reseau_assigne` : Réseau de prestataires assigné

#### **Pour Technicien :**
- `contrat_accepte_technicien` : Client a accepté le contrat
- `reseau_assigne_technicien` : Réseau assigné avec succès

## 🔐 Sécurité et Permissions

### **Rôles et Permissions :**

#### **Technicien :**
- ✅ Voir les demandes physique/entreprise
- ✅ Proposer des contrats
- ✅ Assigner des réseaux de prestataires
- ✅ Voir les statistiques clients
- ✅ Rechercher clients et prestataires

#### **Client (Physique/Entreprise) :**
- ✅ Voir ses propositions de contrats
- ✅ Accepter/refuser les contrats
- ✅ Voir son réseau de prestataires assigné

## 🚨 Gestion des Erreurs

### **Erreurs Courantes :**

#### **Proposition Non Valide :**
```json
{
    "success": false,
    "message": "Proposition non valide ou déjà traitée",
    "code": 400
}
```

#### **Prestataires Non Disponibles :**
```json
{
    "success": false,
    "message": "Certains prestataires ne sont pas disponibles dans cette zone",
    "code": 400
}
```

#### **Accès Non Autorisé :**
```json
{
    "success": false,
    "message": "Accès non autorisé",
    "code": 403
}
```

## 📋 Exemples d'Utilisation

### **1. Technicien recherche un client :**
```bash
curl -X GET "http://localhost:8000/api/technicien/clients?search=john" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### **2. Technicien propose un contrat :**
```bash
curl -X PUT "http://localhost:8000/api/demandes-adhesions/1/proposer-contrat" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "contrat_id": 1,
    "prime_proposee": 45000,
    "taux_couverture": 85,
    "frais_gestion": 15,
    "commentaires": "Proposition adaptée à votre profil",
    "garanties_incluses": [1, 2, 3]
  }'
```

### **3. Client accepte un contrat :**
```bash
curl -X POST "http://localhost:8000/api/client/contrats-proposes/1/accepter" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### **4. Technicien assigne un réseau :**
```bash
curl -X POST "http://localhost:8000/api/technicien/assigner-reseau-prestataires" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "contrat_id": 1,
    "prestataires": {
      "pharmacies": [1, 2],
      "centres_soins": [3, 4, 5],
      "optiques": [6, 7],
      "laboratoires": [8, 9],
      "centres_diagnostic": [10, 11]
    }
  }'
```

## 🔄 Workflow Complet

### **Diagramme de Flux :**

```
Technicien sélectionne client
         ↓
    Popup contrats disponibles
         ↓
    Technicien propose contrat
         ↓
    Statut → EN_PROPOSITION
         ↓
    Client reçoit notification
         ↓
    Client consulte propositions
         ↓
    Client accepte contrat
         ↓
    Statut → ACCEPTEE
         ↓
    Contrat créé automatiquement
         ↓
    Technicien assigne réseau
         ↓
    Client notifié du réseau
```

## 📊 Métriques et Statistiques

### **Pour Technicien :**
- Durée d'attente des demandes
- Priorité basée sur l'ancienneté
- Taux d'acceptation des propositions
- Nombre de prestataires assignés

### **Pour Client :**
- Historique des propositions
- Statut des contrats
- Réseau de prestataires assigné

---

*Ce document décrit l'implémentation complète du workflow de proposition et d'acceptation de contrats.* 