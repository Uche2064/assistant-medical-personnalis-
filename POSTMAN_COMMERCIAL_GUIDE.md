# Guide d'utilisation - Collection Postman Commercial

## 📋 Vue d'ensemble

La collection **19_Commercial_Module** contient tous les endpoints nécessaires pour gérer le système de parrainage commercial avec les nouvelles fonctionnalités de durée et d'historique.

## 🔧 Configuration

### Variables d'environnement requises :
- `base_url` : URL de base de l'API (ex: http://localhost:8000/api)
- `api_key` : Clé API obligatoire
- `access_token` : Token JWT du commercial connecté

### Prérequis :
1. **Authentification** : Le commercial doit être connecté et avoir un token JWT valide
2. **Rôle** : L'utilisateur doit avoir le rôle "commercial"
3. **Code parrainage** : Pour créer des comptes clients, le commercial doit avoir un code parrainage actif

## 🚀 Endpoints disponibles

### 1. **Générer Code Parrainage**
- **Méthode** : `POST`
- **URL** : `/v1/commercial/generer-code-parrainage`
- **Description** : Génère un nouveau code parrainage valide pour 1 an
- **Restriction** : Un commercial ne peut avoir qu'un seul code actif à la fois

#### Réponse si succès :
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
            "jours_restants": 365,
            "statut": "Actif"
        }
    }
}
```

#### Réponse si code existant :
```json
{
    "success": false,
    "message": "Vous avez déjà un code de parrainage actif. Il expire le 06/10/2026 à 17:00",
    "data": {
        "code_actuel": "COMABC123",
        "date_expiration": "2026-10-06 17:00:00",
        "jours_restants": 365
    }
}
```

### 2. **Voir Mon Code Parrainage Actuel**
- **Méthode** : `GET`
- **URL** : `/v1/commercial/mon-code-parrainage`
- **Description** : Récupère le code parrainage actuel avec toutes les informations

#### Réponse :
```json
{
    "success": true,
    "data": {
        "code_parrainage": {
            "id": 1,
            "code_parrainage": "COMABC123",
            "date_debut": "2025-10-06 17:00:00",
            "date_expiration": "2026-10-06 17:00:00",
            "est_actif": true,
            "jours_restants": 365,
            "statut": "Actif",
            "statut_color": "success"
        }
    }
}
```

### 3. **Historique des Codes Parrainage**
- **Méthode** : `GET`
- **URL** : `/v1/commercial/historique-codes-parrainage`
- **Description** : Récupère l'historique complet des codes de parrainage

#### Réponse :
```json
{
    "success": true,
    "data": {
        "codes": [
            {
                "id": 2,
                "code_parrainage": "COMXYZ789",
                "date_debut": "2025-10-06 17:30:00",
                "date_expiration": "2026-10-06 17:30:00",
                "est_actif": true,
                "statut": "Actif",
                "jours_restants": 365
            },
            {
                "id": 1,
                "code_parrainage": "COMABC123",
                "date_debut": "2025-10-06 17:00:00",
                "date_expiration": "2026-10-06 17:00:00",
                "est_actif": false,
                "est_renouvele": true,
                "statut": "Renouvelé"
            }
        ],
        "total": 2,
        "codes_actifs": 1,
        "codes_expires": 0
    }
}
```

### 4. **Renouveler Code Parrainage**
- **Méthode** : `POST`
- **URL** : `/v1/commercial/renouveler-code-parrainage`
- **Description** : Renouvelle le code après expiration
- **Restriction** : Ne peut être utilisé qu'après l'expiration du code actuel

#### Réponse si succès :
```json
{
    "success": true,
    "data": {
        "nouveau_code": {
            "id": 3,
            "code_parrainage": "COMDEF456",
            "date_debut": "2025-10-06 18:00:00",
            "date_expiration": "2026-10-06 18:00:00",
            "est_actif": true,
            "statut": "Actif"
        },
        "ancien_code": {
            "id": 2,
            "code_parrainage": "COMXYZ789",
            "est_renouvele": true,
            "statut": "Renouvelé"
        }
    }
}
```

### 5. **Créer Compte Client**
- **Méthode** : `POST`
- **URL** : `/v1/commercial/creer-compte-client`
- **Description** : Crée un compte client avec le code parrainage actuel
- **Prérequis** : Code parrainage actif requis

## 📊 Tests automatiques

La collection inclut des tests automatiques qui vérifient :

### Tests généraux :
- ✅ Code de statut HTTP correct (200, 201, 422)
- ✅ Structure de réponse avec `success` et `message`
- ✅ Présence des propriétés requises pour les codes de parrainage

### Tests spécifiques :
- ✅ Structure des réponses de parrainage
- ✅ Validation des données de code parrainage
- ✅ Vérification des dates et statuts

## 🔄 Flux d'utilisation typique

### Scénario 1 : Premier code
1. **Générer Code Parrainage** → Code créé pour 1 an
2. **Voir Mon Code Parrainage** → Vérifier les informations
3. **Créer Compte Client** → Utiliser le code pour lier des clients

### Scénario 2 : Code existant
1. **Voir Mon Code Parrainage** → Consulter le code actuel
2. **Historique des Codes** → Voir tous les codes précédents
3. **Tentative de nouveau code** → Retourne le code actuel avec date d'expiration

### Scénario 3 : Renouvellement
1. **Attendre expiration** du code actuel
2. **Renouveler Code Parrainage** → Génère un nouveau code
3. **Historique des Codes** → Voir l'ancien code marqué comme "renouvelé"

## ⚠️ Messages d'erreur courants

### Code déjà existant (422) :
```json
{
    "success": false,
    "message": "Vous avez déjà un code de parrainage actif. Il expire le 06/10/2026 à 17:00",
    "data": {
        "code_actuel": "COMABC123",
        "date_expiration": "2026-10-06 17:00:00",
        "jours_restants": 365
    }
}
```

### Aucun code à renouveler (422) :
```json
{
    "success": false,
    "message": "Aucun code expiré à renouveler. Vous devez attendre l'expiration de votre code actuel."
}
```

### Pas de code actif pour créer un client (422) :
```json
{
    "success": false,
    "message": "Vous n'avez pas de code de parrainage actif. Veuillez en générer un d'abord."
}
```

## 🎯 Bonnes pratiques

1. **Vérifiez toujours votre code actuel** avant de créer des clients
2. **Consultez l'historique** pour voir l'évolution de vos codes
3. **Planifiez le renouvellement** avant l'expiration
4. **Utilisez les tests automatiques** pour valider vos requêtes
5. **Gérez les erreurs 422** qui donnent des informations utiles

## 📝 Notes importantes

- **Durée fixe** : Chaque code est valide pendant exactement 1 an
- **Un seul code actif** : Impossible d'avoir plusieurs codes simultanément
- **Historique complet** : Tous les codes sont conservés pour consultation
- **Compatibilité** : Le système reste compatible avec le code existant
- **Automatique** : La création de clients utilise automatiquement le code actuel

