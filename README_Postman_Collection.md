# 📚 Documentation API Postman - AMP Backend SUNU Santé

## 🎯 Vue d'ensemble

Cette documentation contient **20 modules Postman** couvrant toutes les fonctionnalités de l'API backend AMP SUNU Santé. Chaque module est organisé par domaine fonctionnel pour faciliter l'utilisation et la maintenance.

## 📋 Modules disponibles

### 🔐 **01_Auth_Module** - Authentification
- Inscription (clients physiques/moraux, prestataires)
- Vérification OTP
- Connexion/Déconnexion
- Gestion des mots de passe
- Refresh token

### 📝 **02_DemandesAdhesion_Module** - Demandes d'adhésion
- Soumission de demandes d'adhésion
- Validation par techniciens/médecins
- Suivi des statuts
- Gestion des questionnaires

### 🏢 **03_Entreprise_Module** - Gestion entreprise
- Invitations employés
- Soumission groupée
- Dashboard entreprise
- Gestion des bénéficiaires

### 🏥 **04_Prestataires_Module** - Prestataires de soins
- Validation prestataires
- Gestion des sinistres
- Facturation
- Réseau d'assignation

### 📄 **05_Contrats_Module** - Contrats
- Création de contrats
- Propositions personnalisées
- Gestion des garanties
- Acceptation/refus

### 💰 **06_Factures_Module** - Factures & Remboursements
- Workflow de validation (3 étapes)
- Génération PDF
- Suivi des remboursements
- Autorisations financières

### ❓ **07_Questions_Module** - Questionnaires dynamiques
- Questions adaptées par destinataire
- Types de données variés
- Validation conditionnelle
- Gestion des réponses

### 🛡️ **08_Garanties_Module** - Garanties
- Catégories de garanties
- Montants et pourcentages
- Association garanties-contrats
- Configuration des couvertures

### 🔔 **09_Notifications_Module** - Notifications
- Notifications temps réel
- Emails automatiques
- Gestion des statuts
- Notifications par rôle

### 📊 **10_Statistiques_Module** - Statistiques
- Dashboard général
- Métriques par rôle
- Rapports de performance
- Indicateurs clés

### 👑 **11_Admin_Module** - Administration
- Gestion des utilisateurs
- Configuration système
- Monitoring
- Paramètres globaux

### 🔧 **12_Technicien_Module** - Techniciens
- Analyse des demandes
- Propositions de contrats
- Validation technique
- Gestion des réseaux

### 💼 **13_Comptable_Module** - Comptables
- Validation financière
- Autorisation remboursements
- Suivi des paiements
- Rapports comptables

### 🎯 **14_Commercial_Module** - Commerciaux
- Prospection clients
- Codes parrainage
- Suivi des performances
- Gestion des prospects

### 👥 **15_Gestionnaire_Module** - Gestionnaires RH
- Gestion du personnel
- Affectations
- Suivi des équipes
- Ressources humaines

### 🏥 **16_Assures_Module** - Assurés
- Profil assuré
- Historique des soins
- Suivi des remboursements
- Documents personnels

### 📁 **17_Downloads_Module** - Téléchargements
- Documents PDF
- Factures
- Contrats
- Justificatifs

### 🔗 **18_ClientPrestataires_Module** - Relations clients-prestataires
- Assignation réseaux
- Suivi des soins
- Historique des interactions
- Gestion des partenariats

### 🎯 **19_Commercial_Module** - Système de parrainage commercial ⭐ **MIS À JOUR**

**Nouvelles fonctionnalités de gestion des codes :**
- **Durée contrôlée** : Chaque code parrainage est valide pendant exactement 1 an
- **Un seul code actif** : Un commercial ne peut avoir qu'un seul code actif à la fois
- **Historique complet** : Consultation de tous les codes précédents avec leurs statuts
- **Renouvellement contrôlé** : Nouveau code seulement après expiration du précédent
- **Consultation du code actuel** : Voir le code avec sa date d'expiration et jours restants

**Endpoints disponibles :**
- `POST /generer-code-parrainage` - Génération avec restrictions
- `GET /mon-code-parrainage` - Voir le code actuel
- `GET /historique-codes-parrainage` - Historique complet
- `POST /renouveler-code-parrainage` - Renouvellement après expiration
- `POST /creer-compte-client` - Création avec code actuel automatique
- `GET /mes-clients-parraines` - Suivi des clients
- `GET /mes-statistiques` - Statistiques commerciales

**Règles métier :**
- Si un commercial essaie de générer un nouveau code alors qu'il en a un actif, le système lui renvoie le code actuel avec sa date d'expiration
- Tous les codes précédents sont conservés dans l'historique
- Les statuts incluent : Actif, Expiré, Renouvelé
- Messages d'erreur informatifs avec détails sur les restrictions

### 🩺 **20_Medecin_Controleur_Module** - Médecin Contrôleur ⭐ **NOUVEAU**

**Gestion des questions pour prestataires :**
- Création de questions en masse (optimisé)
- Types de données variés (text, select, checkbox, radio, etc.)
- Questions obligatoires/optionnelles
- Statistiques des questions

**Gestion des garanties médicales :**
- Création et modification de garanties
- Définition des montants maximum
- Activation/Désactivation des garanties
- Organisation par catégories

**Validation des prestataires :**
- Validation des demandes d'adhésion prestataires
- Vérification des qualifications médicales
- Rejet avec motif si non conforme
- Création automatique du compte prestataire

**Validation des factures :**
- Validation médicale des factures (2ème étape)
- Vérification de la conformité des actes médicaux
- Rejet avec motif médical
- Notification automatique au comptable

**Endpoints disponibles :**
- `GET/POST/PUT/DELETE /questions` - Gestion des questions
- `GET/POST/PUT/DELETE /garanties` - Gestion des garanties
- `GET/POST/PUT/DELETE /categories-garanties` - Gestion des catégories
- `PUT /demandes-adhesions/{id}/valider-prestataire` - Validation prestataire
- `POST /factures/{id}/validate-medecin` - Validation facture
- `GET /questions/stats` - Statistiques

## 🚀 Installation et utilisation

### 1. Import des collections

#### Méthode 1 : Import individuel
1. Ouvrez Postman
2. Cliquez sur **Import**
3. Sélectionnez chaque fichier `.json` individuellement
4. Répétez pour tous les 20 modules

#### Méthode 2 : Import en lot
1. Ouvrez Postman
2. Cliquez sur **Import**
3. Sélectionnez le dossier `.documentation_postman`
4. Postman importera automatiquement tous les fichiers

#### Méthode 3 : Import par URL
1. Ouvrez Postman
2. Cliquez sur **Import**
3. Collez l'URL du fichier JSON
4. Cliquez sur **Continue** puis **Import**

## ⚙️ Configuration des variables

### Variables globales à configurer :
- `base_url` : URL de base de votre API (ex: `http://localhost:8000/api`)
- `api_key` : **CLÉ API OBLIGATOIRE** pour toutes les requêtes (header `X-API-Key`)
- `access_token` : Token JWT pour l'authentification (généré après connexion)

### Variables spécifiques :
- `token` : Token d'invitation pour les formulaires employés
- `filename` : Nom de fichier pour les téléchargements

## 🔑 Authentification

### ⚠️ **IMPORTANT : Clé API obligatoire**
**TOUTES les routes sont protégées par une clé API !**

1. **Configuration de la clé API** : Ajoutez le header `X-API-Key` avec votre clé API à **TOUTES** les requêtes
2. **Inscription** : Utilisez l'endpoint `/v1/auth/register` (avec clé API)
3. **Vérification OTP** : Utilisez `/v1/auth/verify-otp` (avec clé API)
4. **Connexion** : Utilisez `/v1/auth/login` (avec clé API)
5. **Récupération du token** : Le token JWT sera retourné dans la réponse de connexion
6. **Configuration du token** : Copiez le token et configurez la variable `access_token`

### 🔐 Double authentification :
- **Clé API** : Header `X-API-Key` (obligatoire pour toutes les requêtes)
- **Token JWT** : Header `Authorization: Bearer {token}` (pour les routes protégées par rôle)

## 📝 Rôles et permissions

### Rôles disponibles :
- `admin_global` : Super administrateur
- `gestionnaire` : Gestion RH du personnel
- `technicien` : Analyse des demandes, propose des contrats
- `medecin_controleur` : Valide les prestataires, contrôle les actes médicaux
- `commercial` : Prospection clients, codes parrainage ⭐ **NOUVEAU**
- `comptable` : Gestion financière, validation remboursements
- `client` : Clients physiques et moraux
- `prestataire` : Centres de soins

### Permissions par rôle :
- **Commercial** : Peut créer des comptes clients, générer des codes parrainage, voir ses statistiques
- **Client** : Peut s'inscrire avec ou sans code parrainage
- **Technicien** : Peut valider les demandes d'adhésion
- **Médecin Contrôleur** : Peut valider les prestataires et les actes médicaux
- **Comptable** : Peut autoriser les remboursements

## 🎯 **Nouveau : Système de parrainage commercial**

### Fonctionnalités ajoutées :

#### **Pour les commerciaux :**
1. **Génération de code parrainage** : `POST /v1/commercial/generer-code-parrainage`
2. **Création de comptes clients** : `POST /v1/commercial/creer-compte-client`
3. **Suivi des clients parrainés** : `GET /v1/commercial/mes-clients-parraines`
4. **Statistiques commerciales** : `GET /v1/commercial/mes-statistiques`

#### **Pour les clients :**
1. **Inscription avec code parrainage** : `POST /v1/auth/register` (champ `code_parrainage` optionnel)
2. **Liaison automatique** au commercial si code valide
3. **Email automatique** avec informations de connexion (si compte créé par commercial)

### Flux d'utilisation :

#### **Scénario 1 : Commercial crée le compte**
```
Commercial se connecte → Génère code parrainage → Crée compte client → 
Mot de passe généré automatiquement → Email envoyé au client → 
Client lié au commercial
```

#### **Scénario 2 : Client s'inscrit lui-même**
```
Client s'inscrit → Saisit code parrainage (optionnel) → 
Si code valide : client lié au commercial → 
Si pas de code : inscription normale
```

## 🔧 Configuration de l'environnement

### Variables d'environnement requises :
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
OTP_EXPIRED_AT=10
JWT_SECRET=your_jwt_secret
```

### Base de données :
- Les migrations incluent les nouveaux champs de parrainage
- Un seeder génère automatiquement les codes parrainage des commerciaux existants

## 📞 Support

Pour toute question ou problème :
- 📧 Email : support@sunusante.com
- 📱 Téléphone : +225 XX XX XX XX
- 💬 Chat en ligne disponible sur notre site

## 📄 Licence

© 2025 SUNU Santé. Tous droits réservés.

---

**Note** : Cette documentation est mise à jour régulièrement. Vérifiez toujours la version la plus récente.
