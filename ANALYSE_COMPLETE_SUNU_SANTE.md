# ANALYSE COMPLÈTE DU SYSTÈME SUNU SANTÉ

## 1. PRÉSENTATION GÉNÉRALE DU SYSTÈME

Le système SUNU Santé vise à digitaliser toute la chaîne de gestion de l'assurance santé, couvrant :

- **Prospection et gestion des adhésions** (clients physiques et moraux)
- **Suivi des prestations de soins et remboursements**
- **Gestion des sinistres**
- **Interface des prestataires de soins**
- **Gestion RH et administrative interne**

## 2. ACTEURS DU SYSTÈME

| Acteur | Rôle / Description |
|--------|-------------------|
| **Assuré principal** | Client physique ou employé d'entreprise, a un compte personnel |
| **Bénéficiaire** | Personne rattachée à un assuré principal (sans compte) |
| **Entreprise** | Client moral, soumet les fiches de ses employés via un lien |
| **Commercial** | Prospecte les clients, reçoit une prime après conversion |
| **Prestataire de soins** | Centre de soins/labo/pharmacie, facture les soins |
| **Technicien** | Analyse les adhésions et factures, propose les contrats |
| **Médecin contrôleur** | Valide les adhésions des prestataires et contrôle les actes médicaux |
| **Comptable** | Valide les remboursements et suit les flux financiers |
| **Gestionnaire** | Ajoute le personnel de SUNU (hors admin global) |
| **Admin global** | Super admin qui gère les gestionnaires |

## 3. PROCESSUS MÉTIER DÉTAILLÉS

### 3.1. Adhésion Client Physique
1. Le commercial fournit un **code de parrainage** au prospect
2. Le client crée un compte assuré principal et remplit :
   - Fiche de demande d'adhésion
   - Questionnaire médical
   - Possibilité d'ajouter des bénéficiaires (eux aussi remplissent les deux fiches)
3. Le technicien analyse la demande et propose une prime
4. Le contrat est envoyé par mail (garanties, taux, options)
5. Le client peut demander des modifications
6. Si accepté → le client devient assuré, un réseau de prestataires lui est assigné

### 3.2. Adhésion Client Moral (Entreprise)
1. L'entreprise crée un compte
2. Le système génère automatiquement un **lien expirable unique**
3. Les employés accèdent au lien pour :
   - Remplir leur fiche d'adhésion et médicale
4. L'entreprise reçoit une notification par fiche remplie
5. Elle valide les fiches, puis soumet le lot complet
6. Le technicien analyse, choisit un type de contrat (Découverte, Premium, Business), puis l'envoie
7. L'entreprise peut demander des modifications
8. Si accepté → chaque employé devient assuré principal, l'entreprise paie la prime globale

### 3.3. Gestion des Prestataires
1. Le prestataire fait une demande d'adhésion (documents requis)
2. Le médecin contrôleur valide la demande
3. Une fois activé :
   - Il voit les assurés qui lui sont assignés
   - Il peut générer des factures en fin de mois
4. Les factures passent par :
   - **Technicien** : validation de la couverture contractuelle
   - **Médecin** : vérification des actes médicaux et tarifs
   - **Comptable** : remboursement

### 3.4. Sinistres
Lorsqu'un assuré ou bénéficiaire tombe malade, il est soigné par un prestataire de son réseau → ce traitement devient un **sinistre**.

### 3.5. Gestion RH de SUNU
- **Admin global** : ajoute les gestionnaires
- **Gestionnaire** : ajoute le personnel SUNU (tech, médecins, comptables, commerciaux)
- Chaque rôle a une interface et des fonctions propres

## 4. DIAGRAMMES UML À PRODUIRE

### 4.1 Diagramme de contexte
Affiche tous les acteurs externes et leur interaction globale avec le système.

### 4.2 Diagramme de cas d'utilisation
Représente les cas d'usage regroupés par :
- Assuré
- Entreprise
- Prestataire
- Commercial
- Médecin contrôleur
- Technicien
- Comptable
- Admins

### 4.3 Diagrammes d'activités
- Adhésion client physique
- Adhésion entreprise
- Processus de traitement de facture
- Soumission et gestion de sinistres

### 4.4 Diagrammes de séquence
- Interaction entre entreprise et employés pour l'adhésion
- Processus de facturation d'un prestataire → remboursement
- Workflow création de compte → validation → affectation réseau

## 5. DIAGRAMME DE CLASSES (STRUCTURE OBJET)

```
Utilisateur (abstract)
├─ AssurePrincipal
├─ Commercial
├─ Technicien
├─ MedecinControleur
├─ Comptable
├─ Gestionnaire
├─ AdminGlobal
├─ Prestataire
├─ Entreprise

Entreprise
- id
- nom
- email
- lien_adhesion
- statut

Employe
- id
- entreprise_id (FK)
- utilisateur_id (FK)

Beneficiaire
- id
- nom
- date_naissance
- assure_principal_id (FK)

Adhesion
- id
- type: physique | entreprise
- statut
- prime
- date

FicheMedicale
- id
- user_id
- reponses (JSON)

Contrat
- id
- nom
- entreprise_id | assure_id
- statut
- date_signature

CategorieGarantie
- id
- nom

Garantie
- id
- nom
- categorie_id (FK)
- taux_couverture

CentreSoins
- id
- nom
- type

AffectationCentre
- centre_id
- assure_id

Facture
- id
- prestataire_id
- assure_id
- montant
- date
- statut

ActeMedical
- id
- facture_id
- libelle
- cout

Sinistre
- id
- assure_id
- date
- description

Parrainage
- code
- commercial_id
- client_id
```

## 6. MODÈLE RELATIONNEL (TABLES CLÉS)

### Tables principales existantes (à analyser) :
- `utilisateurs`
- `assures_principaux`
- `beneficiaires`
- `entreprises`
- `employes`
- `adhesions`
- `contrats`
- `garanties`
- `categories_garanties`
- `centres_soins`
- `affectations`
- `factures`
- `actes_medicaux`
- `sinistres`
- `prestataires`
- `questionnaires`
- `parrainages`

## 7. ANALYSE DE L'EXISTANT

D'après l'analyse de votre code, vous avez déjà :

### ✅ Ce qui est bien implémenté :
- Structure de base des modèles (User, Client, Assure, Contrat, etc.)
- Système d'authentification avec JWT
- Gestion des rôles avec Spatie Permission
- Système de questions/réponses pour les questionnaires
- Gestion des factures avec workflow de validation
- Système de parrainage pour les commerciaux

### 🔧 Ce qui nécessite des améliorations :
- Gestion des entreprises et employés
- Workflow complet d'adhésion avec liens expirables
- Système de notifications
- Gestion des bénéficiaires
- Interface de gestion des prestataires
- Dashboard pour chaque rôle

## 8. PLAN D'IMPLÉMENTATION (3 SEMAINES)

### Semaine 1 : Backend - Core Business Logic
- Finaliser les modèles manquants
- Implémenter le workflow d'adhésion entreprise
- Système de liens expirables
- Gestion des bénéficiaires

### Semaine 2 : Backend - Workflows & Notifications
- Système de notifications
- Workflow de facturation complet
- Gestion des sinistres
- API endpoints pour tous les rôles

### Semaine 3 : Frontend & Tests
- Interfaces utilisateur par rôle
- Tests d'intégration
- Documentation API
- Déploiement

## 9. PROMPT POUR LE DESIGNER FRONTEND

```
PROMPT POUR DESIGNER FRONTEND - SUNU SANTÉ

Contexte : Système de gestion d'assurance santé avec 9 rôles différents

TECHNOLOGIES : Vue.js 3 + Composition API, Vuetify 3, Vue Router, Pinia

REQUIS :
1. Interface responsive et moderne
2. Séparation complète des interfaces par rôle
3. Design system cohérent
4. Navigation intuitive
5. Formulaires optimisés UX

ROLES ET INTERFACES :

1. ASSURÉ PRINCIPAL
- Dashboard personnel avec statistiques
- Gestion des bénéficiaires (ajout/suppression/modification)
- Consultation des centres de soins assignés
- Historique des remboursements
- Profil et paramètres

2. ENTREPRISE
- Dashboard entreprise avec statistiques employés
- Gestion des liens d'adhésion (génération/expiration)
- Suivi des fiches employés
- Consultation des contrats
- Rapports financiers

3. COMMERCIAL
- Dashboard commercial avec statistiques clients
- Gestion des codes de parrainage
- Suivi des prospects
- Historique des conversions
- Commission et paiements

4. TECHNICIEN
- Dashboard technique
- Validation des demandes d'adhésion
- Gestion des contrats
- Validation des factures
- Rapports d'analyse

5. MÉDECIN CONTRÔLEUR
- Dashboard médical
- Validation des prestataires
- Contrôle des factures médicales
- Gestion des questionnaires
- Tarifs de référence

6. COMPTABLE
- Dashboard financier
- Validation des remboursements
- Suivi des flux financiers
- Rapports comptables
- Gestion des paiements

7. GESTIONNAIRE
- Dashboard RH
- Gestion du personnel
- Création de comptes
- Suivi des performances
- Rapports RH

8. ADMIN GLOBAL
- Dashboard administrateur
- Gestion des gestionnaires
- Configuration système
- Logs et audit
- Rapports globaux

9. PRESTATAIRE
- Dashboard prestataire
- Liste des assurés assignés
- Génération de factures
- Suivi des remboursements
- Profil établissement

COMPOSANTS RÉUTILISABLES :
- Navigation par rôle
- Tableaux de données avec filtres
- Formulaires dynamiques
- Modales de confirmation
- Notifications toast
- Graphiques et statistiques
- Upload de fichiers
- Signature électronique

PALETTE DE COULEURS :
- Primaire : #1976D2 (Bleu SUNU)
- Secondaire : #424242 (Gris foncé)
- Succès : #4CAF50 (Vert)
- Avertissement : #FF9800 (Orange)
- Erreur : #F44336 (Rouge)
- Info : #2196F3 (Bleu clair)

ARCHITECTURE :
- Layout principal avec sidebar navigation
- Routes protégées par rôle
- Store Pinia par module métier
- Composants modulaires
- API service centralisé
- Gestion d'état globale

FONCTIONNALITÉS SPÉCIALES :
- Mode sombre/clair
- Notifications temps réel
- Export PDF/Excel
- Recherche globale
- Filtres avancés
- Pagination intelligente
- Drag & drop pour uploads
- Signature électronique des contrats

DÉLIVRABLES :
1. Maquettes Figma/Adobe XD
2. Composants Vue.js
3. Pages par rôle
4. Documentation technique
5. Guide d'utilisation
```

## 10. PROCHAINES ÉTAPES

1. **Analyser votre code existant** pour identifier les gaps
2. **Créer les migrations manquantes** pour les nouvelles fonctionnalités
3. **Implémenter les workflows** d'adhésion entreprise
4. **Développer les interfaces** par rôle
5. **Tester et déployer**

Voulez-vous que je commence par analyser votre code existant pour identifier précisément ce qui manque et créer un plan d'action détaillé ? 