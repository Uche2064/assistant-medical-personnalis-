# Migrations et Enums - Mises à Jour

## Enums Supprimés

### ❌ **Enums Supprimés (4) :**
1. `TypePersonneEnum.php` - Remplacé par `TypeDemandeurEnum` et `TypePrestataireEnum`
2. `StatutValidationEnum.php` - Remplacé par `StatutDemandeAdhesionEnum`
3. `LienEnum.php` - Remplacé par `LienParenteEnum`
4. `TypePersonnelEnum.php` - Fonctionnalité intégrée dans `RoleEnum`

### ✅ **Enums Conservés et Mis à Jour (10) :**
1. `RoleEnum.php` - Rôles utilisateurs
2. `TypeClientEnum.php` - Types de clients
3. `SexeEnum.php` - Sexe
4. `TypeDonneeEnum.php` - Types de données
5. `EmailType.php` - Types d'emails

### 🆕 **Enums Nouveaux (11) :**
1. `TypeContratEnum.php` - Types de contrats
2. `StatutContratEnum.php` - Statuts des contrats
3. `StatutDemandeAdhesionEnum.php` - Statuts des demandes d'adhésion
4. `TypeDemandeurEnum.php` - Types de demandeurs
5. `TypePrestataireEnum.php` - Types de prestataires
6. `StatutPrestataireEnum.php` - Statuts des prestataires
7. `StatutFactureEnum.php` - Statuts des factures (mis à jour)
8. `StatutSinistreEnum.php` - Statuts des sinistres
9. `StatutAssureEnum.php` - Statuts des assurés
10. `LienParenteEnum.php` - Liens de parenté
11. `StatutClientEnum.php` - Statuts des clients

## Migrations Mises à Jour

### 📝 **Migrations Modifiées (8) :**

#### 1. **contrats_table.php**
```php
// AVANT
$table->enum('type_contrat', ['basic', 'standard', 'premium', 'team']);
$table->enum('statut', ['propose', 'accepte', 'refuse', 'actif', 'expire', 'resilie']);

// APRÈS
$table->string('type_contrat'); // Casté vers TypeContratEnum
$table->string('statut'); // Casté vers StatutContratEnum
```

#### 2. **demandes_adhesions_table.php**
```php
// AVANT
$table->enum('type_demandeur', ['physique', 'centre_de_soins', ...]);
$table->enum('statut', ['en_attente', 'validee', 'rejetee']);

// APRÈS
$table->string('type_demandeur'); // Casté vers TypeDemandeurEnum
$table->string('statut'); // Casté vers StatutDemandeAdhesionEnum
```

#### 3. **prestataires_table.php**
```php
// AVANT
$table->enum('type_prestataire', ['centre_de_soins', ...]);
$table->enum('statut', ['en_attente', 'valide', 'rejete', 'suspendu']);

// APRÈS
$table->string('type_prestataire'); // Casté vers TypePrestataireEnum
$table->string('statut'); // Casté vers StatutPrestataireEnum
```

#### 4. **assures_table.php**
```php
// AVANT
$table->enum('lien_parente', ['conjoint', 'enfant', 'parent', 'autre']);
$table->enum('statut', ['actif', 'inactif', 'suspendu']);

// APRÈS
$table->string('lien_parente'); // Casté vers LienParenteEnum
$table->string('statut'); // Casté vers StatutAssureEnum
```

#### 5. **sinistres_table.php**
```php
// AVANT
$table->enum('statut', ['declare', 'en_cours', 'traite', 'cloture']);

// APRÈS
$table->string('statut'); // Casté vers StatutSinistreEnum
```

#### 6. **factures_table.php**
```php
// AVANT
$table->enum('statut', ['en_attente', 'validee_technicien', ...]);

// APRÈS
$table->string('statut'); // Casté vers StatutFactureEnum
```

#### 7. **questions_table.php**
```php
// AVANT
$table->enum('type_donnee', ['text', 'number', 'boolean', 'date', 'file', 'select']);
$table->enum('destinataire', ['physique', 'centre_de_soins', ...]);

// APRÈS
$table->string('type_donnee'); // Casté vers TypeDonneeEnum
$table->string('destinataire'); // Casté vers TypeDemandeurEnum
```

#### 8. **clients_table.php**
```php
// AVANT
$table->enum('type_client', ['physique', 'moral']);
$table->enum('statut', ['prospect', 'client', 'assure']);

// APRÈS
$table->string('type_client'); // Casté vers TypeClientEnum
$table->string('statut'); // Casté vers StatutClientEnum
```

## Modèles Mis à Jour

### 🔧 **Modèles Modifiés (2) :**

#### 1. **Contrat.php**
```php
protected $casts = [
    'type_contrat' => \App\Enums\TypeContratEnum::class,
    'statut' => \App\Enums\StatutContratEnum::class,
];
```

#### 2. **Client.php**
```php
protected $casts = [
    'type_client' => \App\Enums\TypeClientEnum::class,
    'statut' => \App\Enums\StatutClientEnum::class,
];
```

#### 3. **Assure.php**
```php
protected $casts = [
    'lien_parente' => \App\Enums\LienParenteEnum::class,
    'statut' => \App\Enums\StatutAssureEnum::class,
];
```

## Avantages Obtenus

### 🎯 **Type Safety**
- Vérification stricte des types à la compilation
- Évite les erreurs de frappe dans les valeurs

### 🧹 **Maintenabilité**
- Centralisation des valeurs possibles
- Facile à modifier et étendre

### 🎨 **UI/UX**
- Méthodes `getLabel()` pour l'affichage en français
- Méthodes `getColor()` pour les couleurs d'interface
- Méthodes `getStep()` pour les workflows

### 🔍 **IntelliSense**
- Autocomplétion dans l'IDE
- Documentation intégrée

## Prochaines Étapes

1. **Mettre à jour les autres modèles** avec les casts appropriés
2. **Adapter les seeders** pour utiliser les enums
3. **Mettre à jour les contrôleurs** pour la validation
4. **Créer les tests** pour les enums
5. **Tester les migrations** avec la nouvelle structure 