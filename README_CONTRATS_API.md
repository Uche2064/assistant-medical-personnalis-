# API Contrats - Documentation Frontend

## Vue d'ensemble

Cette documentation décrit l'API pour la gestion des contrats dans le système SUNU Santé. Les contrats sont créés par les techniciens et peuvent être consultés par différents rôles selon les permissions.

## Base URL

```
https://api.sunusante.com/api/v1
```

## Authentification

Toutes les routes nécessitent une authentification Bearer Token :

```
Authorization: Bearer {token}
```

## Routes disponibles

### 1. Lister les contrats

**Route :** `GET /contrats`

**Permissions :** Tous les utilisateurs authentifiés

**Paramètres de requête :**
- `type` (optionnel) : Type de contrat (`basic`, `standard`, `premium`, `team`)
- `min` (optionnel) : Montant minimum de la prime
- `max` (optionnel) : Montant maximum de la prime
- `page` (optionnel) : Numéro de page pour la pagination
- `per_page` (optionnel) : Nombre d'éléments par page (défaut: 15)

**Exemple de requête :**
```bash
GET /contrats?type=premium&min=50000&max=100000&page=1&per_page=10
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Liste des contrats récupérée avec succès",
    "data": {
        "data": [
            {
                "id": 1,
                "type_contrat": "premium",
                "type_contrat_label": "Premium",
                "prime_standard": 75000.00,
                "prime_standard_formatted": "75 000 FCFA",
                "date_debut": "2024-01-01",
                "date_fin": "2024-12-31",
                "est_actif": true,
                "est_actif_label": "Actif",
                "categories_garanties_standard": [1, 2, 3, 4, 5, 6, 7],
                "created_at": "2024-01-01 00:00:00",
                "updated_at": "2024-01-01 00:00:00",
                "technicien": {
                    "id": 1,
                    "nom": "Technicien",
                    "prenoms": "Tech",
                    "email": "technicien1@gmail.com",
                    "telephone": "+2250123456789",
                    "nom_complet": "Technicien Tech"
                },
                "categories_garanties": [
                    {
                        "id": 1,
                        "libelle": "sante",
                        "libelle_formatted": "Sante",
                        "description": "Garanties liées aux soins de santé",
                        "couverture": 90.00,
                        "couverture_formatted": "90%",
                        "garanties": [
                            {
                                "id": 1,
                                "libelle": "consultation medecin generaliste",
                                "libelle_formatted": "Consultation medecin generaliste",
                                "plafond": 15000.00,
                                "plafond_formatted": "15 000 FCFA",
                                "prix_standard": 5000.00,
                                "prix_standard_formatted": "5 000 FCFA",
                                "taux_couverture": 80.00,
                                "taux_couverture_formatted": "80%"
                            }
                        ]
                    }
                ],
                "statistiques": {
                    "nombre_categories": 1,
                    "nombre_garanties": 1,
                    "couverture_moyenne": 90.00
                },
                "meta": {
                    "is_expired": false,
                    "is_active": true,
                    "days_until_expiry": 365,
                    "can_be_modified": true
                }
            }
        ],
        "meta": {
            "total": 4,
            "actifs": 3,
            "inactifs": 1,
            "expires": 0,
            "repartition_types": {
                "basic": 1,
                "standard": 1,
                "premium": 1,
                "team": 1
            },
            "repartition_prix": {
                "0-25000": 1,
                "25001-50000": 1,
                "50001-75000": 1,
                "75001+": 1
            },
            "prix_moyen": 66250.00,
            "prix_min": 25000.00,
            "prix_max": 120000.00
        }
    }
}
```

### 2. Récupérer un contrat spécifique

**Route :** `GET /contrats/{id}`

**Permissions :** Technicien

**Exemple de requête :**
```bash
GET /contrats/1
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Contrat récupéré avec succès",
    "data": {
        "id": 1,
        "type_contrat": "premium",
        "type_contrat_label": "Premium",
        "prime_standard": 75000.00,
        "prime_standard_formatted": "75 000 FCFA",
        "date_debut": "2024-01-01",
        "date_fin": "2024-12-31",
        "est_actif": true,
        "est_actif_label": "Actif",
        "categories_garanties_standard": [1, 2, 3, 4, 5, 6, 7],
        "created_at": "2024-01-01 00:00:00",
        "updated_at": "2024-01-01 00:00:00",
        "technicien": {
            "id": 1,
            "nom": "Technicien",
            "prenoms": "Tech",
            "email": "technicien1@gmail.com",
            "telephone": "+2250123456789",
            "nom_complet": "Technicien Tech"
        },
        "categories_garanties": [
            {
                "id": 1,
                "libelle": "sante",
                "libelle_formatted": "Sante",
                "description": "Garanties liées aux soins de santé",
                "couverture": 90.00,
                "couverture_formatted": "90%",
                "garanties": [
                    {
                        "id": 1,
                        "libelle": "consultation medecin generaliste",
                        "libelle_formatted": "Consultation medecin generaliste",
                        "plafond": 15000.00,
                        "plafond_formatted": "15 000 FCFA",
                        "prix_standard": 5000.00,
                        "prix_standard_formatted": "5 000 FCFA",
                        "taux_couverture": 80.00,
                        "taux_couverture_formatted": "80%"
                    }
                ]
            }
        ],
        "statistiques": {
            "nombre_categories": 1,
            "nombre_garanties": 1,
            "couverture_moyenne": 90.00
        },
        "meta": {
            "is_expired": false,
            "is_active": true,
            "days_until_expiry": 365,
            "can_be_modified": true
        }
    }
}
```

### 3. Créer un nouveau contrat

**Route :** `POST /contrats`

**Permissions :** Technicien

**Payload :**
```json
{
    "type_contrat": "premium",
    "prime_standard": 75000,
    "categories_garanties": [
        {
            "categorie_garantie_id": 1,
            "couverture": 90.00
        },
        {
            "categorie_garantie_id": 2,
            "couverture": 85.00
        }
    ]
}
```

**Réponse de succès (201) :**
```json
{
    "success": true,
    "message": "Contrat créé avec succès",
    "data": {
        "id": 5,
        "type_contrat": "premium",
        "prime_standard": "75000.00",
        "technicien_id": 3,
        "date_debut": "2024-01-01",
        "date_fin": "2024-12-31",
        "est_actif": true,
        "categories_garanties_standard": [1, 2],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "technicien": {
            "id": 3,
            "nom": "Technicien",
            "prenoms": "Tech",
            "email": "technicien1@gmail.com"
        },
        "categories_garanties": [
            {
                "id": 1,
                "libelle": "sante",
                "pivot": {
                    "couverture": 90.00
                }
            }
        ]
    }
}
```

### 4. Mettre à jour un contrat

**Route :** `PUT /contrats/{id}`

**Permissions :** Technicien

**Payload :**
```json
{
    "type_contrat": "standard",
    "prime_standard": 45000
}
```

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Contrat mis à jour avec succès",
    "data": {
        "id": 1,
        "type_contrat": "standard",
        "prime_standard": "45000.00",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 5. Supprimer un contrat

**Route :** `DELETE /contrats/{id}`

**Permissions :** Technicien

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Contrat supprimé avec succès",
    "data": null
}
```

### 6. Statistiques des contrats

**Route :** `GET /contrats/stats`

**Permissions :** Technicien

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Statistiques des contrats",
    "data": {
        "total": 4,
        "actifs": 3,
        "suspendus": 1,
        "type_contrat": {
            "basic": 1,
            "standard": 1,
            "premium": 1,
            "team": 1
        }
    }
}
```

### 7. Récupérer les catégories de garanties

**Route :** `GET /contrats/categories-garanties`

**Permissions :** Tous les utilisateurs authentifiés

**Réponse de succès (200) :**
```json
{
    "success": true,
    "message": "Catégories de garanties récupérées avec succès",
    "data": [
        {
            "id": 1,
            "libelle": "sante",
            "libelle_formatted": "Sante",
            "description": "Garanties liées aux soins de santé",
            "created_at": "2024-01-01 00:00:00",
            "updated_at": "2024-01-01 00:00:00",
            "garanties": [
                {
                    "id": 1,
                    "libelle": "consultation medecin generaliste",
                    "libelle_formatted": "Consultation medecin generaliste",
                    "plafond": 15000.00,
                    "plafond_formatted": "15 000 FCFA",
                    "prix_standard": 5000.00,
                    "prix_standard_formatted": "5 000 FCFA",
                    "taux_couverture": 80.00,
                    "taux_couverture_formatted": "80%",
                    "created_at": "2024-01-01 00:00:00"
                },
                {
                    "id": 2,
                    "libelle": "consultation specialiste",
                    "libelle_formatted": "Consultation specialiste",
                    "plafond": 25000.00,
                    "plafond_formatted": "25 000 FCFA",
                    "prix_standard": 8000.00,
                    "prix_standard_formatted": "8 000 FCFA",
                    "taux_couverture": 80.00,
                    "taux_couverture_formatted": "80%",
                    "created_at": "2024-01-01 00:00:00"
                }
            ],
            "medecin_controleur": {
                "id": 1,
                "nom": "Médecin",
                "prenoms": "Contrôleur",
                "nom_complet": "Médecin Contrôleur",
                "email": "medecin.controleur@gmail.com",
                "telephone": "+2250123456789"
            },
            "statistiques": {
                "nombre_garanties": 2,
                "plafond_moyen": 20000.00,
                "prix_moyen": 6500.00,
                "taux_couverture_moyen": 80.00
            }
        }
    ]
}
```

## Types de contrats disponibles

```json
{
    "basic": {
        "label": "Basique",
        "description": "Contrat de base avec couverture minimale",
        "prime_standard": 25000,
        "couverture": 70
    },
    "standard": {
        "label": "Standard",
        "description": "Contrat standard avec couverture complète",
        "prime_standard": 45000,
        "couverture": 80
    },
    "premium": {
        "label": "Premium",
        "description": "Contrat premium avec couverture étendue",
        "prime_standard": 75000,
        "couverture": 90
    },
    "team": {
        "label": "Équipe",
        "description": "Contrat pour équipes/entreprises",
        "prime_standard": 120000,
        "couverture": 95
    }
}
```

## Catégories de garanties disponibles

```json
[
    {
        "id": 1,
        "libelle": "sante",
        "description": "Garanties liées aux soins de santé"
    },
    {
        "id": 2,
        "libelle": "pharmacie",
        "description": "Garanties pour les médicaments"
    },
    {
        "id": 3,
        "libelle": "laboratoire",
        "description": "Garanties pour les analyses médicales"
    },
    {
        "id": 4,
        "libelle": "optique",
        "description": "Garanties pour les soins ophtalmologiques"
    },
    {
        "id": 5,
        "libelle": "dentaire",
        "description": "Garanties pour les soins dentaires"
    },
    {
        "id": 6,
        "libelle": "maternite",
        "description": "Garanties spécialisées pour la grossesse"
    },
    {
        "id": 7,
        "libelle": "urgence",
        "description": "Garanties pour les soins d'urgence"
    },
    {
        "id": 8,
        "libelle": "prevention",
        "description": "Garanties pour les examens de prévention"
    }
]
```

## Codes d'erreur

### 400 - Bad Request
```json
{
    "success": false,
    "message": "Erreur de validation",
    "errors": {
        "type_contrat": ["Le type de contrat est obligatoire."],
        "prime_standard": ["La prime standard doit être un nombre."]
    }
}
```

### 401 - Unauthorized
```json
{
    "success": false,
    "message": "Non authentifié",
    "data": null
}
```

### 403 - Forbidden
```json
{
    "success": false,
    "message": "Accès interdit",
    "data": null
}
```

### 404 - Not Found
```json
{
    "success": false,
    "message": "Contrat non trouvé",
    "data": null
}
```

### 422 - Validation Error
```json
{
    "success": false,
    "message": "Erreur de validation",
    "errors": {
        "categories_garanties": ["Au moins une catégorie de garantie est requise."],
        "categories_garanties.0.couverture": ["La couverture doit être comprise entre 0 et 100."]
    }
}
```

### 500 - Internal Server Error
```json
{
    "success": false,
    "message": "Erreur interne du serveur",
    "data": null
}
```

## Exemples d'utilisation

### Frontend React/Vue.js

```javascript
// Lister les contrats
const getContrats = async (filters = {}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/v1/contrats?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    });
    return response.json();
};

// Créer un contrat
const createContrat = async (contratData) => {
    const response = await fetch('/api/v1/contrats', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(contratData)
    });
    return response.json();
};

// Mettre à jour un contrat
const updateContrat = async (id, contratData) => {
    const response = await fetch(`/api/v1/contrats/${id}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(contratData)
    });
    return response.json();
};
```

## Notes importantes

1. **Permissions** : Seuls les techniciens peuvent créer, modifier et supprimer des contrats
2. **Validation** : Toutes les données sont validées côté serveur
3. **Pagination** : La liste des contrats est paginée par défaut
4. **Relations** : Les contrats incluent automatiquement les relations technicien et catégories de garanties
5. **Soft Delete** : Les contrats supprimés sont conservés en base de données
6. **Audit** : Toutes les actions sont tracées avec les timestamps 