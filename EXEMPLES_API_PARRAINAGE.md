# Exemples d'utilisation de l'API Parrainage

## Prérequis
- Token d'authentification valide pour un commercial
- Headers requis : `Authorization: Bearer {token}`

## 1. Générer un nouveau code de parrainage

### Requête
```http
POST /v1/commercial/generer-code-parrainage
Authorization: Bearer {token}
Content-Type: application/json
```

### Réponse si aucun code existant
```json
{
    "success": true,
    "message": "Code parrainage généré avec succès",
    "data": {
        "code_parrainage": {
            "id": 1,
            "code_parrainage": "COMABC123",
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
        }
    }
}
```

### Réponse si code existant
```json
{
    "success": false,
    "message": "Vous avez déjà un code de parrainage actif. Il expire le 06/10/2026 à 17:00",
    "data": {
        "code_actuel": "COMABC123",
        "date_expiration": "2026-10-06 17:00:00",
        "peut_renouveler": false,
        "jours_restants": 365
    }
}
```

## 2. Voir le code parrainage actuel

### Requête
```http
GET /v1/commercial/mon-code-parrainage
Authorization: Bearer {token}
```

### Réponse
```json
{
    "success": true,
    "message": "Code de parrainage actuel récupéré avec succès",
    "data": {
        "code_parrainage": {
            "id": 1,
            "code_parrainage": "COMABC123",
            "date_debut": "2025-10-06 17:00:00",
            "date_expiration": "2026-10-06 17:00:00",
            "est_actif": true,
            "est_renouvele": false,
            "est_expire": false,
            "peut_renouveler": false,
            "jours_restants": 365,
            "duree_totale": 365,
            "statut": "Actif",
            "statut_color": "success",
            "date_debut_formatee": "06/10/2025 à 17:00",
            "date_expiration_formatee": "06/10/2026 à 17:00"
        }
    }
}
```

## 3. Voir l'historique des codes

### Requête
```http
GET /v1/commercial/historique-codes-parrainage
Authorization: Bearer {token}
```

### Réponse
```json
{
    "success": true,
    "message": "Historique des codes de parrainage récupéré avec succès",
    "data": {
        "codes": [
            {
                "id": 2,
                "code_parrainage": "COMXYZ789",
                "date_debut": "2025-10-06 17:30:00",
                "date_expiration": "2026-10-06 17:30:00",
                "est_actif": true,
                "est_renouvele": false,
                "est_expire": false,
                "peut_renouveler": false,
                "jours_restants": 365,
                "duree_totale": 365,
                "statut": "Actif",
                "statut_color": "success",
                "date_debut_formatee": "06/10/2025 à 17:30",
                "date_expiration_formatee": "06/10/2026 à 17:30",
                "created_at": "2025-10-06 17:30:00",
                "updated_at": "2025-10-06 17:30:00"
            },
            {
                "id": 1,
                "code_parrainage": "COMABC123",
                "date_debut": "2025-10-06 17:00:00",
                "date_expiration": "2026-10-06 17:00:00",
                "est_actif": false,
                "est_renouvele": true,
                "est_expire": false,
                "peut_renouveler": false,
                "jours_restants": 0,
                "duree_totale": 365,
                "statut": "Renouvelé",
                "statut_color": "warning",
                "date_debut_formatee": "06/10/2025 à 17:00",
                "date_expiration_formatee": "06/10/2026 à 17:00",
                "created_at": "2025-10-06 17:00:00",
                "updated_at": "2025-10-06 17:30:00"
            }
        ],
        "total": 2,
        "codes_actifs": 1,
        "codes_expires": 0
    }
}
```

## 4. Renouveler un code expiré

### Requête
```http
POST /v1/commercial/renouveler-code-parrainage
Authorization: Bearer {token}
```

### Réponse si code expiré
```json
{
    "success": true,
    "message": "Code de parrainage renouvelé avec succès",
    "data": {
        "nouveau_code": {
            "id": 3,
            "code_parrainage": "COMDEF456",
            "date_debut": "2025-10-06 18:00:00",
            "date_expiration": "2026-10-06 18:00:00",
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
            "id": 2,
            "code_parrainage": "COMXYZ789",
            "date_debut": "2025-10-06 17:30:00",
            "date_expiration": "2026-10-06 17:30:00",
            "est_actif": false,
            "est_renouvele": true,
            "est_expire": true,
            "peut_renouveler": false,
            "jours_restants": 0,
            "duree_totale": 365,
            "statut": "Renouvelé",
            "statut_color": "warning"
        }
    }
}
```

### Réponse si aucun code à renouveler
```json
{
    "success": false,
    "message": "Aucun code expiré à renouveler. Vous devez attendre l'expiration de votre code actuel."
}
```

## 5. Créer un compte client avec le code

### Requête
```http
POST /v1/commercial/creer-compte-client
Authorization: Bearer {token}
Content-Type: application/json

{
    "nom": "Dupont",
    "prenoms": "Jean",
    "email": "jean.dupont@example.com",
    "contact": "0123456789",
    "adresse": "123 Rue de la Paix",
    "date_naissance": "1990-01-15",
    "sexe": "M",
    "profession": "Ingénieur",
    "type_demandeur": "client",
    "type_client": "physique"
}
```

### Réponse
```json
{
    "success": true,
    "message": "Compte client créé avec succès. Un email a été envoyé au client avec ses informations de connexion.",
    "data": {
        "client": {
            "id": 123,
            "email": "jean.dupont@example.com",
            "contact": "0123456789",
            "adresse": "123 Rue de la Paix",
            "code_parrainage": "COMDEF456",
            "commercial_id": 1,
            "compte_cree_par_commercial": true
        }
    }
}
```

## Codes d'erreur

- **403** : Accès non autorisé (utilisateur n'est pas commercial)
- **422** : Code déjà existant ou aucune action possible
- **500** : Erreur serveur

## Notes importantes

1. **Un seul code actif** : Un commercial ne peut avoir qu'un seul code de parrainage actif à la fois
2. **Durée fixe** : Chaque code est valide pendant exactement 1 an
3. **Renouvellement** : Un nouveau code ne peut être généré qu'après l'expiration du précédent
4. **Historique complet** : Tous les codes précédents sont conservés pour consultation
5. **Compatibilité** : Le système reste compatible avec le code existant

