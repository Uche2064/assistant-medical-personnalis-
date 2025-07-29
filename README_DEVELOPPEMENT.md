# 📋 SUNU Santé - Backend API

## 🎯 **Présentation du Projet**

SUNU Santé est une plateforme complète de gestion d'assurance santé qui digitalise toute la chaîne de gestion, de la prospection à la facturation.

## ✅ **Fonctionnalités terminées**
- [x] Authentification complète (OTP, JWT, email)
- [x] Gestion des rôles et permissions (middleware personnalisé)
- [x] Gestion des gestionnaires et personnels
- [x] Questions médicales dynamiques par type de demandeur
- [x] **Client Physique** : Formulaire complet + bénéficiaires + notification technicien
- [x] **Entreprise** : Lien unique + soumission employé + soumission groupée + notification technicien
- [x] **Prestataire** : Formulaire simplifié + documents requis + notification médecin contrôleur
- [x] **Validation des demandes** : 
  - ✅ Proposition de contrat par technicien (sélection contrat + création proposition + notification client)
  - ✅ Validation prestataire par médecin contrôleur (simple bouton valider → statut validé + notification)

## 🏗️ **Architecture Technique**

### **Technologies utilisées :**
- **Backend** : Laravel 12 + PHP 8.2
- **Base de données** : MySQL avec migrations optimisées
- **Authentification** : JWT + OTP email
- **Permissions** : Spatie/Laravel-Permission avec middleware personnalisé
- **Uploads** : Helpers personnalisés (ImageUploadHelper, PdfUploadHelper)
- **Notifications** : Email + Notifications in-app

### **Structure de la base de données :**
- **22 migrations** optimisées et non-redondantes
- **19 modèles** avec relations Eloquent
- **11 Enums PHP 8.1+** pour la type safety
- **Soft deletes** sur tous les modèles principaux

## 👥 **Rôles et Permissions**

### **9 Rôles distincts :**
1. **admin_global** : Super admin (gère les gestionnaires)
2. **gestionnaire** : Admin (gère le personnel)
3. **technicien** : Analyse les demandes, propose des contrats
4. **medecin_controleur** : Valide les prestataires, contrôle les factures
5. **commercial** : Prospecte les clients
6. **comptable** : Gère les flux financiers
7. **user** : Client prospect
8. **prestataire** : Centre de soins, pharmacie, etc.
9. **assure_principal** : Client assuré

## 🔄 **Flows Métier Détaillés**

### **1. Flow Client Physique**
```
1. Commercial → Donne code parrainage au prospect
2. Prospect → Crée compte + Remplit fiche adhésion + Questionnaire médical
3. Prospect → Ajoute bénéficiaires (optionnel)
4. Système → Notifie technicien
5. Technicien → Consulte demande + Clique "Accepter"
6. Modal → Sélectionne contrat (Découverte/Standard/Premium)
7. Technicien → Configure prime + Clique "Proposer"
8. Système → Crée proposition + Envoie email avec lien d'acceptation
9. Client → Consulte proposition + Accepte/Refuse
10. Si accepté → Client devient assuré + Réseau prestataires assigné
```

### **2. Flow Entreprise**
```
1. Entreprise → Crée compte
2. Système → Génère lien unique expirable
3. Entreprise → Partage lien aux employés
4. Employés → Remplissent fiches individuelles
5. Système → Notifie entreprise à chaque fiche reçue
6. Entreprise → Consulte toutes les fiches + Soumet demande groupée
7. Système → Notifie technicien
8. Technicien → Analyse + Propose contrat (même flow que client physique)
9. Si accepté → Chaque employé devient assuré principal
```

### **3. Flow Prestataire**
```
1. Prestataire → Crée compte + Remplit fiche adhésion
2. Prestataire → Upload documents requis selon type (pharmacie, centre de soins, etc.)
3. Système → Notifie médecin contrôleur
4. Médecin → Consulte demande + Télécharge documents
5. Médecin → Clique "Valider" (simple bouton)
6. Système → Statut passe à "validé" + Notifie prestataire
7. Prestataire → Peut être assigné au réseau des assurés
```

### **4. Flow Facturation (à implémenter)**
```
1. Assuré → Se fait soigner chez prestataire
2. Prestataire → Génère facture en fin de mois
3. Système → Notifie technicien
4. Technicien → Vérifie couverture contractuelle
5. Système → Notifie médecin contrôleur
6. Médecin → Vérifie actes médicaux et tarifs
7. Système → Notifie comptable
8. Comptable → Valide remboursement
9. Système → Rembourse prestataire
```

## 🎨 **Interfaces pour le Designer**

### **Interface Admin Global**
- **Dashboard** : Statistiques générales, liste des gestionnaires
- **Gestion des gestionnaires** : CRUD complet (créer, lister, modifier, suspendre)
- **Vue d'ensemble** : Graphiques de performance, métriques clés

### **Interface Gestionnaire**
- **Dashboard** : Statistiques du personnel sous sa responsabilité
- **Gestion du personnel** : CRUD complet (techniciens, médecins, comptables, commerciaux)
- **Suivi des demandes** : Vue d'ensemble des demandes en cours

### **Interface Technicien**
- **Dashboard** : Demandes d'adhésion en attente, statistiques
- **Liste des demandes** : Filtres par type (physique, entreprise), statut
- **Consultation demande** : Vue détaillée avec toutes les informations
- **Modal proposition contrat** : 
  - Sélection du type de contrat (Découverte/Standard/Premium)
  - Configuration de la prime
  - Sélection des garanties incluses
  - Commentaires optionnels
- **Suivi des propositions** : Statut des propositions envoyées

### **Interface Médecin Contrôleur**
- **Dashboard** : Demandes prestataires en attente, factures à contrôler
- **Validation prestataires** : 
  - Liste des demandes prestataires
  - Téléchargement des documents
  - Bouton "Valider" simple
- **Contrôle factures** : Interface de vérification des actes médicaux
- **Gestion des questions** : CRUD des questions médicales par type de demandeur

### **Interface Commercial**
- **Dashboard** : Statistiques de prospection, codes parrainage générés
- **Gestion des prospects** : Suivi des prospects, génération de codes
- **Rapports** : Performance commerciale, conversions

### **Interface Comptable**
- **Dashboard** : Flux financiers, factures en attente de remboursement
- **Validation remboursements** : Interface de validation des paiements
- **Rapports financiers** : Bilans, flux de trésorerie

### **Interface Client Physique**
- **Dashboard** : Informations personnelles, contrats actifs
- **Demande d'adhésion** : Formulaire multi-étapes avec questionnaire médical
- **Gestion des bénéficiaires** : Ajout/modification/suppression
- **Consultation contrats** : Détails des contrats, garanties
- **Acceptation contrat** : Page dédiée via lien email

### **Interface Entreprise**
- **Dashboard** : Employés, demandes en cours
- **Génération lien invitation** : Interface pour créer et partager le lien
- **Suivi des fiches employés** : Vue d'ensemble des fiches reçues
- **Soumission groupée** : Interface pour soumettre la demande complète

### **Interface Prestataire**
- **Dashboard** : Assurés assignés, factures générées
- **Demande d'adhésion** : Formulaire avec upload de documents
- **Gestion des assurés** : Liste des assurés qui peuvent venir se faire soigner
- **Génération de factures** : Interface pour créer et envoyer les factures
- **Suivi des remboursements** : Statut des factures envoyées

### **Interface Assuré Principal**
- **Dashboard** : Consommation, prestataires assignés
- **Gestion des bénéficiaires** : Ajout/modification/suppression
- **Consultation réseau** : Liste des prestataires disponibles
- **Suivi des soins** : Historique des consultations et remboursements

## 🔧 **Points Techniques Importants**

### **Authentification**
- **JWT** pour les API
- **OTP email** pour la validation
- **Middleware personnalisé** pour les rôles
- **Tokens d'acceptation** pour les contrats (7 jours)

### **Uploads**
- **Images** : Photos de profil, documents
- **PDFs** : Documents prestataires, questionnaires
- **Validation** : Types MIME, tailles, formats

### **Notifications**
- **Email** : Templates Laravel
- **In-app** : Notifications push
- **Jobs** : Traitement asynchrone

### **Sécurité**
- **Middleware API Key** : Protection des endpoints
- **Validation stricte** : FormRequests pour chaque endpoint
- **Soft deletes** : Pas de suppression définitive
- **Logs** : Traçabilité complète

## 📊 **Endpoints API Principaux**

### **🔐 Authentification**
```
POST /api/v1/auth/register                    # Inscription utilisateur
POST /api/v1/auth/login                       # Connexion
POST /api/v1/auth/send-otp                   # Envoi OTP
POST /api/v1/auth/verify-otp                 # Vérification OTP
POST /api/v1/auth/refresh-token              # Renouvellement token
POST /api/v1/auth/logout                     # Déconnexion
POST /api/v1/auth/change-password            # Changement mot de passe
GET  /api/v1/auth/check-unique               # Vérification unicité email
POST /api/v1/auth/forgot-password            # Demande reset mot de passe
POST /api/v1/auth/reset-password             # Reset mot de passe
GET  /api/v1/auth/me                         # Informations utilisateur connecté
GET  /api/v1/auth/test-roles                 # Test des rôles (debug)
```

### **👥 Gestion des Gestionnaires (Admin Global)**
```
POST   /api/v1/admin/gestionnaires           # Créer un gestionnaire
GET    /api/v1/admin/gestionnaires           # Liste des gestionnaires
GET    /api/v1/admin/gestionnaires/stats     # Statistiques gestionnaires
GET    /api/v1/admin/gestionnaires/{id}      # Détails d'un gestionnaire
PATCH  /api/v1/admin/gestionnaires/{id}/suspend    # Suspendre
PATCH  /api/v1/admin/gestionnaires/{id}/activate   # Activer
DELETE /api/v1/admin/gestionnaires/{id}      # Supprimer
```

### **👨‍💼 Gestion du Personnel (Gestionnaire)**
```
# Lecture (Admin + Gestionnaire)
GET    /api/v1/gestionnaire/personnels       # Liste du personnel
GET    /api/v1/gestionnaire/personnels/stats # Statistiques personnel
GET    /api/v1/gestionnaire/personnels/{id}  # Détails d'un personnel

# Écriture (Gestionnaire uniquement)
POST   /api/v1/gestionnaire/personnels       # Créer un personnel
PATCH  /api/v1/gestionnaire/personnels/{id}/suspend   # Suspendre
PATCH  /api/v1/gestionnaire/personnels/{id}/activate  # Activer
DELETE /api/v1/gestionnaire/personnels/{id}  # Supprimer
```

### **❓ Gestion des Questions (Médecin Contrôleur)**
```
GET    /api/v1/questions                     # Questions par destinataire
GET    /api/v1/questions/all                 # Toutes les questions
GET    /api/v1/questions/{id}                # Détails d'une question
POST   /api/v1/questions                     # Créer questions (bulk)
PUT    /api/v1/questions/{id}                # Modifier une question
PATCH  /api/v1/questions/{id}/toggle         # Activer/Désactiver
DELETE /api/v1/questions/{id}                # Supprimer une question
POST   /api/v1/questions/bulk-delete         # Suppression en masse
```

### **📋 Demandes d'Adhésion**
```
# Soumission des demandes
POST   /api/v1/demandes-adhesions            # Client physique
POST   /api/v1/demandes-adhesions/prestataire # Prestataire
POST   /api/v1/demandes-adhesions/entreprise # Entreprise

# Consultation (Technicien/Médecin/Admin)
GET    /api/v1/demandes-adhesions            # Liste des demandes
GET    /api/v1/demandes-adhesions/{id}       # Détails d'une demande
GET    /api/v1/demandes-adhesions/{id}/download # Télécharger demande

# Validation
PUT    /api/v1/demandes-adhesions/{id}/proposer-contrat    # Technicien
PUT    /api/v1/demandes-adhesions/{id}/valider-prestataire # Médecin
PUT    /api/v1/demandes-adhesions/{id}/rejeter             # Technicien/Médecin
```

### **🏢 Gestion Entreprise**
```
POST   /api/v1/entreprise/inviter-employe    # Générer lien invitation
POST   /api/v1/entreprise/soumettre-demande-adhesion # Soumission groupée
```

### **👥 Formulaire Employé (Public)**
```
GET    /api/v1/employes/formulaire/{token}   # Afficher formulaire
POST   /api/v1/employes/formulaire/{token}   # Soumettre fiche employé
```

### **🏥 Prestataires de Soins**
```
GET    /api/v1/prestataire/dashboard         # Dashboard prestataire
GET    /api/v1/prestataire/profile           # Profil prestataire
PUT    /api/v1/prestataire/profile           # Modifier profil
GET    /api/v1/prestataire/questions         # Questions prestataire
GET    /api/v1/prestataire/documents-requis  # Documents requis
POST   /api/v1/prestataire/valider-documents # Valider documents
```

### **📊 Catégories de Garanties**
```
# Lecture (Médecin + Technicien)
GET    /api/v1/categories-garanties          # Liste des catégories
GET    /api/v1/categories-garanties/{id}     # Détails d'une catégorie

# Écriture (Médecin uniquement)
POST   /api/v1/categories-garanties          # Créer une catégorie
PUT    /api/v1/categories-garanties/{id}     # Modifier une catégorie
DELETE /api/v1/categories-garanties/{id}     # Supprimer une catégorie
```

### **🛡️ Garanties**
```
# Lecture (Médecin + Technicien)
GET    /api/v1/garanties                     # Liste des garanties
GET    /api/v1/garanties/{id}                # Détails d'une garantie

# Écriture (Médecin uniquement)
POST   /api/v1/garanties                     # Créer une garantie
PUT    /api/v1/garanties/{id}                # Modifier une garantie
DELETE /api/v1/garanties/{id}                # Supprimer une garantie
```

### **📄 Contrats**
```
GET    /api/v1/contrats                      # Liste des contrats
POST   /api/v1/contrats                      # Créer un contrat (Technicien)
GET    /api/v1/contrats/{id}                 # Détails d'un contrat (Technicien)
PUT    /api/v1/contrats/{id}                 # Modifier un contrat (Technicien)
DELETE /api/v1/contrats/{id}                 # Supprimer un contrat (Technicien)
```

### **👨‍👩‍👧‍👦 Bénéficiaires (Assuré Principal)**
```
GET    /api/v1/assure/beneficiaires          # Liste des bénéficiaires
GET    /api/v1/assure/beneficiaires/{id}     # Détails d'un bénéficiaire
POST   /api/v1/assure/beneficiaires          # Ajouter un bénéficiaire
PUT    /api/v1/assure/beneficiaires/{id}     # Modifier un bénéficiaire
DELETE /api/v1/assure/beneficiaires/{id}     # Supprimer un bénéficiaire
```

### **👤 Clients**
```
GET    /api/v1/clients                       # Liste des clients
GET    /api/v1/clients/{id}                  # Détails d'un client
PUT    /api/v1/clients/{id}                  # Modifier un client
DELETE /api/v1/clients/{id}                  # Supprimer un client
```

### **🔗 Utilitaires**
```
GET    /api/v1/has-demande                   # Vérifier si utilisateur a une demande
GET    /api/v1/contrats-disponibles          # Contrats disponibles pour proposition
```

## 🔐 **Authentification et Permissions**

### **Headers requis**
```
Authorization: Bearer {jwt_token}
Accept: application/json
Content-Type: application/json
```

### **Codes de réponse**
- `200` : Succès
- `201` : Créé avec succès
- `400` : Erreur de validation
- `401` : Non authentifié
- `403` : Non autorisé (rôles insuffisants)
- `404` : Ressource non trouvée
- `422` : Erreur de validation des données
- `500` : Erreur serveur

### **Format de réponse standard**
```json
{
    "status": true,
    "message": "Message de succès",
    "data": {
        // Données de la réponse
    }
}
```

### **Format d'erreur**
```json
{
    "status": false,
    "message": "Message d'erreur",
    "errors": {
        // Détails des erreurs de validation
    }
}
```

## 🚀 **Prochaines étapes**

### **Module Contrats** (Priorité 1)
- [ ] Acceptation/refus de contrat par le client
- [ ] Gestion des modifications de contrat
- [ ] Finalisation des garanties

### **Module Prestataires** (Priorité 2)
- [ ] Assignment des prestataires aux assurés
- [ ] Gestion du réseau de prestataires
- [ ] Interface prestataire complète

### **Module Facturation** (Priorité 3)
- [ ] Génération de factures par les prestataires
- [ ] Validation par les techniciens
- [ ] Contrôle médical des actes
- [ ] Processus de remboursement

### **Module Sinistres** (Priorité 4)
- [ ] Déclaration de sinistres
- [ ] Traitement et suivi
- [ ] Interface assuré

## 📝 **Notes pour le Designer**

### **Design System**
- **Couleurs** : Palette professionnelle (bleu médical, vert validation, rouge erreur)
- **Typographie** : Lisibilité optimale pour les formulaires complexes
- **Espacement** : Cohérence dans tous les modules
- **Responsive** : Mobile-first pour les formulaires de terrain

### **UX Priorités**
- **Simplicité** : Interfaces claires, pas de surcharge
- **Efficacité** : Workflows optimisés pour les utilisateurs métier
- **Feedback** : Notifications claires, états de chargement
- **Accessibilité** : Standards WCAG pour l'inclusion

### **Interactions Clés**
- **Modals** : Pour les sélections de contrats, confirmations
- **Wizards** : Pour les formulaires complexes (adhésion, bénéficiaires)
- **Drag & Drop** : Pour les uploads de documents
- **Real-time** : Notifications instantanées

---

**Ce README sera mis à jour à chaque nouvelle fonctionnalité implémentée.** 