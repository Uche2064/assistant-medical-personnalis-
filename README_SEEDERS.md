# Seeders pour les Contrats, Catégories de Garanties et Garanties

## Vue d'ensemble

Ce document décrit les seeders créés pour peupler la base de données avec des données de test réalistes pour le système SUNU Santé.

## Seeders créés

### 1. CategorieGarantieSeeder

**Fichier :** `database/seeders/CategorieGarantieSeeder.php`

**Créateur :** Médecin Contrôleur

**Données créées :** 8 catégories de garanties

- **Santé** : Soins de santé, consultations médicales, hospitalisation
- **Pharmacie** : Médicaments, ordonnances, produits pharmaceutiques
- **Laboratoire** : Analyses médicales, tests de diagnostic
- **Optique** : Soins ophtalmologiques, lunettes, lentilles
- **Dentaire** : Soins dentaires, consultations odontologiques
- **Maternité** : Grossesse, accouchement, soins post-natals
- **Urgence** : Soins d'urgence, ambulance, interventions
- **Prévention** : Examens de prévention, vaccinations, bilans

### 2. GarantieSeeder

**Fichier :** `database/seeders/GarantieSeeder.php`

**Créateur :** Médecin Contrôleur

**Données créées :** 25 garanties réparties par catégorie

#### Garanties Santé (4 garanties)
- Consultation médecin généraliste : 15,000 FCFA (80% couverture)
- Consultation spécialiste : 25,000 FCFA (80% couverture)
- Hospitalisation : 500,000 FCFA (85% couverture)
- Chirurgie : 1,000,000 FCFA (90% couverture)

#### Garanties Pharmacie (3 garanties)
- Médicaments génériques : 50,000 FCFA (70% couverture)
- Médicaments spécialisés : 100,000 FCFA (75% couverture)
- Produits pharmaceutiques : 30,000 FCFA (65% couverture)

#### Garanties Laboratoire (3 garanties)
- Analyses sanguines : 75,000 FCFA (80% couverture)
- Examens radiologiques : 120,000 FCFA (85% couverture)
- Tests de diagnostic : 100,000 FCFA (80% couverture)

#### Garanties Optique (3 garanties)
- Consultation ophtalmologue : 20,000 FCFA (75% couverture)
- Lunettes de vue : 80,000 FCFA (70% couverture)
- Lentilles de contact : 40,000 FCFA (65% couverture)

#### Garanties Dentaire (4 garanties)
- Consultation dentaire : 25,000 FCFA (75% couverture)
- Détartrage : 15,000 FCFA (80% couverture)
- Soins conservateurs : 60,000 FCFA (70% couverture)
- Prothèse dentaire : 200,000 FCFA (60% couverture)

#### Garanties Maternité (3 garanties)
- Suivi de grossesse : 150,000 FCFA (85% couverture)
- Accouchement : 300,000 FCFA (90% couverture)
- Soins post-natals : 100,000 FCFA (80% couverture)

#### Garanties Urgence (3 garanties)
- Ambulance : 50,000 FCFA (100% couverture)
- Soins d'urgence : 200,000 FCFA (95% couverture)
- Réanimation : 500,000 FCFA (100% couverture)

#### Garanties Prévention (3 garanties)
- Bilan de santé : 100,000 FCFA (80% couverture)
- Vaccinations : 50,000 FCFA (90% couverture)
- Dépistage : 75,000 FCFA (85% couverture)

### 3. ContratSeeder

**Fichier :** `database/seeders/ContratSeeder.php`

**Créateur :** Technicien

**Données créées :** 4 contrats avec catégories assignées

#### Contrat BASIC
- **Prime :** 25,000 FCFA (25€)
- **Catégories assignées :** Santé, Pharmacie
- **Couverture :** 70%
- **Description :** Contrat de base avec couverture minimale

#### Contrat STANDARD
- **Prime :** 45,000 FCFA (45€)
- **Catégories assignées :** Santé, Pharmacie, Laboratoire, Optique
- **Couverture :** 80%
- **Description :** Contrat standard avec couverture complète

#### Contrat PREMIUM
- **Prime :** 75,000 FCFA (75€)
- **Catégories assignées :** Santé, Pharmacie, Laboratoire, Optique, Dentaire, Maternité, Urgence
- **Couverture :** 90%
- **Description :** Contrat premium avec couverture étendue

#### Contrat TEAM
- **Prime :** 120,000 FCFA (120€)
- **Catégories assignées :** Toutes les catégories
- **Couverture :** 95%
- **Description :** Contrat pour équipes/entreprises

## Utilisation

### Exécution complète
```bash
php artisan db:seed
```

### Exécution des données de test uniquement
```bash
php artisan db:seed --class=TestDataSeeder
```

### Exécution individuelle
```bash
php artisan db:seed --class=CategorieGarantieSeeder
php artisan db:seed --class=GarantieSeeder
php artisan db:seed --class=ContratSeeder
```

## Structure des données

### Conversion des prix
Les prix sont convertis en FCFA (Franc CFA) :
- 1€ ≈ 1,000 FCFA
- 1$ ≈ 900 FCFA

### Taux de couverture par type de contrat
- **BASIC :** 70%
- **STANDARD :** 80%
- **PREMIUM :** 90%
- **TEAM :** 95%

### Assignation automatique des catégories
Chaque contrat a des catégories de garanties assignées par défaut selon son niveau :
- **BASIC :** Santé + Pharmacie
- **STANDARD :** Santé + Pharmacie + Laboratoire + Optique
- **PREMIUM :** Toutes sauf Prévention
- **TEAM :** Toutes les catégories

### Attribution des créateurs
- **Médecin Contrôleur :** Crée les catégories de garanties et les garanties
- **Technicien :** Crée les contrats et les assigne aux catégories

## Fichiers modifiés

- `database/seeders/DatabaseSeeder.php` : Ajout des nouveaux seeders
- `database/seeders/ContratSeeder.php` : Mise à jour complète avec attribution technicien
- `database/seeders/CategorieGarantieSeeder.php` : Nouveau fichier avec attribution médecin contrôleur
- `database/seeders/GarantieSeeder.php` : Nouveau fichier avec attribution médecin contrôleur
- `database/seeders/TestDataSeeder.php` : Nouveau fichier pour tests

## Notes importantes

1. **Ordre d'exécution :** Les seeders doivent être exécutés dans l'ordre car les garanties dépendent des catégories, et les contrats dépendent des catégories.

2. **Données réalistes :** Tous les prix et taux de couverture sont basés sur des standards du marché de la santé en Afrique de l'Ouest.

3. **Flexibilité :** Les données peuvent être facilement modifiées dans les seeders pour s'adapter aux besoins spécifiques.

4. **Tests :** Utilisez `TestDataSeeder` pour tester rapidement les données sans affecter les autres seeders.

5. **Créateurs :** Chaque élément a un créateur approprié assigné selon les rôles définis dans le système.

## Dépendances

Les seeders nécessitent que les personnels suivants existent :
- **Médecin Contrôleur** (pour créer catégories et garanties)
- **Technicien** (pour créer les contrats)

Assurez-vous que `PersonnelSeeder` a été exécuté avant ces seeders. 