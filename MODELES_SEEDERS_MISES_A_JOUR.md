# Modèles et Seeders - Mises à Jour

## Modèles Mis à Jour

### 🔧 **Modèles Modifiés (8) :**

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

#### 4. **DemandeAdhesion.php**
```php
protected $casts = [
    'statut' => \App\Enums\StatutDemandeAdhesionEnum::class,
    'type_demandeur' => \App\Enums\TypeDemandeurEnum::class,
];
```

#### 5. **Prestataire.php**
```php
protected $casts = [
    'type_prestataire' => \App\Enums\TypePrestataireEnum::class,
    'statut' => \App\Enums\StatutPrestataireEnum::class,
];
```

#### 6. **Sinistre.php**
```php
protected $casts = [
    'statut' => \App\Enums\StatutSinistreEnum::class,
];
```

#### 7. **Facture.php**
```php
protected $casts = [
    'statut' => \App\Enums\StatutFactureEnum::class,
];
```

#### 8. **Question.php**
```php
protected $casts = [
    'type_donnee' => \App\Enums\TypeDonneeEnum::class,
    'destinataire' => \App\Enums\TypeDemandeurEnum::class,
];
```

## Méthodes Mises à Jour

### 🎯 **Méthodes de Statut (Utilisation des Enums) :**

#### **Contrat.php**
- `isProposed()` → `StatutContratEnum::PROPOSE`
- `isAccepted()` → `StatutContratEnum::ACCEPTE`
- `isRefused()` → `StatutContratEnum::REFUSE`
- `isActive()` → `StatutContratEnum::ACTIF`
- `isExpired()` → `StatutContratEnum::EXPIRE`
- `isCancelled()` → `StatutContratEnum::RESILIE`

#### **Client.php**
- `isProspect()` → `StatutClientEnum::PROSPECT`
- `isClient()` → `StatutClientEnum::CLIENT`
- `isAssure()` → `StatutClientEnum::ASSURE`
- `isPhysique()` → `TypeClientEnum::PHYSIQUE`
- `isMoral()` → `TypeClientEnum::MORAL`

#### **DemandeAdhesion.php**
- `isPending()` → `StatutDemandeAdhesionEnum::EN_ATTENTE`
- `isValidated()` → `StatutDemandeAdhesionEnum::VALIDEE`
- `isRejected()` → `StatutDemandeAdhesionEnum::REJETEE`

#### **Prestataire.php**
- `isPending()` → `StatutPrestataireEnum::EN_ATTENTE`
- `isValidated()` → `StatutPrestataireEnum::VALIDE`
- `isRejected()` → `StatutPrestataireEnum::REJETE`
- `isSuspended()` → `StatutPrestataireEnum::SUSPENDU`

#### **Sinistre.php**
- `isDeclared()` → `StatutSinistreEnum::DECLARE`
- `isInProgress()` → `StatutSinistreEnum::EN_COURS`
- `isTreated()` → `StatutSinistreEnum::TRAITE`
- `isClosed()` → `StatutSinistreEnum::CLOTURE`

#### **Facture.php**
- `isPending()` → `StatutFactureEnum::EN_ATTENTE`
- `isValidatedByTechnicien()` → `StatutFactureEnum::VALIDEE_TECHNICIEN`
- `isValidatedByMedecin()` → `StatutFactureEnum::VALIDEE_MEDECIN`
- `isAuthorizedByComptable()` → `StatutFactureEnum::AUTORISEE_COMPTABLE`
- `isReimbursed()` → `StatutFactureEnum::REMBOURSEE`
- `isRejected()` → `StatutFactureEnum::REJETEE`

## Accesseurs Simplifiés

### 🎨 **Accesseurs Utilisant les Enums :**

#### **DemandeAdhesion.php**
```php
public function getTypeDemandeurFrancaisAttribute()
{
    return $this->type_demandeur->getLabel();
}
```

#### **Prestataire.php**
```php
public function getTypeFrancaisAttribute()
{
    return $this->type_prestataire->getLabel();
}
```

#### **Facture.php**
```php
public function getStatutFrancaisAttribute()
{
    return $this->statut->getLabel();
}
```

#### **Question.php**
```php
public function getTypeDonneeFrancaisAttribute()
{
    return $this->type_donnee->getLabel();
}

public function getDestinataireFrancaisAttribute()
{
    return $this->destinataire->getLabel();
}
```

## Seeders Mis à Jour

### 🌱 **Seeders Modifiés (3) :**

#### 1. **ProspectPhysiqueMedicalQuestionSeeder.php**
```php
// AVANT
use App\Enums\TypePersonneEnum;

// APRÈS
use App\Enums\TypeDemandeurEnum;

// Remplacé toutes les occurrences
'destinataire' => TypeDemandeurEnum::PHYSIQUE
```

#### 2. **PrestataireMedicalQuestionSeeder.php**
```php
// AVANT
use App\Enums\TypePersonneEnum;

// APRÈS
use App\Enums\TypeDemandeurEnum;

// Remplacé toutes les occurrences
'destinataire' => TypeDemandeurEnum::PHARMACIE
'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS
'destinataire' => TypeDemandeurEnum::OPTIQUE
'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC
```

#### 3. **ContratSeeder.php**
```php
// AVANT
'type_contrat' => TypeContratEnum::BASCI,

// APRÈS
'type_contrat' => TypeContratEnum::BASIC,
```

## Enums Mis à Jour

### 🔧 **Enums Améliorés (1) :**

#### **TypeDonneeEnum.php**
```php
// Ajout de la méthode getLabel()
public function getLabel(): string
{
    return match($this) {
        self::TEXT => 'Texte',
        self::NUMBER => 'Nombre',
        self::BOOLEAN => 'Oui/Non',
        self::DATE => 'Date',
        self::SELECT => 'Sélection',
        self::CHECKBOX => 'Case à cocher',
        self::RADIO => 'Bouton radio',
        self::FILE => 'Fichier',
    };
}
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

### 🚀 **Performance**
- Moins de code répétitif
- Accesseurs simplifiés
- Validation automatique

## Prochaines Étapes

1. **Tester les migrations** avec la nouvelle structure
2. **Mettre à jour les contrôleurs** pour la validation
3. **Créer les tests** pour les enums et modèles
4. **Documenter l'API** avec les nouvelles structures
5. **Implémenter les workflows** métier 