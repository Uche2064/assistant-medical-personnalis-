# 📋 Flow Complet des Sinistres - API Documentation

## 🎯 Vue d'ensemble

Le système de gestion des sinistres fonctionne en **2 étapes principales** :

1. **Étape 1** : Enregistrement du sinistre par le prestataire
2. **Étape 2** : Création de la facture avec les actes médicaux

## 🔐 Authentification

Toutes les routes nécessitent une authentification JWT :

```http
Authorization: Bearer {jwt_token}
```

## 🏥 Étape 1 : Enregistrement du Sinistre

### 1.1 Recherche d'Assurés Assignés

**Endpoint :** `GET /api/v1/prestataire/search-assures`

**Description :** Un prestataire ne peut voir que les assurés qui lui ont été assignés par un technicien.

**Paramètres :**
```http
?search=Jean&per_page=10
```

**Réponse :**
```json
{
  "success": true,
  "message": "Assurés assignés trouvés",
  "data": [
    {
      "id": 123,
      "nom": "Dupont",
      "prenoms": "Jean Pierre",
      "email": "jean.dupont@email.com",
      "contact": "+225 07 12 34 56 78",
      "type_assure": "Employé - SUNU ASSURANCES",
      "est_principal": false,
      "contrat": {
        "id": 45,
        "type_contrat": "ENTREPRISE",
        "est_actif": true,
        "statut": "ACTIF"
      }
    }
  ]
}
```

**Codes d'erreur :**
- `403` : Utilisateur non prestataire
- `200` avec tableau vide : Aucun client assigné

### 1.2 Créer un Sinistre

**Endpoint :** `POST /api/v1/prestataire/sinistres`

**Payload :**
```json
{
  "assure_id": 123,
  "description": "Consultation médicale pour grippe"
}
```

**Validation :**
- `assure_id` : Obligatoire, doit exister dans la table `assures`
- `description` : Optionnel, max 1000 caractères

**Réponse :**
```json
{
  "success": true,
  "message": "Sinistre créé avec succès",
  "data": {
    "id": 456,
    "description": "Consultation médicale pour grippe",
    "date_sinistre": "2025-01-08T10:30:00.000000Z",
    "statut": "DECLARE",
    "assure": {
      "id": 123,
      "nom": "Dupont",
      "prenoms": "Jean Pierre",
      "email": "jean.dupont@email.com",
      "contact": "+225 07 12 34 56 78",
      "est_principal": false,
      "type_assure": "Employé - SUNU ASSURANCES",
      "contrat": {
        "id": 45,
        "type_contrat": "ENTREPRISE",
        "est_actif": true
      }
    },
    "created_at": "2025-01-08T10:30:00.000000Z"
  }
}
```

**Codes d'erreur :**
- `403` : Utilisateur non prestataire
- `403` : Assuré non assigné au prestataire
- `400` : Assuré sans contrat actif
- `404` : Assuré non trouvé
- `422` : Erreur de validation

## 💊 Étape 2 : Création de la Facture

### 2.1 Récupérer les Garanties du Contrat

**Endpoint :** `GET /api/v1/prestataire/contrats/{contrat_id}/garanties`

**Réponse :**
```json
{
  "success": true,
  "message": "Garanties récupérées avec succès",
  "data": [
    {
      "id": 78,
      "libelle": "Consultation médicale générale",
      "prix_standard": 15000.00,
      "taux_couverture": 80.00,
      "plafond": 50000.00,
      "categorie": {
        "id": 12,
        "libelle": "Soins ambulatoires",
        "couverture": 80.00
      }
    },
    {
      "id": 79,
      "libelle": "Prescription et délivrance médicaments",
      "prix_standard": 25000.00,
      "taux_couverture": 70.00,
      "plafond": 100000.00,
      "categorie": {
        "id": 13,
        "libelle": "Pharmacie",
        "couverture": 70.00
      }
    }
  ]
}
```

### 2.2 Créer la Facture

**Endpoint :** `POST /api/v1/prestataire/sinistres/{sinistre_id}/facture`

**Payload :**
```json
{
  "diagnostic": "Grippe saisonnière avec fièvre et courbatures",
  "lignes_facture": [
    {
      "garantie_id": 78,
      "libelle_acte": "Consultation médicale générale",
      "quantite": 1
    },
    {
      "garantie_id": 79,
      "libelle_acte": "Prescription paracétamol et vitamine C",
      "quantite": 2
    }
  ],
  "photo_justificatifs": [
    "https://example.com/photo1.jpg",
    "https://example.com/photo2.jpg"
  ]
}
```

**Validation :**
- `diagnostic` : Obligatoire, max 1000 caractères
- `lignes_facture` : Obligatoire, tableau non vide
- `lignes_facture.*.garantie_id` : Obligatoire, doit exister dans `garanties`
- `lignes_facture.*.libelle_acte` : Obligatoire, max 255 caractères
- `lignes_facture.*.quantite` : Obligatoire, entier positif
- `photo_justificatifs` : Optionnel, tableau d'URLs

**Réponse :**
```json
{
  "success": true,
  "message": "Facture créée avec succès",
  "data": {
    "id": 789,
    "numero_facture": "FAC-2025-0001-000789",
    "montant_reclame": 40000.00,
    "montant_a_rembourser": 30500.00,
    "ticket_moderateur": 9500.00,
    "diagnostic": "Grippe saisonnière avec fièvre et courbatures",
    "statut": "EN_ATTENTE",
    "photo_justificatifs": [
      "https://example.com/photo1.jpg",
      "https://example.com/photo2.jpg"
    ],
    "lignes_facture": [
      {
        "id": 1,
        "garantie_id": 78,
        "libelle_acte": "Consultation médicale générale",
        "prix_unitaire": 15000.00,
        "quantite": 1,
        "prix_total": 15000.00,
        "taux_couverture": 80.00,
        "montant_couvert": 12000.00,
        "ticket_moderateur": 3000.00,
        "garantie": {
          "id": 78,
          "libelle": "Consultation médicale générale",
          "prix_standard": 15000.00,
          "taux_couverture": 80.00
        }
      },
      {
        "id": 2,
        "garantie_id": 79,
        "libelle_acte": "Prescription paracétamol et vitamine C",
        "prix_unitaire": 25000.00,
        "quantite": 2,
        "prix_total": 50000.00,
        "taux_couverture": 70.00,
        "montant_couvert": 35000.00,
        "ticket_moderateur": 15000.00,
        "garantie": {
          "id": 79,
          "libelle": "Prescription et délivrance médicaments",
          "prix_standard": 25000.00,
          "taux_couverture": 70.00
        }
      }
    ],
    "sinistre": {
      "id": 456,
      "description": "Consultation médicale pour grippe",
      "date_sinistre": "2025-01-08T10:30:00.000000Z",
      "assure": {
        "id": 123,
        "nom": "Dupont",
        "prenoms": "Jean Pierre"
      }
    },
    "created_at": "2025-01-08T11:15:00.000000Z"
  }
}
```

**Calculs automatiques :**
- `prix_total = prix_unitaire × quantite`
- `montant_couvert = prix_total × (taux_couverture / 100)`
- `ticket_moderateur = prix_total - montant_couvert`
- `montant_reclame = somme des prix_total`
- `montant_a_rembourser = somme des montant_couvert`
- `ticket_moderateur_total = somme des ticket_moderateur`

## 🔍 Consultation des Sinistres

### Liste des Sinistres (Prestataire)

**Endpoint :** `GET /api/v1/prestataire/sinistres`

**Paramètres :**
```http
?search=Jean&statut=DECLARE&per_page=10
```

**Réponse :**
```json
{
  "success": true,
  "message": "Liste des sinistres récupérée avec succès",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 456,
        "description": "Consultation médicale pour grippe",
        "date_sinistre": "2025-01-08T10:30:00.000000Z",
        "statut": "DECLARE",
        "assure": {
          "id": 123,
          "nom": "Dupont",
          "prenoms": "Jean Pierre"
        },
        "factures": [
          {
            "id": 789,
            "numero_facture": "FAC-2025-0001-000789",
            "statut": "EN_ATTENTE"
          }
        ]
      }
    ],
    "total": 5,
    "per_page": 10
  }
}
```

### Détails d'un Sinistre

**Endpoint :** `GET /api/v1/prestataire/sinistres/{id}`

**Réponse :** (Même structure que la création avec tous les détails)

## 👨‍💼 Validation par le Technicien

### Liste des Factures en Attente

**Endpoint :** `GET /api/v1/technicien/factures`

**Paramètres :**
```http
?statut=EN_ATTENTE&prestataire_id=12&date_debut=2025-01-01&date_fin=2025-01-31&per_page=20
```

**Réponse :**
```json
{
  "success": true,
  "message": "Liste des factures récupérée avec succès",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 789,
        "numero_facture": "FAC-2025-0001-000789",
        "montant_reclame": 40000.00,
        "montant_a_rembourser": 30500.00,
        "statut": "EN_ATTENTE",
        "sinistre": {
          "id": 456,
          "description": "Consultation médicale pour grippe",
          "date_sinistre": "2025-01-08",
          "assure": {
            "id": 123,
            "nom": "Dupont",
            "prenoms": "Jean Pierre",
            "est_principal": false
          }
        },
        "prestataire": {
          "id": 12,
          "raison_sociale": "Clinique Moderne",
          "type_prestataire": "CENTRE_SOINS"
        },
        "created_at": "2025-01-08T11:15:00.000000Z"
      }
    ],
    "total": 15,
    "per_page": 20
  }
}
```

### Détails d'une Facture

**Endpoint :** `GET /api/v1/technicien/factures/{id}`

**Réponse :** (Même structure que la création de facture avec tous les détails)

### Valider une Facture

**Endpoint :** `POST /api/v1/technicien/factures/{id}/valider`

**Payload :**
```json
{
  "notes_validation": "Facture conforme, validation accordée"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Facture validée avec succès",
  "data": {
    "id": 789,
    "statut": "VALIDEE_TECHNICIEN",
    "est_valide_par_technicien": true,
    "valide_par_technicien_a": "2025-01-08T14:30:00.000000Z"
  }
}
```

### Rejeter une Facture

**Endpoint :** `POST /api/v1/technicien/factures/{id}/rejeter`

**Payload :**
```json
{
  "motif_rejet": "Documents justificatifs insuffisants. Veuillez fournir l'ordonnance complète."
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Facture rejetée avec succès",
  "data": {
    "id": 789,
    "statut": "REJETEE",
    "motif_rejet_technicien": "Documents justificatifs insuffisants. Veuillez fournir l'ordonnance complète.",
    "rejetee_a": "2025-01-08T14:30:00.000000Z"
  }
}
```

## 📊 Statuts et Workflow

### Statuts des Sinistres
- `DECLARE` : Sinistre déclaré, en attente de facture
- `TRAITE` : Sinistre traité, facture créée

### Statuts des Factures
- `EN_ATTENTE` : Facture créée, en attente de validation
- `VALIDEE_TECHNICIEN` : Validée par le technicien
- `VALIDEE_MEDECIN` : Validée par le médecin contrôleur
- `AUTORISEE_COMPTABLE` : Autorisée par le comptable
- `REMBOURSEE` : Remboursement effectué
- `REJETEE` : Facture rejetée

## 🔔 Notifications

### Nouvelle Facture
Lorsqu'une facture est créée, une notification est automatiquement envoyée à tous les techniciens via :
- **Email** : Notification par email
- **Application** : Notification in-app

### Contenu de la Notification
```json
{
  "titre": "Nouvelle facture à valider",
  "message": "Le prestataire {prestataire} a créé une facture pour {assure}",
  "donnees": {
    "facture_id": 789,
    "prestataire": "Clinique Moderne",
    "assure": "Jean Pierre Dupont",
    "montant": 40000.00
  }
}
```

## 🛡️ Sécurité et Validations

### Assignation des Prestataires
- Un prestataire ne peut créer des sinistres que pour les assurés qui lui ont été assignés
- L'assignation se fait via la table `client_prestataires` liée à `client_contrats`
- Vérification automatique de l'assignation avant création du sinistre

### Contrats Actifs
- Vérification que l'assuré a un contrat actif
- Vérification des dates de début et fin du contrat
- Pour les bénéficiaires, récupération du contrat du principal

### Calculs Automatiques
- Prix standard récupéré automatiquement depuis la garantie
- Calculs de couverture basés sur les taux définis
- Validation des montants côté serveur

## 🚨 Gestion des Erreurs

### Codes d'Erreur Communs
- `400` : Données invalides ou contrat non actif
- `403` : Accès non autorisé ou assuré non assigné
- `404` : Ressource non trouvée
- `422` : Erreur de validation des données
- `500` : Erreur serveur

### Messages d'Erreur Typiques
```json
{
  "success": false,
  "message": "Cet assuré ne vous est pas assigné",
  "error_code": "ASSURED_NOT_ASSIGNED"
}
```

## 📱 Utilisation Frontend

### Flow Typique
1. **Prestataire** : Recherche d'assurés assignés
2. **Prestataire** : Création du sinistre
3. **Prestataire** : Récupération des garanties du contrat
4. **Prestataire** : Création de la facture avec les actes
5. **Système** : Notification automatique aux techniciens
6. **Technicien** : Validation ou rejet de la facture

### Interface Recommandée
- **Recherche d'assurés** : Autocomplete avec dropdown
- **Sélection de garanties** : Liste avec prix et taux de couverture
- **Calculs en temps réel** : Montants mis à jour automatiquement
- **Upload de photos** : Drag & drop avec prévisualisation
- **Validation** : Confirmation avant envoi

---

## ✅ Vérification des Méthodes

Toutes les méthodes nécessaires sont **implémentées et disponibles** :

### Prestataire
- ✅ `searchAssures()` - Recherche d'assurés assignés
- ✅ `store()` - Création de sinistre
- ✅ `createFacture()` - Création de facture
- ✅ `getGarantiesByContrat()` - Récupération des garanties
- ✅ `index()` - Liste des sinistres
- ✅ `show()` - Détails d'un sinistre

### Technicien
- ✅ `factures()` - Liste des factures
- ✅ `showFacture()` - Détails d'une facture
- ✅ `validerFacture()` - Validation de facture
- ✅ `rejeterFacture()` - Rejet de facture

### Routes
- ✅ Toutes les routes sont définies dans `routes/api.php`
- ✅ Middleware d'authentification et de rôles configurés
- ✅ Validation des données implémentée

Le flow des sinistres est **complet et fonctionnel** ! 🎉
