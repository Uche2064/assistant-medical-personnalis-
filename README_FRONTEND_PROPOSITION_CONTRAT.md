# API Routes - Proposition et Acceptation de Contrats

## 📋 Routes disponibles

### 1. Proposition de contrat (Technicien)

**Route :** `PUT /demandes-adhesions/{demande_id}/proposer-contrat`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Payload :**
```json
{
    "type_contrat": "standard",
    "commentaires": "Contrat standard recommandé"
}
```

**Types de contrat :** `basic`, `standard`, `premium`, `team`

**Réponse :**
```json
{
    "success": true,
    "message": "Proposition de contrat créée avec succès",
    "data": {
        "proposition_id": 123,
        "contrat": {
            "id": 456,
            "type_contrat": "standard",
            "prime_standard": 50000,
            "prime_standard_formatted": "50 000 FCFA"
        },
        "token": "abc123def456ghi789...",
        "expires_at": "2024-01-15T10:30:00Z"
    }
}
```

---

### 2. Consulter mes propositions (Client)

**Route :** `GET /client/contrats-proposes`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Propositions de contrat récupérées avec succès",
    "data": {
        "propositions": [
            {
                "id": 123,
                "statut": "proposee",
                "statut_label": "Proposée",
                "contrat": {
                    "id": 456,
                    "type_contrat": "standard",
                    "prime_standard": 50000,
                    "prime_standard_formatted": "50 000 FCFA"
                },
                "technicien": {
                    "id": 789,
                    "nom": "Dupont",
                    "prenoms": "Jean",
                    "nom_complet": "Jean Dupont"
                },
                "date_proposition": "2024-01-15 10:30:00",
                "commentaires_technicien": "Contrat standard recommandé"
            }
        ],
        "total": 1,
        "statistiques": {
            "proposees": 1,
            "acceptees": 0,
            "refusees": 0,
            "expirees": 0
        }
    }
}
```

---

### 3. Détails d'une proposition (Technicien/Médecin)

**Route :** `GET /demandes-adhesions/{demande_id}/propositions-contrat/{proposition_id}`

**Headers :**
```
Authorization: Bearer {token}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Proposition de contrat récupérée avec succès",
    "data": {
        "id": 123,
        "statut": "proposee",
        "contrat": {
            "id": 456,
            "type_contrat": "standard",
            "prime_standard": 50000,
            "prime_standard_formatted": "50 000 FCFA"
        },
        "technicien": {
            "id": 789,
            "nom": "Dupont",
            "prenoms": "Jean",
            "nom_complet": "Jean Dupont"
        },
        "garanties": [
            {
                "id": 1,
                "libelle": "Consultation",
                "plafond": 50000,
                "taux_couverture": 80
            }
        ],
        "prime": 50000,
        "prime_formatted": "50 000 FCFA",
        "prime_totale": 60000,
        "prime_totale_formatted": "60 000 FCFA",
        "meta": {
            "can_be_accepted": true,
            "can_be_refused": true,
            "days_until_expiry": 5
        }
    }
}
```

---

### 4. Accepter un contrat (Client)

**Route :** `POST /client/contrats-proposes/{proposition_id}/accepter`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Payload :**
```json
{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Contrat accepté avec succès",
    "data": {
        "contrat_id": 789,
        "message": "Contrat accepté avec succès"
    }
}
```

---

### 5. Refuser un contrat (Client)

**Route :** `POST /client/contrats-proposes/{proposition_id}/refuser`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Payload :**
```json
{
    "raison_refus": "Je refuse cette proposition"
}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Proposition refusée avec succès",
    "data": {
        "proposition_id": 123,
        "message": "Proposition refusée avec succès"
    }
}
```

---

### 6. Accepter via token (sans authentification)

**Route :** `POST /contrats/accepter/{token}`

**Headers :**
```
Content-Type: application/json
```

**Payload :**
```json
{
    "accepte": true,
    "commentaires": "J'accepte cette proposition"
}
```

**Réponse :** Même format que l'acceptation avec authentification

---

## 📊 Statuts des propositions

- `proposee` - Proposition en attente
- `acceptee` - Proposition acceptée
- `refusee` - Proposition refusée
- `expiree` - Proposition expirée

---

## 🚨 Codes d'erreur

| Code | Message |
|------|---------|
| 400 | Demande déjà traitée |
| 404 | Demande/Proposition non trouvée |
| 403 | Non autorisé |
| 422 | Type de contrat invalide | 