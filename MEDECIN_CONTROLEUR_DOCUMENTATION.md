# Documentation - Module Médecin Contrôleur 🩺

## 📋 Vue d'ensemble

Le module Médecin Contrôleur permet de gérer les questions pour les prestataires, valider les demandes d'adhésion des prestataires, gérer les garanties et catégories de garanties, et valider les factures d'un point de vue médical.

---

## 🔐 Authentification

**Rôle requis** : `medecin_controleur`  
**Header requis** : `Authorization: Bearer {token}`

---

## 📚 Fonctionnalités Principales

### 1. **Gestion des Questions**
- Créer des questions pour les prestataires
- Modifier et supprimer des questions
- Insertion en masse pour optimisation
- Statistiques des questions

### 2. **Gestion des Garanties**
- Créer et gérer les garanties médicales
- Définir les montants maximum
- Activer/Désactiver les garanties

### 3. **Gestion des Catégories de Garanties**
- Organiser les garanties par catégories
- Associer plusieurs garanties à une catégorie
- Gérer les catégories de soins

### 4. **Validation des Prestataires**
- Valider les demandes d'adhésion des prestataires
- Vérifier les qualifications médicales
- Rejeter avec motif si non conforme

### 5. **Validation des Factures**
- Valider les factures d'un point de vue médical
- Vérifier la conformité des actes médicaux
- Rejeter les factures non conformes

---

## 🔗 Endpoints Disponibles

### Questions (`/v1/questions`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/v1/questions` | Liste toutes les questions |
| GET | `/v1/questions?destinataire=prestataire` | Questions filtrées par destinataire |
| GET | `/v1/questions/{id}` | Détails d'une question |
| POST | `/v1/questions` | Créer questions en masse |
| PUT | `/v1/questions/{id}` | Modifier une question |
| DELETE | `/v1/questions/{id}` | Supprimer une question |
| DELETE | `/v1/questions/bulk-delete` | Supprimer en masse |
| GET | `/v1/questions/stats` | Statistiques des questions |

### Garanties (`/v1/garanties`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/v1/garanties` | Liste toutes les garanties |
| POST | `/v1/garanties` | Créer une garantie |
| PUT | `/v1/garanties/{id}` | Modifier une garantie |
| DELETE | `/v1/garanties/{id}` | Supprimer une garantie |
| PATCH | `/v1/garanties/{id}` | Activer/Désactiver |

### Catégories de Garanties (`/v1/categories-garanties`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/v1/categories-garanties` | Liste toutes les catégories |
| POST | `/v1/categories-garanties` | Créer une catégorie |
| PUT | `/v1/categories-garanties/{id}` | Modifier une catégorie |
| DELETE | `/v1/categories-garanties/{id}` | Supprimer une catégorie |
| PATCH | `/v1/categories-garanties/{catId}/garanties/{garId}/toggle` | Toggle garantie |

### Demandes d'Adhésion (`/v1/demandes-adhesions`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/v1/demandes-adhesions` | Liste des demandes |
| GET | `/v1/demandes-adhesions/{id}` | Détails d'une demande |
| PUT | `/v1/demandes-adhesions/{id}/valider-prestataire` | Valider prestataire |
| PUT | `/v1/demandes-adhesions/{id}/rejeter` | Rejeter demande |

### Factures (`/v1/factures`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/v1/factures/{id}/validate-medecin` | Valider facture |
| POST | `/v1/factures/{id}/reject-medecin` | Rejeter facture |

### Assurés (`/v1/assures`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/v1/assures/stats` | Statistiques des assurés |

---

## 📊 Exemples de Requêtes

### 1. Créer des Questions en Masse

**Requête** :
```http
POST /v1/questions
Content-Type: application/json
Authorization: Bearer {token}

[
    {
        "libelle": "Quelle est votre spécialité médicale ?",
        "type_de_donnee": "select",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true,
        "options": ["Médecine générale", "Pédiatrie", "Cardiologie"]
    },
    {
        "libelle": "Nombre d'années d'expérience",
        "type_de_donnee": "number",
        "destinataire": "prestataire",
        "est_obligatoire": true,
        "est_active": true
    }
]
```

**Réponse** :
```json
{
    "success": true,
    "message": "2 questions créées avec succès",
    "data": [
        {
            "id": 1,
            "libelle": "Quelle est votre spécialité médicale ?",
            "type_de_donnee": "select",
            "destinataire": "prestataire",
            "est_obligatoire": true,
            "est_active": true,
            "options": ["Médecine générale", "Pédiatrie", "Cardiologie"]
        }
    ]
}
```

### 2. Créer une Garantie

**Requête** :
```http
POST /v1/garanties
Content-Type: application/json
Authorization: Bearer {token}

{
    "libelle": "Consultation générale",
    "description": "Consultation médicale générale",
    "montant_max": 50000,
    "est_active": true
}
```

### 3. Créer une Catégorie de Garanties

**Requête** :
```http
POST /v1/categories-garanties
Content-Type: application/json
Authorization: Bearer {token}

{
    "nom": "Soins dentaires",
    "description": "Catégorie pour tous les soins dentaires",
    "garanties": [1, 2, 3]
}
```

### 4. Valider un Prestataire

**Requête** :
```http
PUT /v1/demandes-adhesions/{id}/valider-prestataire
Content-Type: application/json
Authorization: Bearer {token}

{
    "commentaire": "Prestataire validé après vérification des documents"
}
```

### 5. Valider une Facture

**Requête** :
```http
POST /v1/factures/{id}/validate-medecin
Content-Type: application/json
Authorization: Bearer {token}

{
    "commentaire": "Facture conforme aux actes médicaux déclarés"
}
```

---

## 📊 Types de Données pour Questions

Les questions peuvent avoir différents types de données :

- **text** : Texte libre
- **number** : Nombre
- **date** : Date
- **select** : Liste déroulante (options requises)
- **checkbox** : Cases à cocher (options requises)
- **radio** : Boutons radio (options requises)
- **textarea** : Texte long
- **email** : Email
- **tel** : Téléphone
- **file** : Fichier

---

## 🎯 Destinataires des Questions

- **prestataire** : Questions pour les prestataires de soins
- **client** : Questions pour les clients
- **autre** : Questions pour autres types

---

## 🔄 Workflow de Validation Prestataire

```
1. Prestataire soumet demande d'adhésion
   ↓
2. Prestataire répond aux questions
   ↓
3. Médecin contrôleur consulte la demande
   ↓
4. Médecin contrôleur vérifie les réponses
   ↓
5. Médecin contrôleur valide ou rejette
   ↓
6. Si validé : Compte prestataire créé + Email envoyé
   Si rejeté : Email de notification avec motif
```

---

## 🔄 Workflow de Validation Facture

```
1. Prestataire soumet facture
   ↓
2. Technicien valide la facture (vérification technique)
   ↓
3. Médecin contrôleur valide la facture (vérification médicale)
   ↓
4. Comptable autorise le remboursement
   ↓
5. Remboursement effectué
```

---

## 📈 Statistiques des Questions

**Endpoint** : `GET /v1/questions/stats`

**Réponse** :
```json
{
    "success": true,
    "data": {
        "total": 25,
        "actives": 20,
        "inactives": 5,
        "obligatoires": 15,
        "optionnelles": 10,
        "repartition_par_destinataire": {
            "prestataire": 18,
            "client": 5,
            "autre": 2
        }
    }
}
```

---

## ⚠️ Codes d'Erreur

| Code | Description |
|------|-------------|
| 403 | Accès non autorisé (rôle incorrect) |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## 🔒 Permissions Partagées

Certains endpoints sont accessibles à plusieurs rôles :

### Garanties et Catégories
- **Médecin Contrôleur** : Création, modification, suppression
- **Technicien** : Création, modification, suppression

### Demandes d'Adhésion
- **Médecin Contrôleur** : Validation prestataires uniquement
- **Technicien** : Validation clients et autres

### Factures
- **Médecin Contrôleur** : Validation médicale (2ème étape)
- **Technicien** : Validation technique (1ère étape)
- **Comptable** : Autorisation remboursement (3ème étape)

---

## 📝 Notes Importantes

1. **Questions en masse** : Utilisez l'endpoint bulk pour créer plusieurs questions d'un coup (plus performant)
2. **Options obligatoires** : Pour les types select, checkbox, radio, le champ options est obligatoire
3. **Validation factures** : Une facture doit d'abord être validée par un technicien avant validation médicale
4. **Validation prestataires** : Seul le médecin contrôleur peut valider les prestataires
5. **Suppression en masse** : Utilisez l'endpoint bulk-delete pour supprimer plusieurs questions

---

## 🚀 Collection Postman

La collection **20_Medecin_Controleur_Module.postman_collection.json** contient tous les endpoints avec :
- Exemples de requêtes
- Variables d'environnement
- Headers configurés
- Corps de requêtes pré-remplis

---

## 🎯 Prochaines Étapes

1. Importer la collection dans Postman
2. Configurer les variables d'environnement
3. Se connecter en tant que médecin contrôleur
4. Tester les différents endpoints
5. Intégrer dans le frontend Angular
