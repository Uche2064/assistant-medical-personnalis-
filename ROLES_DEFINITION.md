# 📋 DÉFINITION DES RÔLES - SUNU SANTÉ

## 🎯 **Vue d'ensemble**

Le système SUNU Santé utilise 9 rôles distincts organisés en 2 catégories principales :

### **🏢 RÔLES INTERNES** (Personnel SUNU Santé)
### **👥 RÔLES EXTERNES** (Clients/Partners)

---

## 🏢 **RÔLES INTERNES - Personnel SUNU Santé**

### **1. ADMIN_GLOBAL** 
- **Description** : Super administrateur du système
- **Responsabilités** :
  - Gestion des gestionnaires
  - Configuration système globale
  - Accès total à toutes les fonctionnalités
  - Audit et logs système
- **Accès** : Toutes les fonctionnalités

### **2. GESTIONNAIRE**
- **Description** : Administrateur RH
- **Responsabilités** :
  - Gestion du personnel SUNU Santé
  - Création des comptes employés
  - Suivi des performances
  - Gestion des rôles et permissions
- **Accès** : Gestion du personnel, statistiques RH

### **3. TECHNICIEN**
- **Description** : Analyste technique
- **Responsabilités** :
  - Analyse des demandes d'adhésion
  - Proposition de contrats
  - Validation des factures
  - Gestion des garanties
- **Accès** : Demandes d'adhésion, contrats, factures

### **4. MEDECIN_CONTROLEUR**
- **Description** : Contrôle médical
- **Responsabilités** :
  - Validation des prestataires de soins
  - Contrôle des actes médicaux
  - Gestion des questionnaires médicaux
  - Validation des factures médicales
- **Accès** : Prestataires, questions médicales, factures médicales

### **5. COMMERCIAL**
- **Description** : Prospecteur
- **Responsabilités** :
  - Prospection des clients
  - Génération de codes de parrainage
  - Suivi des conversions
  - Gestion des commissions
- **Accès** : Prospects, codes parrainage, statistiques commerciales

### **6. COMPTABLE**
- **Description** : Gestionnaire financier
- **Responsabilités** :
  - Validation des remboursements
  - Suivi des flux financiers
  - Rapports comptables
  - Gestion des paiements
- **Accès** : Factures, remboursements, rapports financiers

---

## 👥 **RÔLES EXTERNES - Clients/Partners**

### **7. PHYSIQUE**
- **Description** : Client personne physique
- **Responsabilités** :
  - Demande d'adhésion
  - Gestion des bénéficiaires
  - Consultation des contrats
  - Suivi des remboursements
- **Accès** : Profil personnel, bénéficiaires, contrats

### **8. ENTREPRISE**
- **Description** : Client moral
- **Responsabilités** :
  - Gestion des employés
  - Soumission groupée d'adhésions
  - Suivi des contrats entreprise
  - Génération de liens d'invitation
- **Accès** : Employés, contrats entreprise, liens d'invitation

### **9. PRESTATAIRE**
- **Description** : Centre de soins
- **Responsabilités** :
  - Demande d'adhésion au réseau
  - Facturation des soins
  - Gestion des assurés assignés
  - Suivi des remboursements
- **Accès** : Profil établissement, assurés assignés, facturation

---

## 🔄 **HIÉRARCHIE ET RELATIONS**

```
ADMIN_GLOBAL
    ↓ (gère)
GESTIONNAIRE
    ↓ (gère)
PERSONNEL (Technicien, Médecin, Commercial, Comptable)
    ↓ (interagit avec)
CLIENTS (Physique, Entreprise, Prestataire)
```

## 🛡️ **PERMISSIONS PAR RÔLE**

### **Accès Lecture**
- **ADMIN_GLOBAL** : Tout
- **GESTIONNAIRE** : Personnel, statistiques RH
- **TECHNICIEN** : Demandes d'adhésion, contrats, factures
- **MEDECIN_CONTROLEUR** : Prestataires, questions médicales, factures médicales
- **COMMERCIAL** : Prospects, codes parrainage
- **COMPTABLE** : Factures, remboursements, rapports financiers
- **PHYSIQUE** : Profil personnel, bénéficiaires, contrats
- **ENTREPRISE** : Employés, contrats entreprise
- **PRESTATAIRE** : Profil établissement, assurés assignés

### **Accès Écriture**
- **ADMIN_GLOBAL** : Tout
- **GESTIONNAIRE** : Création/modification personnel
- **TECHNICIEN** : Proposition contrats, validation demandes
- **MEDECIN_CONTROLEUR** : Validation prestataires, questions médicales
- **COMMERCIAL** : Génération codes parrainage
- **COMPTABLE** : Validation remboursements
- **PHYSIQUE** : Demande adhésion, gestion bénéficiaires
- **ENTREPRISE** : Gestion employés, soumission groupée
- **PRESTATAIRE** : Demande adhésion, facturation

---

## 📊 **WORKFLOWS PAR RÔLE**

### **Flow Commercial**
1. Prospecte client
2. Génère code parrainage
3. Suit conversion
4. Reçoit commission

### **Flow Technicien**
1. Reçoit demande d'adhésion
2. Analyse dossier
3. Propose contrat
4. Valide factures

### **Flow Médecin Contrôleur**
1. Reçoit demande prestataire
2. Valide documents
3. Contrôle factures médicales
4. Gère questionnaires

### **Flow Comptable**
1. Reçoit factures validées
2. Vérifie remboursements
3. Valide paiements
4. Génère rapports

---

## 🔧 **IMPLÉMENTATION TECHNIQUE**

### **Dans RoleEnum.php**
```php
// Rôles internes
case ADMIN_GLOBAL = "admin_global";
case GESTIONNAIRE = "gestionnaire";
case TECHNICIEN = 'technicien';
case MEDECIN_CONTROLEUR = 'medecin_controleur';
case COMMERCIAL = 'commercial';
case COMPTABLE = 'comptable';

// Rôles externes
case PHYSIQUE = 'physique';
case ENTREPRISE = 'entreprise';
case PRESTATAIRE = 'prestataire';
```

### **Méthodes utilitaires**
- `getInternalRoles()` : Rôles internes
- `getExternalRoles()` : Rôles externes
- `isInternal()` : Vérifie si rôle interne
- `isExternal()` : Vérifie si rôle externe
- `getDescription()` : Description du rôle

---

## ✅ **VALIDATION**

Cette définition des rôles est maintenant :
- ✅ **Claire** : Chaque rôle a une responsabilité précise
- ✅ **Cohérente** : Pas de chevauchement de responsabilités
- ✅ **Évolutive** : Structure modulaire pour ajouter de nouveaux rôles
- ✅ **Sécurisée** : Permissions bien définies par rôle
- ✅ **Documentée** : Code et documentation alignés 