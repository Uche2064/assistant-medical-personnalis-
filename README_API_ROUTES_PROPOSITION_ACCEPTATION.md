# Routes API - Workflow Proposition et Acceptation de Contrats

## 🎯 Vue d'ensemble

Ce document contient toutes les routes API nécessaires pour implémenter le workflow complet de proposition et d'acceptation de contrats.

## 🔐 Authentification

Toutes les routes nécessitent un token Bearer dans le header :
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 📋 **1. Récupérer les contrats disponibles (GET)**

### **Route :**
```
GET /api/demandes-adhesions/contrats-disponibles
```

### **Permissions :**
- `technicien`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Contrats disponibles récupérés avec succès",
    "data": [
        {
            "id": 1,
            "nom": "Contrat Santé Premium",
            "type_contrat": "sante",
            "description": "Couverture santé complète",
            "prime_de_base": 50000,
            "categories_garanties": [
                {
                    "id": 1,
                    "libelle": "Hospitalisation",
                    "garanties": "Chambre individuelle, Soins intensifs, Réanimation"
                },
                {
                    "id": 2,
                    "libelle": "Consultations",
                    "garanties": "Médecin généraliste, Spécialiste, Psychologue"
                },
                {
                    "id": 3,
                    "libelle": "Analyses",
                    "garanties": "Sang, Urine, Radiologie, Échographie"
                }
            ]
        }
    ]
}
```

### **Response (Error - 403) :**
```json
{
    "success": false,
    "message": "Accès non autorisé",
    "code": 403
}
```

---

## 📝 **2. Faire une proposition de contrat (PUT)**

### **Route :**
```
PUT /api/demandes-adhesions/{demande_id}/proposer-contrat
```

### **Permissions :**
- `technicien`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Request Payload :**
```json
{
    "contrat_id": 1,
    "prime_proposee": 45000,
    "taux_couverture": 85,
    "frais_gestion": 15,
    "commentaires": "Proposition adaptée à votre profil",
    "garanties_incluses": [1, 2, 3]
}
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Contrat proposé avec succès",
    "data": {
        "proposition_id": 1,
        "demande_id": 1,
        "contrat": {
            "id": 1,
            "nom": "Contrat Santé Premium"
        },
        "prime_proposee": 45000,
        "taux_couverture": 85,
        "frais_gestion": 15,
        "commentaires": "Proposition adaptée à votre profil",
        "date_proposition": "2025-01-15T10:30:00Z",
        "statut": "PROPOSEE"
    }
}
```

### **Response (Error - 400) :**
```json
{
    "success": false,
    "message": "Données invalides",
    "code": 400,
    "errors": {
        "contrat_id": ["Le contrat sélectionné n'est pas valide"],
        "prime_proposee": ["La prime proposée doit être supérieure à 0"]
    }
}
```

---

## ✅ **3. Accepter un contrat (POST)**

### **Route :**
```
POST /api/client/contrats-proposes/{proposition_id}/accepter
```

### **Permissions :**
- `physique`
- `entreprise`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Request Payload :**
```json
{}
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Contrat accepté avec succès",
    "data": {
        "contrat_id": 1,
        "proposition_id": 1,
        "prime": 45000,
        "taux_couverture": 85,
        "date_debut": "2025-01-15T00:00:00Z",
        "date_fin": "2026-01-15T00:00:00Z",
        "statut": "ACTIF"
    }
}
```

### **Response (Error - 400) :**
```json
{
    "success": false,
    "message": "Cette proposition a déjà été traitée",
    "code": 400
}
```

---

## 🏥 **4. Assigner réseau prestataires (POST)**

### **Route :**
```
POST /api/technicien/assigner-reseau-prestataires
```

### **Permissions :**
- `technicien`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Request Payload :**
```json
{
    "client_id": 1,
    "contrat_id": 1,
    "prestataires": {
        "pharmacies": [1, 2],
        "centres_soins": [3, 4, 5],
        "optiques": [6, 7],
        "laboratoire_centre_diagnostic": [8, 9]
    }
}
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Réseau de prestataires assigné avec succès",
    "data": {
        "client_contrat_id": 1,
        "prestataires_assignes": [
            {
                "id": 1,
                "nom": "Pharmacie Centrale",
                "type": "pharmacie",
                "adresse": "123 Rue Principale, Abidjan"
            },
            {
                "id": 3,
                "nom": "Centre Médical Saint-Jean",
                "type": "centre_soins",
                "adresse": "456 Avenue de la Santé, Abidjan"
            }
        ],
        "nombre_prestataires": 8
    }
}
```

### **Response (Error - 400) :**
```json
{
    "success": false,
    "message": "Certains prestataires ne sont pas disponibles dans cette zone",
    "code": 400
}
```

---

## 📋 **5. Récupérer contrats proposés/acceptés (GET)**

### **Route :**
```
GET /api/client/contrats-proposes
```

### **Permissions :**
- `physique`
- `entreprise`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Contrats proposés récupérés avec succès",
    "data": [
        {
            "proposition_id": 1,
            "contrat": {
                "id": 1,
                "nom": "Contrat Santé Premium",
                "type_contrat": "sante",
                "description": "Couverture santé complète"
            },
            "details_proposition": {
                "prime_proposee": 45000,
                "taux_couverture": 85,
                "frais_gestion": 15,
                "commentaires_technicien": "Proposition adaptée à votre profil",
                "date_proposition": "2025-01-15T10:30:00Z"
            },
            "categories_garanties": [
                {
                    "libelle": "Hospitalisation",
                    "garanties": "Chambre individuelle, Soins intensifs, Réanimation"
                },
                {
                    "libelle": "Consultations",
                    "garanties": "Médecin généraliste, Spécialiste, Psychologue"
                }
            ],
            "statut": "PROPOSEE"
        }
    ]
}
```

### **Response (Error - 403) :**
```json
{
    "success": false,
    "message": "Accès non autorisé",
    "code": 403
}
```

---

## 🔍 **6. Rechercher des clients (GET)**

### **Route :**
```
GET /api/technicien/clients?search=john
```

### **Permissions :**
- `technicien`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Liste des clients récupérée avec succès",
    "data": [
        {
            "id": 1,
            "client_id": 1,
            "nom": "John Doe",
            "email": "john@example.com",
            "type_demandeur": "physique",
            "statut": "EN_ATTENTE",
            "date_soumission": "2025-01-15",
            "duree_attente": "3 jours"
        }
    ]
}
```

---

## 🏥 **7. Rechercher des prestataires (GET)**

### **Route :**
```
GET /api/technicien/prestataires?search=pharmacie&type_prestataire=pharmacie
```

### **Permissions :**
- `technicien`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Liste des prestataires récupérée avec succès",
    "data": [
        {
            "id": 1,
            "nom": "Pharmacie Centrale",
            "type_prestataire": "pharmacie",
            "adresse": "123 Rue Principale, Abidjan",
            "telephone": "+22501234567",
            "email": "contact@pharmacie-centrale.ci",
            "statut": "valide"
        }
    ]
}
```

---

## 📊 **8. Voir les détails d'une demande (GET)**

### **Route :**
```
GET /api/demandes-adhesions/{id}
```

### **Permissions :**
- `medecin_controleur`
- `technicien`
- `admin_global`

### **Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### **Response (Success - 200) :**
```json
{
    "success": true,
    "message": "Détails de la demande d'adhésion",
    "data": {
        "id": 1,
        "type_demandeur": "physique",
        "statut": "EN_ATTENTE",
        "created_at": "2025-01-15T10:30:00Z",
        "updated_at": "2025-01-15T10:30:00Z",
        "motif_rejet": null,
        "valide_par": null,
        "valider_a": null,
        "user": {
            "id": 1,
            "nom": "John Doe",
            "email": "john@example.com"
        },
        "assure": {
            "id": 1,
            "nom": "John",
            "prenoms": "Doe",
            "date_naissance": "1990-01-01",
            "sexe": "M"
        },
        "beneficiaires": [
            {
                "id": 2,
                "nom": "Jane",
                "prenoms": "Doe",
                "date_naissance": "1992-01-01",
                "sexe": "F",
                "lien_parente": "conjoint"
            }
        ],
        "reponses_questionnaire": [
            {
                "question": {
                    "id": 1,
                    "libelle": "Avez-vous des antécédents médicaux ?"
                },
                "reponse": "Non"
            }
        ]
    }
}
```

---

## 🚨 **Codes d'Erreur Communs**

### **400 - Bad Request :**
```json
{
    "success": false,
    "message": "Données invalides",
    "code": 400
}
```

### **401 - Unauthorized :**
```json
{
    "success": false,
    "message": "Token invalide ou expiré",
    "code": 401
}
```

### **403 - Forbidden :**
```json
{
    "success": false,
    "message": "Accès non autorisé",
    "code": 403
}
```

### **404 - Not Found :**
```json
{
    "success": false,
    "message": "Ressource non trouvée",
    "code": 404
}
```

### **500 - Server Error :**
```json
{
    "success": false,
    "message": "Erreur interne du serveur",
    "code": 500
}
```

---

## 📋 **Résumé des Routes Principales**

| Méthode | Route | Description | Permissions |
|---------|-------|-------------|-------------|
| GET | `/api/demandes-adhesions/contrats-disponibles` | Récupérer contrats disponibles | `technicien` |
| PUT | `/api/demandes-adhesions/{id}/proposer-contrat` | Proposer un contrat | `technicien` |
| POST | `/api/client/contrats-proposes/{id}/accepter` | Accepter un contrat | `physique`, `entreprise` |
| POST | `/api/technicien/assigner-reseau-prestataires` | Assigner réseau prestataires | `technicien` |
| GET | `/api/client/contrats-proposes` | Voir contrats proposés | `physique`, `entreprise` |
| GET | `/api/technicien/clients` | Rechercher clients | `technicien` |
| GET | `/api/technicien/prestataires` | Rechercher prestataires | `technicien` |
| GET | `/api/demandes-adhesions/{id}` | Voir détails demande | `technicien`, `medecin_controleur`, `admin_global` |

---

## 🔄 **Workflow Complet**

1. **Technicien** → `GET /api/technicien/clients` → Recherche client
2. **Technicien** → `GET /api/demandes-adhesions/{id}` → Voir détails client
3. **Technicien** → `GET /api/demandes-adhesions/contrats-disponibles` → Voir contrats
4. **Technicien** → `PUT /api/demandes-adhesions/{id}/proposer-contrat` → Proposer contrat
5. **Client** → `GET /api/client/contrats-proposes` → Voir propositions
6. **Client** → `POST /api/client/contrats-proposes/{id}/accepter` → Accepter contrat
7. **Technicien** → `GET /api/technicien/prestataires` → Rechercher prestataires
8. **Technicien** → `POST /api/technicien/assigner-reseau-prestataires` → Assigner réseau

---

*Ce document contient toutes les routes API nécessaires pour implémenter le workflow complet de proposition et d'acceptation de contrats.* 