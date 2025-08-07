# Resources pour les Contrats - Documentation

## Vue d'ensemble

Ce document décrit les resources Laravel créées pour l'affichage personnalisé des contrats et catégories de garanties dans l'API SUNU Santé.

## Resources créées

### 1. ContratResource

**Fichier :** `app/Http/Resources/ContratResource.php`

**Objectif :** Transformer les données d'un contrat pour un affichage personnalisé et structuré.

#### Structure des données

```php
[
    'id' => 1,
    'type_contrat' => 'premium',
    'type_contrat_label' => 'Premium',
    'prime_standard' => 75000.00,
    'prime_standard_formatted' => '75 000 FCFA',
    'date_debut' => '2024-01-01',
    'date_fin' => '2024-12-31',
    'est_actif' => true,
    'est_actif_label' => 'Actif',
    'categories_garanties_standard' => [1, 2, 3, 4, 5, 6, 7],
    'created_at' => '2024-01-01 00:00:00',
    'updated_at' => '2024-01-01 00:00:00',
    
    // Relations
    'technicien' => [
        'id' => 1,
        'nom' => 'Technicien',
        'prenoms' => 'Tech',
        'email' => 'technicien1@gmail.com',
        'telephone' => '+2250123456789',
        'nom_complet' => 'Technicien Tech'
    ],
    
    'categories_garanties' => [
        [
            'id' => 1,
            'libelle' => 'sante',
            'libelle_formatted' => 'Sante',
            'description' => 'Garanties liées aux soins de santé',
            'couverture' => 90.00,
            'couverture_formatted' => '90%',
            'garanties' => [
                [
                    'id' => 1,
                    'libelle' => 'consultation medecin generaliste',
                    'libelle_formatted' => 'Consultation medecin generaliste',
                    'plafond' => 15000.00,
                    'plafond_formatted' => '15 000 FCFA',
                    'prix_standard' => 5000.00,
                    'prix_standard_formatted' => '5 000 FCFA',
                    'taux_couverture' => 80.00,
                    'taux_couverture_formatted' => '80%'
                ]
            ]
        ]
    ],
    
    // Statistiques calculées
    'statistiques' => [
        'nombre_categories' => 1,
        'nombre_garanties' => 1,
        'couverture_moyenne' => 90.00
    ],
    
    // Métadonnées
    'meta' => [
        'is_expired' => false,
        'is_active' => true,
        'days_until_expiry' => 365,
        'can_be_modified' => true
    ]
]
```

#### Fonctionnalités

1. **Formatage automatique** :
   - Prix formatés en FCFA avec espaces
   - Dates formatées en format lisible
   - Labels pour les types de contrat et statuts

2. **Relations conditionnelles** :
   - `technicien` : Chargé seulement si la relation existe
   - `categories_garanties` : Chargé seulement si la relation existe
   - `garanties` : Chargé seulement si la relation existe

3. **Statistiques calculées** :
   - Nombre de catégories
   - Nombre de garanties
   - Couverture moyenne

4. **Métadonnées** :
   - État d'expiration
   - État d'activation
   - Jours jusqu'à expiration
   - Possibilité de modification

### 2. ContratCollection

**Fichier :** `app/Http/Resources/ContratCollection.php`

**Objectif :** Transformer les collections de contrats avec des métadonnées supplémentaires.

#### Structure des données

```php
[
    'data' => [
        // Array de ContratResource
    ],
    'meta' => [
        'total' => 4,
        'actifs' => 3,
        'inactifs' => 1,
        'expires' => 0,
        'repartition_types' => [
            'basic' => 1,
            'standard' => 1,
            'premium' => 1,
            'team' => 1
        ],
        'repartition_prix' => [
            '0-25000' => 1,
            '25001-50000' => 1,
            '50001-75000' => 1,
            '75001+' => 1
        ],
        'prix_moyen' => 66250.00,
        'prix_min' => 25000.00,
        'prix_max' => 120000.00
    ]
]
```

#### Fonctionnalités

1. **Statistiques globales** :
   - Total de contrats
   - Répartition par statut (actifs/inactifs/expirés)
   - Répartition par type de contrat
   - Répartition par tranche de prix

2. **Métriques de prix** :
   - Prix moyen
   - Prix minimum
   - Prix maximum

### 3. CategorieGarantieResource

**Fichier :** `app/Http/Resources/CategorieGarantieResource.php`

**Objectif :** Transformer les données des catégories de garanties.

#### Structure des données

```php
[
    'id' => 1,
    'libelle' => 'sante',
    'libelle_formatted' => 'Sante',
    'description' => 'Garanties liées aux soins de santé',
    'created_at' => '2024-01-01 00:00:00',
    'updated_at' => '2024-01-01 00:00:00',
    
    // Relations
    'garanties' => [
        [
            'id' => 1,
            'libelle' => 'consultation medecin generaliste',
            'libelle_formatted' => 'Consultation medecin generaliste',
            'plafond' => 15000.00,
            'plafond_formatted' => '15 000 FCFA',
            'prix_standard' => 5000.00,
            'prix_standard_formatted' => '5 000 FCFA',
            'taux_couverture' => 80.00,
            'taux_couverture_formatted' => '80%',
            'created_at' => '2024-01-01 00:00:00'
        ]
    ],
    
    'medecin_controleur' => [
        'id' => 1,
        'nom' => 'Médecin',
        'prenoms' => 'Contrôleur',
        'nom_complet' => 'Médecin Contrôleur',
        'email' => 'medecin.controleur@gmail.com',
        'telephone' => '+2250123456789'
    ],
    
    // Statistiques
    'statistiques' => [
        'nombre_garanties' => 2,
        'plafond_moyen' => 20000.00,
        'prix_moyen' => 6500.00,
        'taux_couverture_moyen' => 80.00
    ]
]
```

## Utilisation dans le contrôleur

### ContratController

```php
// Lister les contrats
public function index(Request $request)
{
    $contrats = $query->latest()->paginate($perPage);
    
    return ApiResponse::success(
        new ContratCollection($contrats),
        'Liste des contrats récupérée avec succès'
    );
}

// Récupérer un contrat
public function show(string $id)
{
    $contrat = Contrat::with(['technicien', 'categoriesGaranties.garanties'])
        ->find($id);
    
    return ApiResponse::success(
        new ContratResource($contrat),
        'Contrat récupéré avec succès'
    );
}

// Créer un contrat
public function store(StoreContratFormRequest $request)
{
    // ... logique de création
    
    return ApiResponse::success(
        new ContratResource($contrat),
        'Contrat créé avec succès',
        201
    );
}

// Récupérer les catégories
public function getCategoriesGaranties()
{
    $categories = CategorieGarantie::with(['garanties', 'medecinControleur.user'])
        ->get();
    
    return ApiResponse::success(
        CategorieGarantieResource::collection($categories),
        'Catégories de garanties récupérées avec succès'
    );
}
```

## Avantages des Resources

### 1. **Séparation des préoccupations**
- La logique de transformation est séparée du contrôleur
- Réutilisabilité des transformations
- Maintenance facilitée

### 2. **Formatage automatique**
- Prix formatés en FCFA
- Dates formatées de manière cohérente
- Labels pour les enums

### 3. **Relations conditionnelles**
- Chargement optimisé des relations
- Évite les requêtes N+1
- Performance améliorée

### 4. **Métadonnées enrichies**
- Statistiques calculées automatiquement
- Informations contextuelles (expiration, modification)
- Données pour les graphiques et tableaux de bord

### 5. **Cohérence des données**
- Structure uniforme pour toutes les réponses
- Formatage cohérent des nombres et dates
- Labels standardisés

## Bonnes pratiques

### 1. **Chargement des relations**
```php
// Toujours charger les relations nécessaires
$contrat = Contrat::with(['technicien', 'categoriesGaranties.garanties'])->find($id);
```

### 2. **Utilisation des resources**
```php
// Pour un seul élément
return new ContratResource($contrat);

// Pour une collection
return new ContratCollection($contrats);

// Pour une collection simple
return ContratResource::collection($contrats);
```

### 3. **Optimisation des requêtes**
```php
// Éviter les requêtes N+1
$contrats = Contrat::with(['technicien', 'categoriesGaranties.garanties'])
    ->latest()
    ->paginate($perPage);
```

## Tests recommandés

1. **Test des resources** :
   - Vérifier le formatage des prix
   - Vérifier le formatage des dates
   - Vérifier les labels des enums

2. **Test des relations** :
   - Vérifier que les relations sont chargées correctement
   - Vérifier les données conditionnelles

3. **Test des statistiques** :
   - Vérifier les calculs de moyennes
   - Vérifier les comptages

4. **Test des métadonnées** :
   - Vérifier les calculs d'expiration
   - Vérifier les permissions de modification 