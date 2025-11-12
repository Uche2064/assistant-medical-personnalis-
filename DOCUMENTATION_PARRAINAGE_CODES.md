# Documentation - Gestion des Codes de Parrainage Commerciaux

## Vue d'ensemble

Le système de parrainage commercial a été amélioré pour gérer la durée et le renouvellement des codes de parrainage. Chaque commercial ne peut avoir qu'un seul code de parrainage actif à la fois, et ce code a une durée de validité d'un an.

## Fonctionnalités

### 1. Génération de Code de Parrainage
- **Endpoint**: `POST /v1/commercial/generer-code-parrainage`
- **Description**: Génère un nouveau code de parrainage valide pour 1 an
- **Restriction**: Un commercial ne peut avoir qu'un seul code actif à la fois

#### Réponse en cas de code existant :
```json
{
    "success": false,
    "message": "Vous avez déjà un code de parrainage actif. Il expire le 06/10/2026 à 16:48",
    "data": {
        "code_actuel": "COM2MCMGZ",
        "date_expiration": "2026-10-06 16:48:16",
        "peut_renouveler": false,
        "jours_restants": 365
    }
}
```

### 2. Voir le Code Actuel
- **Endpoint**: `GET /v1/commercial/mon-code-parrainage`
- **Description**: Retourne le code de parrainage actuel avec ses informations

#### Réponse :
```json
{
    "success": true,
    "data": {
        "code_parrainage": {
            "id": 1,
            "code_parrainage": "COM2MCMGZ",
            "date_debut": "2025-10-06 16:48:16",
            "date_expiration": "2026-10-06 16:48:16",
            "est_actif": true,
            "est_renouvele": false,
            "est_expire": false,
            "peut_renouveler": false,
            "jours_restants": 365,
            "duree_totale": 365,
            "statut": "Actif",
            "statut_color": "success"
        }
    }
}
```

### 3. Historique des Codes
- **Endpoint**: `GET /v1/commercial/historique-codes-parrainage`
- **Description**: Retourne l'historique complet des codes de parrainage du commercial

#### Réponse :
```json
{
    "success": true,
    "data": {
        "codes": [
            {
                "id": 1,
                "code_parrainage": "COM2MCMGZ",
                "date_debut": "2025-10-06 16:48:16",
                "date_expiration": "2026-10-06 16:48:16",
                "est_actif": true,
                "est_renouvele": false,
                "est_expire": false,
                "peut_renouveler": false,
                "jours_restants": 365,
                "duree_totale": 365,
                "statut": "Actif",
                "statut_color": "success",
                "date_debut_formatee": "06/10/2025 à 16:48",
                "date_expiration_formatee": "06/10/2026 à 16:48"
            }
        ],
        "total": 1,
        "codes_actifs": 1,
        "codes_expires": 0
    }
}
```

### 4. Renouvellement de Code
- **Endpoint**: `POST /v1/commercial/renouveler-code-parrainage`
- **Description**: Renouvelle le code de parrainage après expiration
- **Restriction**: Ne peut être utilisé qu'après l'expiration du code actuel

#### Réponse :
```json
{
    "success": true,
    "data": {
        "nouveau_code": {
            "id": 2,
            "code_parrainage": "COM3XYZAB",
            "date_debut": "2025-10-06 17:00:00",
            "date_expiration": "2026-10-06 17:00:00",
            "est_actif": true,
            "est_renouvele": false,
            "est_expire": false,
            "peut_renouveler": false,
            "jours_restants": 365,
            "duree_totale": 365,
            "statut": "Actif",
            "statut_color": "success"
        },
        "ancien_code": {
            "id": 1,
            "code_parrainage": "COM2MCMGZ",
            "date_debut": "2025-10-06 16:48:16",
            "date_expiration": "2026-10-06 16:48:16",
            "est_actif": false,
            "est_renouvele": true,
            "est_expire": false,
            "peut_renouveler": false,
            "jours_restants": 0,
            "duree_totale": 365,
            "statut": "Renouvelé",
            "statut_color": "warning"
        }
    }
}
```

## Règles Métier

### Durée et Renouvellement
1. **Durée** : Chaque code de parrainage est valide pendant exactement 1 an
2. **Un seul code actif** : Un commercial ne peut avoir qu'un seul code de parrainage actif à la fois
3. **Renouvellement** : Un nouveau code ne peut être généré qu'après l'expiration du précédent
4. **Historique** : Tous les codes précédents sont conservés pour consultation

### Statuts des Codes
- **Actif** : Code valide et utilisable
- **Expiré** : Code dont la date d'expiration est passée
- **Renouvelé** : Ancien code remplacé par un nouveau
- **Inactif** : Code désactivé manuellement

### Compatibilité
- Le champ `code_parrainage_commercial` dans la table `users` est maintenu pour la compatibilité avec le code existant
- La création de comptes clients utilise le nouveau système de codes avec durée

## Migration des Données Existantes

Un seeder a été créé pour migrer les codes de parrainage existants vers le nouveau système :
- Les codes existants sont migrés avec une durée d'1 an à partir de la date de migration
- Tous les codes existants restent fonctionnels

## Base de Données

### Nouvelle Table : `commercial_parrainage_codes`
```sql
CREATE TABLE commercial_parrainage_codes (
    id BIGINT PRIMARY KEY,
    commercial_id BIGINT NOT NULL,
    code_parrainage VARCHAR(255) NOT NULL,
    date_debut TIMESTAMP NOT NULL,
    date_expiration TIMESTAMP NOT NULL,
    est_actif BOOLEAN DEFAULT true,
    est_renouvele BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (commercial_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_commercial_code (commercial_id, code_parrainage),
    INDEX idx_commercial_active (commercial_id, est_actif),
    INDEX idx_expiration (date_expiration)
);
```

## Utilisation dans le Frontend

### Scénarios d'utilisation :

1. **Premier code** : Le commercial génère son premier code
2. **Code actif** : Le commercial consulte son code actuel et sa date d'expiration
3. **Tentative de nouveau code** : Si le commercial essaie de générer un nouveau code, il reçoit son code actuel avec la date d'expiration
4. **Historique** : Le commercial peut voir tous ses codes précédents
5. **Renouvellement** : Après expiration, le commercial peut renouveler son code

### Messages d'erreur informatifs :
- Code actif existant avec date d'expiration
- Aucun code à renouveler
- Code expiré prêt pour renouvellement

