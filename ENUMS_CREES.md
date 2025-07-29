# Enums Créés - SUNU Santé

## Vue d'ensemble

Tous les enums PHP 8.1+ ont été créés pour remplacer les champs enum dans les migrations, offrant une meilleure type safety et maintenabilité.

## Enums Principaux

### 1. **TypeContratEnum** - Types de contrats
- `BASIC` - Contrat de base
- `STANDARD` - Contrat standard
- `PREMIUM` - Contrat premium
- `TEAM` - Contrat pour équipes
- **Méthodes** : `getLabel()`, `getDescription()`

### 2. **StatutContratEnum** - Statuts des contrats
- `PROPOSE` - Proposé
- `ACCEPTE` - Accepté
- `REFUSE` - Refusé
- `ACTIF` - Actif
- `EXPIRE` - Expiré
- `RESILIE` - Résilié
- **Méthodes** : `getLabel()`, `getColor()`

### 3. **StatutDemandeAdhesionEnum** - Statuts des demandes d'adhésion
- `EN_ATTENTE` - En Attente
- `VALIDEE` - Validée
- `REJETEE` - Rejetée
- **Méthodes** : `getLabel()`, `getColor()`

### 4. **TypeDemandeurEnum** - Types de demandeurs
- `PHYSIQUE` - Client Physique
- `CENTRE_DE_SOINS` - Centre de Soins
- `LABORATOIRE_CENTRE_DIAGNOSTIC` - Laboratoire/Centre de Diagnostic
- `MEDECIN_LIBERAL` - Médecin Libéral
- `PHARMACIE` - Pharmacie
- `OPTIQUE` - Optique
- `AUTRE` - Autre
- **Méthodes** : `getLabel()`

### 5. **TypePrestataireEnum** - Types de prestataires
- `CENTRE_DE_SOINS` - Centre de Soins
- `LABORATOIRE_CENTRE_DIAGNOSTIC` - Laboratoire/Centre de Diagnostic
- `MEDECIN_LIBERAL` - Médecin Libéral
- `PHARMACIE` - Pharmacie
- `OPTIQUE` - Optique
- **Méthodes** : `getLabel()`

### 6. **StatutPrestataireEnum** - Statuts des prestataires
- `EN_ATTENTE` - En Attente
- `VALIDE` - Validé
- `REJETE` - Rejeté
- `SUSPENDU` - Suspendu
- **Méthodes** : `getLabel()`, `getColor()`

### 7. **StatutFactureEnum** - Statuts des factures
- `EN_ATTENTE` - En Attente
- `VALIDEE_TECHNICIEN` - Validée par Technicien
- `VALIDEE_MEDECIN` - Validée par Médecin
- `AUTORISEE_COMPTABLE` - Autorisée par Comptable
- `REMBOURSEE` - Remboursée
- `REJETEE` - Rejetée
- **Méthodes** : `getLabel()`, `getColor()`, `getStep()`

### 8. **StatutSinistreEnum** - Statuts des sinistres
- `DECLARE` - Déclaré
- `EN_COURS` - En Cours
- `TRAITE` - Traité
- `CLOTURE` - Clôturé
- **Méthodes** : `getLabel()`, `getColor()`

### 9. **StatutAssureEnum** - Statuts des assurés
- `ACTIF` - Actif
- `INACTIF` - Inactif
- `SUSPENDU` - Suspendu
- **Méthodes** : `getLabel()`, `getColor()`

### 10. **LienParenteEnum** - Liens de parenté
- `CONJOINT` - Conjoint(e)
- `ENFANT` - Enfant
- `PARENT` - Parent
- `AUTRE` - Autre
- **Méthodes** : `getLabel()`

## Enums Existants (Mis à jour)

### 11. **RoleEnum** - Rôles utilisateurs
- `ADMIN_GLOBAL` - Admin Global
- `TECHNICIEN` - Technicien
- `COMMERCIAL` - Commercial
- `MEDECIN_CONTROLEUR` - Médecin Contrôleur
- `COMPTABLE` - Comptable
- `GESTIONNAIRE` - Gestionnaire
- `USER` - Utilisateur

### 12. **TypeClientEnum** - Types de clients
- `PHYSIQUE` - Physique
- `MORAL` - Moral

### 13. **SexeEnum** - Sexe
- `M` - Masculin
- `F` - Féminin

### 14. **TypeDonneeEnum** - Types de données
- `TEXT` - Texte
- `NUMBER` - Nombre
- `BOOLEAN` - Booléen
- `DATE` - Date
- `FILE` - Fichier
- `SELECT` - Sélection

## Avantages des Enums PHP 8.1+

### 1. **Type Safety**
- Vérification stricte des types à la compilation
- Évite les erreurs de frappe dans les valeurs

### 2. **IntelliSense**
- Autocomplétion dans l'IDE
- Documentation intégrée

### 3. **Maintenabilité**
- Centralisation des valeurs possibles
- Facile à modifier et étendre

### 4. **Méthodes Utilitaires**
- `getLabel()` - Labels en français
- `getColor()` - Couleurs pour l'UI
- `getStep()` - Étapes pour les workflows

### 5. **Validation**
- Validation automatique des valeurs
- Pas de valeurs invalides possibles

## Utilisation dans les Modèles

```php
// Dans les casts
protected $casts = [
    'type_contrat' => \App\Enums\TypeContratEnum::class,
    'statut' => \App\Enums\StatutContratEnum::class,
];

// Dans les méthodes
public function isActive()
{
    return $this->statut === \App\Enums\StatutContratEnum::ACTIF;
}

// Dans les vues
{{ $contrat->type_contrat->getLabel() }}
{{ $contrat->statut->getColor() }}
```

## Prochaines Étapes

1. **Mettre à jour les migrations** pour utiliser les enums
2. **Mettre à jour tous les modèles** avec les casts appropriés
3. **Adapter les seeders** pour utiliser les enums
4. **Mettre à jour les contrôleurs** pour la validation
5. **Créer les tests** pour les enums 