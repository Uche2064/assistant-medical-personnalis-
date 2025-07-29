# PROMPT POUR DESIGNER FRONTEND - SUNU SANTÉ

## CONTEXTE DU PROJET

SUNU Santé est un système complet de gestion d'assurance santé qui digitalise toute la chaîne de gestion, de la prospection à la facturation. Le système gère 9 rôles différents avec des interfaces spécifiques.

## SPÉCIFICATIONS TECHNIQUES

### TECHNOLOGIES REQUISES
- **Framework** : Vue.js 3 avec Composition API
- **UI Framework** : Vuetify 3
- **Routing** : Vue Router 4
- **State Management** : Pinia
- **HTTP Client** : Axios
- **Build Tool** : Vite
- **TypeScript** : Optionnel mais recommandé

### ARCHITECTURE REQUISE
```
src/
├── components/
│   ├── common/           # Composants réutilisables
│   ├── layout/           # Layouts et navigation
│   ├── forms/            # Formulaires dynamiques
│   └── role-specific/    # Composants par rôle
├── views/                # Pages par rôle
├── stores/               # Stores Pinia par module
├── services/             # Services API
├── utils/                # Utilitaires
├── types/                # Types TypeScript
└── assets/               # Ressources statiques
```

## RÔLES ET INTERFACES

### 1. ASSURÉ PRINCIPAL
**Couleur principale** : #1976D2 (Bleu)

**Dashboard** :
- Statistiques personnelles (consommation, remboursements)
- Actions rapides (déclarer sinistre, consulter centres)
- Notifications récentes
- Graphiques de consommation

**Fonctionnalités** :
- Gestion des bénéficiaires (ajout/suppression/modification)
- Consultation des centres de soins assignés
- Historique des remboursements
- Déclaration de sinistres
- Profil et paramètres
- Portefeuille santé

**Composants spécifiques** :
- `BeneficiaireCard.vue` - Carte d'un bénéficiaire
- `CentreSoinsMap.vue` - Carte des centres
- `SinistreForm.vue` - Formulaire de déclaration
- `ConsommationChart.vue` - Graphique de consommation

### 2. ENTREPRISE
**Couleur principale** : #424242 (Gris foncé)

**Dashboard** :
- Statistiques employés (nombre, adhésions, consommations)
- Actions rapides (générer lien, soumettre demande)
- Notifications employés
- Graphiques de coûts

**Fonctionnalités** :
- Gestion des liens d'adhésion (génération/expiration)
- Suivi des fiches employés
- Consultation des contrats
- Rapports financiers
- Gestion des employés

**Composants spécifiques** :
- `LienAdhesionGenerator.vue` - Générateur de liens
- `EmployeTable.vue` - Tableau des employés
- `ContratViewer.vue` - Visualiseur de contrats
- `RapportFinancier.vue` - Rapports financiers

### 3. COMMERCIAL
**Couleur principale** : #FF9800 (Orange)

**Dashboard** :
- Statistiques clients (prospects, conversions)
- Actions rapides (générer code, contacter prospect)
- Notifications conversions
- Graphiques de performance

**Fonctionnalités** :
- Gestion des codes de parrainage
- Suivi des prospects
- Historique des conversions
- Commission et paiements
- Rapports de performance

**Composants spécifiques** :
- `ProspectCard.vue` - Carte d'un prospect
- `CodeParrainageGenerator.vue` - Générateur de codes
- `ConversionChart.vue` - Graphique de conversions
- `CommissionTable.vue` - Tableau des commissions

### 4. TECHNICIEN
**Couleur principale** : #4CAF50 (Vert)

**Dashboard** :
- Statistiques demandes (en attente, validées, rejetées)
- Actions rapides (valider demande, proposer contrat)
- Notifications nouvelles demandes
- Graphiques de validation

**Fonctionnalités** :
- Validation des demandes d'adhésion
- Gestion des contrats
- Validation des factures
- Rapports d'analyse
- Propositions de contrats

**Composants spécifiques** :
- `DemandeAdhesionCard.vue` - Carte d'une demande
- `ContratProposer.vue` - Proposeur de contrats
- `FactureValidator.vue` - Validateur de factures
- `AnalyseRapport.vue` - Rapports d'analyse

### 5. MÉDECIN CONTRÔLEUR
**Couleur principale** : #E91E63 (Rose)

**Dashboard** :
- Statistiques prestataires (validations, rejets)
- Actions rapides (valider prestataire, contrôler facture)
- Notifications nouvelles demandes
- Graphiques de contrôle

**Fonctionnalités** :
- Validation des prestataires
- Contrôle des factures médicales
- Gestion des questionnaires
- Tarifs de référence
- Rapports médicaux

**Composants spécifiques** :
- `PrestataireValidator.vue` - Validateur de prestataires
- `FactureMedicaleViewer.vue` - Visualiseur de factures
- `QuestionnaireManager.vue` - Gestionnaire de questionnaires
- `TarifReference.vue` - Tarifs de référence

### 6. COMPTABLE
**Couleur principale** : #9C27B0 (Violet)

**Dashboard** :
- Statistiques financières (flux, remboursements)
- Actions rapides (valider remboursement, générer rapport)
- Notifications factures
- Graphiques financiers

**Fonctionnalités** :
- Validation des remboursements
- Suivi des flux financiers
- Rapports comptables
- Gestion des paiements
- Audit financier

**Composants spécifiques** :
- `RemboursementValidator.vue` - Validateur de remboursements
- `FluxFinancierChart.vue` - Graphique des flux
- `RapportComptable.vue` - Rapports comptables
- `AuditTable.vue` - Tableau d'audit

### 7. GESTIONNAIRE
**Couleur principale** : #607D8B (Bleu gris)

**Dashboard** :
- Statistiques RH (personnel, performances)
- Actions rapides (créer compte, gérer personnel)
- Notifications RH
- Graphiques de performance

**Fonctionnalités** :
- Gestion du personnel
- Création de comptes
- Suivi des performances
- Rapports RH
- Gestion des rôles

**Composants spécifiques** :
- `PersonnelManager.vue` - Gestionnaire de personnel
- `CompteCreator.vue` - Créateur de comptes
- `PerformanceChart.vue` - Graphique de performance
- `RoleManager.vue` - Gestionnaire de rôles

### 8. ADMIN GLOBAL
**Couleur principale** : #F44336 (Rouge)

**Dashboard** :
- Statistiques globales (système, utilisateurs)
- Actions rapides (gérer gestionnaires, configurer)
- Notifications système
- Graphiques globaux

**Fonctionnalités** :
- Gestion des gestionnaires
- Configuration système
- Logs et audit
- Rapports globaux
- Maintenance système

**Composants spécifiques** :
- `GestionnaireManager.vue` - Gestionnaire de gestionnaires
- `SystemConfig.vue` - Configuration système
- `AuditLog.vue` - Logs d'audit
- `GlobalRapport.vue` - Rapports globaux

### 9. PRESTATAIRE
**Couleur principale** : #795548 (Marron)

**Dashboard** :
- Statistiques prestations (assurés, factures)
- Actions rapides (générer facture, consulter assurés)
- Notifications factures
- Graphiques de prestations

**Fonctionnalités** :
- Liste des assurés assignés
- Génération de factures
- Suivi des remboursements
- Profil établissement
- Historique des actes

**Composants spécifiques** :
- `AssureList.vue` - Liste des assurés
- `FactureGenerator.vue` - Générateur de factures
- `RemboursementTracker.vue` - Suivi des remboursements
- `ProfilEtablissement.vue` - Profil établissement

## COMPOSANTS RÉUTILISABLES

### Navigation
- `Sidebar.vue` - Navigation latérale par rôle
- `TopBar.vue` - Barre supérieure avec notifications
- `Breadcrumb.vue` - Fil d'Ariane
- `RoleSwitcher.vue` - Changement de rôle (si applicable)

### Formulaires
- `DynamicForm.vue` - Formulaire dynamique basé sur JSON
- `FileUpload.vue` - Upload de fichiers avec drag & drop
- `SignaturePad.vue` - Pad de signature électronique
- `QuestionnaireForm.vue` - Formulaire de questionnaire

### Tableaux et Données
- `DataTable.vue` - Tableau de données avec filtres
- `Pagination.vue` - Pagination
- `FilterPanel.vue` - Panneau de filtres
- `ExportButtons.vue` - Boutons d'export (PDF/Excel)

### Notifications
- `NotificationToast.vue` - Toast de notification
- `NotificationCenter.vue` - Centre de notifications
- `NotificationBadge.vue` - Badge de notification

### Graphiques
- `LineChart.vue` - Graphique linéaire
- `BarChart.vue` - Graphique en barres
- `PieChart.vue` - Graphique circulaire
- `StatsCard.vue` - Carte de statistiques

## DESIGN SYSTEM

### Palette de Couleurs
```css
/* Couleurs principales par rôle */
:root {
  --assure-primary: #1976D2;
  --entreprise-primary: #424242;
  --commercial-primary: #FF9800;
  --technicien-primary: #4CAF50;
  --medecin-primary: #E91E63;
  --comptable-primary: #9C27B0;
  --gestionnaire-primary: #607D8B;
  --admin-primary: #F44336;
  --prestataire-primary: #795548;
  
  /* Couleurs communes */
  --success: #4CAF50;
  --warning: #FF9800;
  --error: #F44336;
  --info: #2196F3;
  --neutral: #9E9E9E;
}
```

### Typographie
- **Police principale** : Roboto
- **Titres** : 24px, 20px, 18px, 16px
- **Corps** : 14px
- **Petit texte** : 12px

### Espacement
- **Unité de base** : 8px
- **Marges** : 8px, 16px, 24px, 32px
- **Padding** : 8px, 16px, 24px, 32px

### Ombres
```css
--shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
--shadow-md: 0 4px 6px rgba(0,0,0,0.15);
--shadow-lg: 0 10px 15px rgba(0,0,0,0.20);
```

## FONCTIONNALITÉS SPÉCIALES

### Mode Sombre/Clair
- Toggle automatique selon les préférences système
- Persistance des préférences utilisateur
- Adaptation automatique des couleurs

### Notifications Temps Réel
- WebSocket avec Pusher
- Notifications push
- Centre de notifications
- Badges de notification

### Responsive Design
- Mobile-first approach
- Breakpoints : 320px, 768px, 1024px, 1440px
- Navigation adaptative
- Tableaux scrollables sur mobile

### Accessibilité
- Support des lecteurs d'écran
- Navigation au clavier
- Contraste suffisant
- Textes alternatifs

### Performance
- Lazy loading des composants
- Virtual scrolling pour grandes listes
- Cache des données
- Optimisation des images

## DÉLIVRABLES ATTENDUS

### 1. Maquettes Figma/Adobe XD
- Maquettes pour chaque rôle
- Composants réutilisables
- Responsive design
- Prototypes interactifs

### 2. Composants Vue.js
- Tous les composants listés
- Documentation des props et events
- Tests unitaires
- Storybook (optionnel)

### 3. Pages par Rôle
- Dashboard pour chaque rôle
- Pages de fonctionnalités
- Gestion d'erreurs
- États de chargement

### 4. Documentation Technique
- Guide d'installation
- Guide d'utilisation
- API documentation
- Architecture détaillée

### 5. Guide d'Utilisation
- Tutoriels par rôle
- Vidéos de démonstration
- FAQ
- Support utilisateur

## CRITÈRES DE VALIDATION

### Fonctionnels
- Toutes les fonctionnalités implémentées
- Workflows complets
- Validation des données
- Gestion d'erreurs

### Techniques
- Code propre et maintenable
- Performance optimisée
- Sécurité respectée
- Tests couverts

### UX/UI
- Interface intuitive
- Design cohérent
- Responsive design
- Accessibilité

### Qualité
- Documentation complète
- Code review
- Tests automatisés
- Déploiement automatisé

## TIMELINE SUGGÉRÉE

### Semaine 1 : Design et Architecture
- Maquettes Figma
- Architecture technique
- Design system
- Composants de base

### Semaine 2 : Développement Core
- Composants réutilisables
- Navigation et layout
- Authentification
- API integration

### Semaine 3 : Fonctionnalités par Rôle
- Dashboard par rôle
- Fonctionnalités spécifiques
- Tests et optimisation
- Documentation

## CONTACT ET SUPPORT

Pour toute question ou clarification :
- Réunions hebdomadaires de suivi
- Documentation technique détaillée
- Support technique continu
- Formation utilisateur finale

---

**Objectif** : Créer une interface moderne, intuitive et performante qui facilite l'utilisation du système SUNU Santé pour tous les utilisateurs, tout en respectant les contraintes techniques et les besoins métier spécifiques à chaque rôle. 